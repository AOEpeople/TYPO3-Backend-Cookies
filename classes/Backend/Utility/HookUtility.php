<?php
namespace Aoe\Becookies\Backend\Utility;

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
 *
 */
class HookUtility {
	/**
	 * Initializes the frontend hook.
	 *
	 * @return void
	 */
	public static function initializeFrontendHook() {
		$frontendHookItem = [
			'becookies' => \Aoe\Becookies\Typo3\Hook\FrontendHook::class . '->process'
		];

		$preprocessRequestHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'];

		if (is_array($preprocessRequestHooks)) {
			$preprocessRequestHooks = $frontendHookItem + $preprocessRequestHooks;
		} else {
			$preprocessRequestHooks = $frontendHookItem;
		}
	}
}

