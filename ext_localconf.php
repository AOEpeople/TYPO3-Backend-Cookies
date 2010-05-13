<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_becookies_frontendHook.php:tx_becookies_frontendHook->process';
$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_becookies_backendHook.php:tx_becookies_backendHook->process';
?>