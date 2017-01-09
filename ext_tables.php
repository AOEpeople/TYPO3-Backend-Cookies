<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

$tempColumns = array (
	'tx_becookies_login' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:becookies/Resources/Private/Language/locallang_db.xml:sys_domain.tx_becookies_login',
		'config' => array (
			'type' => 'check',
		),
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_domain', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_domain', 'tx_becookies_login;;;;1-1-1');