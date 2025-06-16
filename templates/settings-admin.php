<?php
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */
/** @var array $_ */
/** @var \OCP\IL10N $l */
\OCP\Util::addScript('archives_analyzer', 'archives_analyzer-settings-admin', 'core');
\OCP\Util::addScript('archives_analyzer', 'settings-admin');
\OCP\Util::addStyle('settings', 'settings');
\OCP\Util::addStyle('archives_analyzer', 'settings-admin');
?>
<hr>
<div class="settings-section sub-section">
	<h2><?php p($l->t('Archives analyzer')); ?></h2>
	<p><?php p($l->t('_archives_analyzer_description_')); ?></p>
	<form onsubmit="return false;">
		<div>
			<label for="apiUrl"><?php p($l->t('API URL')) ?></label>
			<input type="text" name="apiUrl" id="apiUrl"
				   value="<?php p($_['apiUrl']); ?>"
				   placeholder="<?php p($l->t('API URL')); ?>"/>
		</div>
		<div>
			<label for="apiKey"><?php p($l->t('API Key')) ?></label>
			<input type="text" name="apiKey" id="apiKey"
				   value="<?php p($_['apiKey']); ?>"
				   placeholder="<?php p($l->t('API Key')); ?>"/>
		</div>
		<div>
			<label for="ollamaModel"><?php p($l->t('Ollama model')) ?></label>
			<select id="ollamaModel">
				<option value="qwen2.5vl:32b-q8_0">qwen2.5vl:32b-q8_0</option>
			</select>
		</div>
		<div>
			<button type="button" name="saveSettings" id="saveSettings">
				<?php p($l->t('Save settings')); ?>
			</button>
		</div>
		<div id="settingsMsg"></div>
	</form>

</div>
