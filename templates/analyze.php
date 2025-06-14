<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
/** @var OCP\IURLGenerator $urlGenerator */
$urlGenerator = $_['urlGenerator'];
$version = \OC::$server->getAppManager()->getAppVersion('archives_analyzer');
?>
<script src="<?php print_unescaped(\OC::$server->getURLGenerator()->linkTo('archives_analyzer', 'js/archives_analyzer-main.js')); ?>"></script>
<link rel="stylesheet" href="<?php p($urlGenerator->linkTo('archives_analyzer', 'css/viewer.css')) ?>?v=<?php p($version) ?>"/>

<div id="app-content">
    <div id="app-content-wrapper">
        <h1><?php p($l->t('Analyzing File')); ?></h1>
        <p><?php p($l->t('File being analyzed:')); ?> <?php p($_['file']); ?></p>
        <!-- Your analysis results will go here -->
    </div>
</div>
