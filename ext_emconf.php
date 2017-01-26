<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Backend Cookies',
    'description' => 'This extension allows to handle backend session cookies and sets them for view domains that are different to the backend domain.',
    'category' => 'be',
    'version' => '8.0.0',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Oliver Hader',
    'author_email' => 'oliver@typo3.org',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'php' => '7.0.0-0.0.0',
            'typo3' => '8.0.0-8.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
