<?php
/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\ArchivesAnalyzer\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\IAppConfig;

class Admin implements ISettings {
	public function __construct(
		private IAppConfig $config,
	) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$apiKey = $this->config->getValueString('archives_analyzer', 'ApiKey');
		$apiUrl = $this->config->getValueString('archives_analyzer', 'ApiUrl');
		$model = $this->config->getValueString('archives_analyzer', 'OllamaModel', '');

		$parameters = [
			'apiKey' => $apiKey,
			'apiUrl' => $apiUrl,
			'ollama_model' => $model,
		];

		return new TemplateResponse('archives_analyzer', 'settings-admin', $parameters, '');
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection(): string {
		return 'ai';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 *             the admin section. The forms are arranged in ascending order of the
	 *             priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 */
	public function getPriority(): int {
		return 75;
	}
}
