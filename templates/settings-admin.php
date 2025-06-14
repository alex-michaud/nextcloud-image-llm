<?php
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */
/** @var array $_ */
/** @var \OCP\IL10N $l */
\OCP\Util::addScript('archives_analyzer', 'settings-admin', 'core');
style('archives_analyzer', 'settings-admin');
?>
<form>
	<h3>Archives analyzer</h3>

	<div>
		<input type="text" name="apiUrl" id="apiUrl"
			   value="<?php p($_['apiUrl']); ?>"
			   placeholder="<?php p($l->t('API URL')); ?>"/>
	</div>
	<div>
		<input type="text" name="apiKey" id="apiKey"
			   value="<?php p($_['apiKey']); ?>"
			   placeholder="<?php p($l->t('API Key')); ?>"/>
	</div>
	<div>
		<button type="button" name="saveSettings" id="saveSettings">
			<?php p($l->t('Save settings')); ?>
		</button>
	</div>
	<div id="settingsMsg"></div>
</form>
