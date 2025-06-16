<?php

declare(strict_types=1);

namespace OCA\ArchivesAnalyzer\Controller;

use Exception;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IRequest;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;
use OCP\Http\Client\IClientService;
use OCP\IAppConfig;
//use OCP\Http\Client\IClientException;

class AnalyzeController extends Controller
{
	private LoggerInterface $logger;
	private IUserSession $userSession;
	private IRootFolder $rootFolder;
	private IClientService $clientService;
	private IAppConfig $config;

	/**
	 * @param IRequest $request
	 * @param LoggerInterface $logger
	 * @param IRootFolder $rootFolder
	 * @param IUserSession $userSession
	 * @param IClientService $clientService
	 * @param IAppConfig $config
	 * @param string $appName
	 */
    public function __construct(
		IRequest $request,
		LoggerInterface $logger,
		IRootFolder $rootFolder,
		IUserSession $userSession,
		IClientService $clientService,
		IAppConfig $config,
		string $appName
	) {
		$this->logger = $logger;
		$this->rootFolder = $rootFolder;
		$this->userSession = $userSession;
		$this->clientService = $clientService;
		$this->config = $config;
		parent::__construct($appName, $request);
		$this->logger->debug('AnalyzeController initialized', ['app' => $appName]);
	}

    #[NoCSRFRequired]
    #[NoAdminRequired]
	/**
	 * Convert an archive file to Markdown format using an LLM service.
	 */
    public function markdown(string $file, string $prompt): JSONResponse
    {
		if (!$prompt) {
			$prompt = 'You are a Markdown formatter. Output only valid raw Markdown. Do not wrap your response in a code block or backticks.';
		}

		if (is_null($file) || $file === '') { // Added check for empty string
// 			$this->logger->warning('Analyze request received without a valid file parameter');
			if (is_null($file) || $file === '') {
				return new JSONResponse(['error' => 'File parameter missing or empty'], Http::STATUS_BAD_REQUEST);
			}
		}

        // You can add logic here to analyze the file or pass info to the template
        try {
			// Get current user
			$user = $this->userSession->getUser();
			if ($user === null) {
				$this->logger->warning('User not logged in when trying to analyze file');
	            return new JSONResponse(['error' => 'User not logged in'], Http::STATUS_UNAUTHORIZED);
			}

			// Get file info if possible
			$userFolder = $this->rootFolder->getUserFolder($user->getUID());

			$relativePath = ltrim((string)$file, '/');

			if ($userFolder->nodeExists($relativePath)) {
				$file = $userFolder->get($relativePath);
// 				$this->logger->debug('File found', [
// 					'file_id' => $file->getId(),
// 					'mime' => $file->getMimeType(),
// 					'size' => $file->getSize()
// 				]);
//                $imageData = $file->getContent();
//				$base64 = base64_encode($imageData);
				$base64 = $this->getBase64ImageFromFile($file);

				// make a request to the api llm service
//				$httpClient = $this->clientService->newClient();

//				$model = $this->config->getValueString('archives_analyzer', 'OllamaModel', 'qwen2.5vl:32b-q8_0');
//				$payload = [
//					'images' => [$base64],
//					'model' => $model,
//					'prompt' => $prompt,
//				];

				try {
					/*$response = $httpClient->post($apiUrl, [
						'body' => json_encode($payload),
						'headers' => [
							'Content-Type' => 'application/json',
							'Accept' => 'application/json',
							'x-api-key' => $apiKey
						],
						'timeout' => 60
					]);
					$apiResult = $response->getBody();*/

					$apiResult = $this->queryLLMService($base64, $prompt);

					// Optionally decode JSON if needed:
					$apiData = json_decode($apiResult, true);
					if (json_last_error() !== JSON_ERROR_NONE) {
						$this->logger->error('Failed to decode LLM API response: ' . json_last_error_msg());
						return new JSONResponse(['error' => 'Invalid LLM API response'], Http::STATUS_BAD_GATEWAY);
					}
					$dataResponse = $apiData['response'];
					if (empty($dataResponse)) {
						$this->logger->error('LLM API returned empty response');
						return new JSONResponse(['error' => 'LLM API returned empty response'], Http::STATUS_BAD_GATEWAY);
					}
				    // cleanup any Markdown code block
//					$dataResponse = preg_replace('/^```markdown|```$/', '', $dataResponse);
					// trim spaces at the beginning and end
//					$dataResponse = trim($dataResponse);
					$dataResponse = $this->cleanupMarkdownCodeBlock($dataResponse);

					$originalName = $file->getName();
					$baseName = pathinfo($originalName, PATHINFO_FILENAME);
					$newFileName = $baseName . '.md';

					$parentFolder = $file->getParent();
					if ($parentFolder->nodeExists($newFileName)) {
						$parentFolder->get($newFileName)->delete(); // Overwrite if exists
					}
					$newFile = $parentFolder->newFile($newFileName);

					$newFile->putContent($dataResponse);

					// Return the LLM response
					return new JSONResponse([
						'success' => true,
						'fileid' => $newFile->getId(),
						'parentid' => $parentFolder->getId()
					 ], Http::STATUS_OK);
				} catch (Exception $e) {
					$this->logger->error('LLM API request failed: ' . $e->getMessage());
					return new JSONResponse(['error' => 'LLM API request failed'], Http::STATUS_BAD_GATEWAY);
				}
			} else {
// 				$this->logger->warning('File not found', ['path' => $relativePath]);
	            return new JSONResponse(['error' => 'File not found'], Http::STATUS_NOT_FOUND);
			}
		} catch (NotFoundException $e) {
// 			$this->logger->error('Error accessing file', ['exception' => $e->getMessage()]);
        	return new JSONResponse(['error' => 'Error accessing file: ' . $e->getMessage()], Http::STATUS_NOT_FOUND);
		} catch (\Exception $e) {
// 			$this->logger->error('Unexpected error', ['exception' => $e->getMessage()]);
        	return new JSONResponse(['error' => 'Unexpected error: ' . $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}

// 		$this->logger->debug('Rendering analyze template', ['template_data' => array_keys($templateData)]);
    }

	public function cleanupMarkdownCodeBlock(string $markdown): string
	{
		// cleanup any Markdown code block
		$markdown = preg_replace('/^```markdown|```$/', '', $markdown);
		// trim spaces at the beginning and end
		return trim($markdown);
	}

	public function getBase64ImageFromFile($file) {
		if ($file instanceof \OCP\Files\File) {
			$imageData = $file->getContent();
			return base64_encode($imageData);
		} else {
			throw new \InvalidArgumentException('Provided file is not a valid OCP\Files\File instance');
		}
	}

	public function queryLLMService($base64Image, $prompt) {
		$apiKey = $this->config->getValueString('archives_analyzer', 'ApiKey');
		$apiUrl = $this->config->getValueString('archives_analyzer', 'ApiUrl');
		$model = $this->config->getValueString('archives_analyzer', 'OllamaModel', 'qwen2.5vl:32b-q8_0');

		if (empty($apiKey)) {
			$this->logger->error('API Key is not configured in Archives Analyzer settings');
			return new JSONResponse(['error' => 'API Key is not configured'], Http::STATUS_BAD_REQUEST);
		}
		if (empty($apiUrl)) {
			$this->logger->error('API URL is not configured in Archives Analyzer settings');
			return new JSONResponse(['error' => 'API URL is not configured'], Http::STATUS_BAD_REQUEST);
		}

		$httpClient = $this->clientService->newClient();

		$payload = [
			'images' => [$base64Image],
			'model' => $model,
			'prompt' => $prompt,
		];

		$response = $httpClient->post($apiUrl, [
			'body' => json_encode($payload),
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
				'x-api-key' => $apiKey
			],
			'timeout' => 60
		]);
		return $response->getBody();
	}

}
