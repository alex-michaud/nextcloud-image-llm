<?php
/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\ArchivesAnalyzer\Settings;

use OCA\Encryption\Session;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\ISession;
use OCP\IUserSession;
use OCP\Settings\ISettings;
use Psr\Log\LoggerInterface;
use OCP\IAppConfig;

class Admin implements ISettings {
	public function __construct(
		private IL10N $l,
		private LoggerInterface $logger,
		private IUserSession $userSession,
		private IAppConfig $config,
		private ISession $session,
	) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {
		$apiKey = $this->config->getValueString('archives_analyzer', 'ApiKey');
		$apiUrl = $this->config->getValueString('archives_analyzer', 'ApiUrl');
		$session = new Session($this->session);

		$parameters = [
			'apiKey' => $apiKey,
			'$apiUrl' => $apiUrl,
		];

		return new TemplateResponse('archives_analyzer', 'settings-admin', $parameters, '');
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection() {
		return 'archives_analyzer';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 *             the admin section. The forms are arranged in ascending order of the
	 *             priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 */
	public function getPriority() {
		return 11;
	}
}
