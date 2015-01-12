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

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Request repository
 *
 * @author Oliver Hader <oliver@typo3.org>
 * @package becookies
 * @subpackage classes
 *
 */
class tx_becookies_requestRepository implements \TYPO3\CMS\Core\SingletonInterface {
	const TABLE = 'tx_becookies_request';

	/*
	 * Persists a request element.
	 *
	 * @param tx_becookies_request $request
	 * @return integer
	 */
	public function persist(tx_becookies_request $request) {
		if ($request->getIdentifier()) {
			throw new LogicException('Updating existing elements is not allowed.');
		}

		$fields = array(
			'beuser' => $request->getBackendUserId(),
			'session' => $request->getSessionId(),
			'domain' => $request->getDomain(),
			'tstamp' => ($request->getTimeStamp() ? $request->getTimeStamp() : $GLOBALS['EXEC_TIME']),
		);

		$GLOBALS['TYPO3_DB']->exec_INSERTquery(self::TABLE, $fields);
		return $GLOBALS['TYPO3_DB']->sql_insert_id();
	}

	/**
	 * Removes a request element.
	 *
	 * @param tx_becookies_request $request
	 * @return void
	 */
	public function remove(tx_becookies_request $request) {
		if (!$request->getIdentifier()) {
			throw new LogicException('Cannot remove element without an identifier.');
		}

		$GLOBALS['TYPO3_DB']->exec_DELETEquery(self::TABLE, 'uid=' . intval($request->getIdentifier()));
	}

	/**
	 * Loads a request element by identifier.
	 *
	 * @param integer $identifier
	 * @return tx_becookies_request
	 */
	public function loadByIdentifier($identifier) {
		$request = NULL;

		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', self::TABLE, 'uid=' . intval($identifier));
		if (count($rows)) {
            $request = GeneralUtility::makeInstance(
                'tx_becookies_request',
                $rows[0]['beuser'],
                $rows[0]['session'],
                $rows[0]['domain'],
                $rows[0]['uid'],
                $rows[0]['tstamp']
            );
		}
		return $request;
	}

	/**
	 * Purges expired request elements.
	 *
	 * @param integer $exiresAfter
	 * @return void
	 */
	public function purge($exiresAfter) {
		$exiresAfter = intval($exiresAfter);

		if ($exiresAfter <= 0) {
			throw new LogicException('Elements cannot expire immediatelly or in the past');
		}

		$GLOBALS['TYPO3_DB']->exec_DELETEquery(self::TABLE, 'tstamp < ' . ($GLOBALS['EXEC_TIME'] - $exiresAfter));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/becookies/classes/class.tx_becookies_requestRepository.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/becookies/classes/class.tx_becookies_requestRepository.php']);
}
