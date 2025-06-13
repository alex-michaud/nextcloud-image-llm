<?php

declare(strict_types=1);

namespace OCA\ArchivesAnalyzer\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Util;
use OCP\ILogger;
use OCP\Server;

class Application extends App implements IBootstrap {
	public const APP_ID = 'archives_analyzer';

    public function __construct() {
        parent::__construct(self::APP_ID);
// 		$this->getLogger()->debug('Initializing Archives Analyzer Application');
    }

// 	private function getLogger(): LoggerInterface {
// 		return Server::get(LoggerInterface::class);
// 	}

    public function register(IRegistrationContext $context): void {
//         $context->registerCapability(Capabilities::class);
    }

    public function boot(IBootContext $context): void {
// 		$logger = $this->getLogger(); // Get logger instance
// 		$logger->debug('Booting app and injecting scripts');

		try {
			// Be explicit about the path
			Util::addScript(self::APP_ID, 'archives_analyzer-main', 'files');
// 			$logger->debug('Script registered successfully');
		} catch (\Exception $e) {
// 			$logger->error('Error registering script: ' . $e->getMessage());
		}
    }
}
