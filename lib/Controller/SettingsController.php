<?php

namespace OCA\ArchivesAnalyzer\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IAppConfig;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;

class SettingsController extends Controller {
	private IAppConfig $config;

	public function __construct(
		string $appName,
		IRequest $request,
		IAppConfig $config
	) {
		parent::__construct($appName, $request);
		$this->config = $config;
	}

	#[AuthorizedAdminSetting]
	public function save(string $apiKey, string $apiUrl, string $ollamaModel): JSONResponse {
		$this->config->setValueString('archives_analyzer', 'ApiKey', $apiKey);
		$this->config->setValueString('archives_analyzer', 'ApiUrl', $apiUrl);
		$this->config->setValueString('archives_analyzer', 'OllamaModel', $ollamaModel ?? '');
		return new JSONResponse(['status' => 'success']);
	}
}
