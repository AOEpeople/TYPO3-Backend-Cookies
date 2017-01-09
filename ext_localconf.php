<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][] =
	\AOE\BeCookies\Hooks\BackendHook::class.'->process';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][] =
	\AOE\BeCookies\Hooks\FrontendHook::class.'->process';

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['AOE\\BeCookies\\Hooks\\FrontendHook'] = array(
	'className' => 'AOE\\BeCookies\\Hooks\\FrontendHook',
);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['AOE\\BeCookies\\Hooks\\BackendHook'] = array(
	'className' => 'AOE\\BeCookies\\Hooks\\BackendHook',
);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['AOE\\BeCookies\\Request\\Request'] = array(
	'className' => 'AOE\\BeCookies\\Request\\Request',
);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['AOE\\BeCookies\\Request\\RequestRepository'] = array(
	'className' => 'AOE\\BeCookies\\Request\\RequestRepository',
);
