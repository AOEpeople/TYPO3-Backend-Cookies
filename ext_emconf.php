<?php

########################################################################
# Extension Manager/Repository config file for ext "becookies".
#
# Auto generated 26-05-2010 13:30
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Backend Cookies',
	'description' => 'This extension allows to handle backend session cookies and sets them for view domains that are different to the backend domain.',
	'category' => 'be',
	'author' => 'Oliver Hader',
	'author_email' => 'oliver@typo3.org',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.4.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:10:{s:9:"ChangeLog";s:4:"62ba";s:10:"README.txt";s:4:"e98a";s:16:"ext_autoload.php";s:4:"54cc";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"409f";s:14:"ext_tables.sql";s:4:"c6a4";s:38:"classes/class.tx_becookies_request.php";s:4:"b965";s:48:"classes/class.tx_becookies_requestRepository.php";s:4:"7b98";s:40:"hooks/class.tx_becookies_backendHook.php";s:4:"3279";s:41:"hooks/class.tx_becookies_frontendHook.php";s:4:"41fe";}',
	'suggests' => array(
	),
);

?>