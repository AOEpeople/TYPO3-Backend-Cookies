<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

$tempColumns = array (
	'tx_becookies_login' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:becookies/locallang_db.xml:sys_domain.tx_becookies_login',
		'config' => array (
			'type' => 'check',
		),
	),
);

t3lib_extMgm::addTCAcolumns('sys_domain', $tempColumns);
t3lib_extMgm::addToAllTCAtypes('sys_domain', 'tx_becookies_login;;;;1-1-1');
?>