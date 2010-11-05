<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Oliver Hader <oliver@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Request object
 *
 * @author Oliver Hader <oliver@typo3.org>
 * @package becookies
 * @subpackage classes
 *
 */
class tx_becookies_common {
	/**
	 * Initializes the frontend hook.
	 *
	 * @return void
	 */
	public static function initializeFrontendHook() {
		$frontendHookItem = array(
			'becookies' => 'EXT:becookies/hooks/class.tx_becookies_frontendHook.php:tx_becookies_frontendHook->process'
		);

		$preprocessRequestHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'];

		if (is_array($preprocessRequestHooks)) {
			$preprocessRequestHooks = $frontendHookItem + $preprocessRequestHooks;
		} else {
			$preprocessRequestHooks = $frontendHookItem;
		}
	}

	/**
	 * Initializes all class files as defined in the autoload file.
	 *
	 * @return void
	 * @note There have been some requests to have becookies working on patched TYPO3 systems below 4.4.0/4.3.0.
	 * @deprecated
	 */
	public static function initializeClassFiles() {
		if (self::isBelowVersion('4.3.0')) {
			require_once t3lib_extMgm::extPath('becookies') . 'compatibility/interface.t3lib_singleton.php';

			$autoloadFile = t3lib_extMgm::extPath('becookies') . 'ext_autoload.php';
			$classFiles = require $autoloadFile;

			foreach ($classFiles as $classFile) {
				require_once $classFile;
			}
		}
	}

	/**
	 * Determines whether the currently used TYPO3 version is below a expected version.
	 *
	 * @param string $version The expected version (e.g. '4.4.1')
	 * @return boolean
	 */
	public static function isBelowVersion($version) {
		return (t3lib_div::int_from_ver(TYPO3_version) < t3lib_div::int_from_ver($version));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/becookies/classes/class.tx_becookies_common.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/becookies/classes/class.tx_becookies_common.php']);
}
