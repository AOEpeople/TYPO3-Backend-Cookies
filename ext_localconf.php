<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

\Aoe\Becookies\Backend\Utility\HookUtility::initializeFrontendHook();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess']['becookies'] = 
    \Aoe\Becookies\Typo3\Hook\BackendHook::class . '->process';
