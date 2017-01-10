<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][] =
	'AOE\\BeCookies\\Hooks\\BackendHook->process';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][] =
	'AOE\\BeCookies\\Hooks\\FrontendHook->process';
