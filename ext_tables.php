<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$tempColumns = array (
	'tx_becookies_login' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:becookies/locallang_db.xml:sys_domain.tx_becookies_login',
		'config' => array (
			'type' => 'check',
		),
	),
);

ExtensionManagementUtility::addTCAcolumns('sys_domain', $tempColumns);
ExtensionManagementUtility::addToAllTCAtypes('sys_domain', 'tx_becookies_login;;;;1-1-1');
?>