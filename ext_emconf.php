<?php

########################################################################
# Extension Manager/Repository config file for ext "becookies".
#
# Auto generated 06-09-2010 17:24
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
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.7',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.4.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:13:{s:9:"ChangeLog";s:4:"bd5a";s:10:"README.txt";s:4:"e98a";s:16:"ext_autoload.php";s:4:"0505";s:12:"ext_icon.gif";s:4:"b4e6";s:17:"ext_localconf.php";s:4:"878a";s:14:"ext_tables.php";s:4:"e95f";s:14:"ext_tables.sql";s:4:"7ac4";s:16:"locallang_db.xml";s:4:"78ef";s:37:"classes/class.tx_becookies_common.php";s:4:"25ab";s:38:"classes/class.tx_becookies_request.php";s:4:"b965";s:48:"classes/class.tx_becookies_requestRepository.php";s:4:"7b98";s:40:"hooks/class.tx_becookies_backendHook.php";s:4:"6567";s:41:"hooks/class.tx_becookies_frontendHook.php";s:4:"4470";}',
	'suggests' => array(
	),
);

?>