<?php

declare(strict_types=1);

namespace OCA\ArchivesAnalyzer\Controller;

use OCA\ArchivesAnalyzer\AppInfo\Application;

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
use OCP\Http\Client\IClientException;

class AnalyzeController extends Controller
{
	private LoggerInterface $logger;
	private IUserSession $userSession;
	private IRootFolder $rootFolder;
	private IClientService $clientService;

	/**
	 * @param IRequest $request
	 * @param LoggerInterface $logger
	 * @param IRootFolder $rootFolder
	 * @param IUserSession $userSession
	 * @param IClientService $clientService
	 * @param string $appName
	 */
    public function __construct(
		IRequest $request,
		LoggerInterface $logger,
		IRootFolder $rootFolder,
		IUserSession $userSession,
		IClientService $clientService,
		string $appName
	) {
		$this->logger = $logger;
		$this->rootFolder = $rootFolder;
		$this->userSession = $userSession;
		$this->clientService = $clientService;
		parent::__construct($appName, $request);
		$this->logger->debug('AnalyzeController initialized', ['app' => $appName]);
	}

    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function markdown(string $file): JSONResponse
    {
//     	$this->logger->debug('Analyze request received', [
// 			'file' => $file,
// 			'request_method' => $this->request->getMethod(),
// 			'request_params' => $this->request->getParams()
// 		]);

		if (is_null($file) || $file === '') { // Added check for empty string
// 			$this->logger->warning('Analyze request received without a valid file parameter');
			if (is_null($file) || $file === '') {
				return new JSONResponse(['error' => 'File parameter missing or empty'], JSONResponse::STATUS_BAD_REQUEST);
			}
		}

        // You can add logic here to analyze the file or pass info to the template
        try {
			// Get current user
			$user = $this->userSession->getUser();
			if ($user === null) {
				$this->logger->warning('User not logged in when trying to analyze file');
	            return new JSONResponse(['error' => 'User not logged in'], JSONResponse::STATUS_UNAUTHORIZED);
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
				$base64 = null;
                $imageData = $file->getContent();
				$base64 = base64_encode($imageData);

				// make a request to the api llm service
				$llmApiUrl = 'http://host.docker.internal:3000/api/llm/generate';
// 			        $httpClient = \OC::$server->get(IClientService::class)->newClient();
				$httpClient = $this->clientService->newClient();
				$prompt = 'You are a Markdown formatter. Output only valid raw Markdown. Do not wrap your response in a code block or backticks.';
				$model = 'qwen2.5vl:32b-q8_0';
				$payload = [
					'images' => [$base64],
					'model' => $model,
					'prompt' => $prompt,
				];

				try {
					$response = $httpClient->post($llmApiUrl, [
						'body' => json_encode($payload),
						'headers' => [
							'Content-Type' => 'application/json',
							'Accept' => 'application/json',
							'x-api-key' => '1cd01ecf-5222-4e24-b8df-2435e78ecf87'
						],
						'timeout' => 60
					]);
					$apiResult = $response->getBody();
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
				    // cleanup any markdown code block
					$dataResponse = preg_replace('/^```markdown|```$/', '', $dataResponse);
					// trim spaces at the beginning and end
					$dataResponse = trim($dataResponse);

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
				} catch (IClientException $e) {
					$this->logger->error('LLM API request failed: ' . $e->getMessage());
					return new JSONResponse(['error' => 'LLM API request failed'], Http::STATUS_BAD_GATEWAY);
				}

				return new JSONResponse([
					'fileInfo' => [
						'id' => $file->getId(),
						'name' => $file->getName(),
						'mime' => $file->getMimeType(),
						'size' => $file->getSize(),
					]
				]);
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

}
