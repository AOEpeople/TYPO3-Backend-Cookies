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
 * Hook to set backend cookies using a frontend request.
 *
 * @author Oliver Hader <oliver@typo3.org>
 * @package becookies
 * @subpackage hooks
 *
 */
class tx_becookies_frontendHook implements \TYPO3\CMS\Core\SingletonInterface {
	const VALUE_TimeFrame = 20;

	/**
	 * @var array
	 */
	protected $arguments;

	/**
	 * @var t3lib_beUserAuth
	 */
	protected $backendUser;

	/**
	 * Creates this object.
	 */
	public function __construct() {
		if (isset($_GET['tx_becookies']) && is_array($_GET['tx_becookies'])) {
			$this->setArguments(GeneralUtility::_GP('tx_becookies'));
		}
		$this->setBackendUser(GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication'));
	}

	/**
	 * Initializes the database connection.
	 *
	 * @return void
	 */
	protected function initializeDatabase() {
		if ($GLOBALS['TYPO3_DB']->isConnected() === FALSE) {
			if (!(
					TYPO3_db_host && TYPO3_db_username && TYPO3_db_password && TYPO3_db &&
					$GLOBALS['TYPO3_DB']->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password) &&
					$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db)
			)) {
				$this->throwException( 'Could not connect to TYPO3 database.' );
			}
		}
	}

	/**
	 * Sets the arguments.
	 *
	 * @param array $arguments
	 * @return void
	 */
	public function setArguments(array $arguments) {
		$this->arguments = $arguments;
	}

	/**
	 * Sets a backend user.
	 *
	 * @param \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser
	 * @return void
	 */
	public function setBackendUser(\TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser) {
		$this->backendUser = $backendUser;
	}

	/**
	 * Processes the request, validates it and send accordant cookie headers.
	 *
	 * @param array $configuration
	 * @return void
	 */
	public function process(array $configuration) {
		if (!isset($this->arguments) || !count($this->arguments)) {
			return;
		}

		$exceptionMessage = 'Warning: No Backend Cookies were transferred to the domain "' . GeneralUtility::getIndpEnv('HTTP_HOST') . '".';
		if(FALSE === $this->areArgumentsValid()) {
			$this->throwException( $exceptionMessage, 'arguments are not valid' );
		}
		if(FALSE === $this->isTimeFrameValid()) {
			$this->throwException( $exceptionMessage, 'timeFrame is not valid: EXEC_TIME is '. $GLOBALS['EXEC_TIME'] . ', argumentsTime is ' . $this->arguments['time'] );
		}

		$this->initializeDatabase();
		$this->getRepository()->purge(self::VALUE_TimeFrame);

		if ($sessionId = $this->getSessionId()) {
			$this->setSessionCookie($sessionId, GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'));
			exit;
		}

		$this->throwException( $exceptionMessage, 'no sessionId found' );
	}

	/**
	 * Determines whether the given arguments are valid.
	 *
	 * @return boolean
	 */
	protected function areArgumentsValid() {
		$result = FALSE;

		$arguments = $this->arguments;

		if (isset($arguments['hash']) && $arguments['hash']) {
			$hash = $arguments['hash'];
			unset($arguments['hash']);
			ksort($arguments);

			$result = ($hash === sha1(serialize($arguments) . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']));
		}

		return $result;
	}

	/**
	 * Determines whether the request is withing a defined time frame of 20 seconds.
	 *
	 * @return boolean
	 */
	protected function isTimeFrameValid() {
		return ($GLOBALS['EXEC_TIME'] <= $this->arguments['time'] + self::VALUE_TimeFrame);
	}

	/**
	 * Gets the real session ID by the given SHA1 hashed value.
	 *
	 * @return string
	 */
	protected function getSessionId() {
		$sessionId = NULL;

		$request = $this->getRepository()->loadByIdentifier($this->arguments['id']);

		if ($request) {
			$currentHost = GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');

			$isDomainValid = ($request->getDomain() === $currentHost || strpos($request->getDomain(), $currentHost . ':') === 0);
			$isTimeStampValid = ($GLOBALS['EXEC_TIME'] <= $request->getTimeStamp() + self::VALUE_TimeFrame);

			if ($isDomainValid && $isTimeStampValid) {
				$sessionId = $request->getSessionId();
			}

			$request->remove();
		}

		return $sessionId;
	}

	/**
	 * Sets the session cookie for the current disposal.
	 *
	 * @see t3lib_userAuth::setSessionCookie()
	 * @param string $sessionId The session ID to be set
	 * @param string $cookieDomain Domain to be used for the cookie
	 * @return void
	 */
	protected function setSessionCookie($sessionId, $cookieDomain) {
		$this->backendUser->newSessionID = TRUE;

		$isSetSessionCookie = $this->backendUser->isSetSessionCookie();
		$isRefreshTimeBasedCookie = $this->backendUser->isRefreshTimeBasedCookie();

		if ($isSetSessionCookie || $isRefreshTimeBasedCookie) {
			$settings = $GLOBALS['TYPO3_CONF_VARS']['SYS'];

			// If no cookie domain is set, use the base path:
			$cookiePath = ($cookieDomain ? '/' : GeneralUtility::getIndpEnv('TYPO3_SITE_PATH'));
			// If the cookie lifetime is set, use it:
			$cookieExpire = ($isRefreshTimeBasedCookie ? $GLOBALS['EXEC_TIME'] + $this->backendUser->lifetime : 0);
			// Use the secure option when the current request is served by a secure connection:
			$cookieSecure = (bool)$settings['cookieSecure'] && GeneralUtility::getIndpEnv('TYPO3_SSL');
			// Deliver cookies only via HTTP and prevent possible XSS by JavaScript:
			$cookieHttpOnly = (bool)$settings['cookieHttpOnly'];

			// Do not set cookie if cookieSecure is set to "1" (force HTTPS) and no secure channel is used:
			if ((int)$settings['cookieSecure'] !== 1 || GeneralUtility::getIndpEnv('TYPO3_SSL')) {
				setcookie(
					$this->backendUser->name,
					$sessionId,
					$cookieExpire,
					$cookiePath,
					$cookieDomain,
					$cookieSecure,
					$cookieHttpOnly
				);
			} else {
				throw new t3lib_exception(
					'Cookie was not set since HTTPS was forced in $TYPO3_CONF_VARS[SYS][cookieSecure].',
					1254325546
				);
			}
		}
	}

	/**
	 * @return tx_becookies_requestRepository
	 */
	protected function getRepository() {
		return GeneralUtility::makeInstance('tx_becookies_requestRepository');
	}

	/**
	 * @param string $message
	 * @param string $reason
	 * @throws RuntimeException
	 */
	private function throwException($message, $reason = '') {
		if(FALSE === empty($reason)) {
			$message .= ' (reason:' . $reason . ')';
		}
		throw new RuntimeException( $message );
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/becookies/hooks/class.tx_becookies_frontendHook.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/becookies/hooks/class.tx_becookies_frontendHook.php']);
}
