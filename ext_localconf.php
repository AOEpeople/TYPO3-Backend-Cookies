<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

require_once t3lib_extMgm::extPath('becookies') . 'classes/class.tx_becookies_common.php';
tx_becookies_common::initializeClassFiles();
tx_becookies_common::initializeFrontendHook();

$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][$_EXTKEY] =
	'EXT:' . $_EXTKEY . '/hooks/class.tx_becookies_backendHook.php:tx_becookies_backendHook->process';
?>