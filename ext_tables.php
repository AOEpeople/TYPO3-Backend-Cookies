<?php

defined('TYPO3_MODE') or die('');

$tempColumns = [
    'tx_becookies_login' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:becookies/Resources/Private/Language/locallang_db.xml:sys_domain.tx_becookies_login',
        'config' => [
            'type' => 'check',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_domain', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_domain', 'tx_becookies_login;;;;1-1-1');
