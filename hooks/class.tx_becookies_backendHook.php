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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook to render IFAMES that call the accordant frontend URLs to set the cookies.
 *
 * @author Oliver Hader <oliver@typo3.org>
 * @package becookies
 * @subpackage hooks
 *
 */
class tx_becookies_backendHook implements \TYPO3\CMS\Core\SingletonInterface {
	/**
	 * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected $backendUser;

	/**
	 * Creates this object.
	 */
	public function __construct() {
		$this->setBackendUser($GLOBALS['BE_USER']);
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
	 * Sets accordant iframes to have the cookies defined.
	 *
	 * @param array $configuration
	 * @param TYPO3backend $parent
	 * @return void
	 */
	public function process(array $configuration, TYPO3backend $parent) {
		$content = '';

		foreach ($this->getAllDomains() as $domain) {
			if ($this->isRequired($domain)) {
				$requestId = $this->createRequest($domain);
				$url = $this->generateUrl($domain, $requestId);
				$content .= $this->generateIFrame($url);
			}
		}

		if ($content) {
			$GLOBALS['TBE_TEMPLATE']->postCode .= "\t<div style=\"width:0; height:0; display:none;\">\n" . $content . "\t</div>\n";
		}
	}

	/**
	 * Creates a request element.
	 *
	 * @param string $domain
	 * @return integer
	 */
	protected function createRequest($domain) {
		/* @var $request tx_becookies_request */
        $request = GeneralUtility::makeInstance(
            'tx_becookies_request',
            $this->backendUser->user['uid'],
            $this->backendUser->id,
            $domain
        );
		return $request->persist();
	}

	/**
	 * Determines whether it is required to set cookies for a domain.
	 *
	 * @param string $domain Domain to be checked
	 * @return boolean
	 */
	protected function isRequired($domain) {
		list($domain) = GeneralUtility::trimExplode(':', $domain, TRUE, 2);
		$isCurrentHost = (GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY') === $domain);

		return (!$isCurrentHost && !$this->matchesCookieDomain($domain));
	}

	/**
	 * Determines whether a domain matches the cookieDomain setting.
	 *
	 * @param string $domain Domain to be checked
	 * @return boolean
	 */
	protected function matchesCookieDomain($domain) {
		$result = FALSE;
		$cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];

		if ($cookieDomain) {
			if ($cookieDomain{0} == '/') {
				if (@preg_match($cookieDomain, $domain, $match)) {
					$result = TRUE;
				}
			} elseif ($cookieDomain === $domain) {
				$result = TRUE;
			} elseif (preg_match('/' . preg_quote('.' . ltrim($cookieDomain, '.'), '/') . '$/', $domain)) {
				$result = TRUE;
			}
		}

		return $result;
	}

	/**
	 * Generates the HTML markup for an IFRAME.
	 *
	 * @param string $url URL to be used as source
	 * @return string
	 */
	protected function generateIFrame($url) {
		$url = htmlspecialchars($url);
		return "\t\t<iframe src=\"" . $url . "\" height=\"0\" width=\"0\" frameborder=\"0\" style=\"width:0;height:0;\"></iframe>\n";
	}

	/**
	 * Generates a frontend URL for a given domain.
	 *
	 * @param string $domain Domain to be used
	 * @param integer $requestId Identifier of the request element
	 * @return string
	 */
	protected function generateUrl($domain, $requestId) {
		$scheme = (GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https' : 'http');
		$port = GeneralUtility::getIndpEnv('TYPO3_PORT');
		$host = $domain . (strpos($domain, ':') === FALSE && $port && $port != '80' ? ':' . $port : '');
		$query = GeneralUtility::implodeArrayForUrl('tx_becookies', $this->generateArguments($requestId));

		$url = $scheme . '://' . $host . '/index.php?' . $query;
		return $url;
	}

	/**
	 * Generates the argument required to set the cookies with the frontend request.
	 *
	 * @param integer $requestId Identifier of the request element
	 * @return array
	 */
	protected function generateArguments($requestId) {
		$arguments = array(
			'id' => (string) $requestId,
			'time' => (string) $GLOBALS['EXEC_TIME'],
		);

		ksort($arguments);
		$arguments['hash'] = sha1(serialize($arguments) . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);

		return $arguments;
	}

	/**
	 * Gets all configured domains.
	 *
	 * @return array All configured domains
	 */
	protected function getAllDomains() {
		$domains = array();

		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'sys_domain',
			'redirectTo="" AND hidden=0 AND tx_becookies_login=1'
		);

		if (is_array($rows)) {
			foreach ($rows as $row) {
				$domains[] = $row['domainName'];
			}
			$domains = array_unique($domains);
		}

		return $domains;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/becookies/hooks/class.tx_becookies_backendHook.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/becookies/hooks/class.tx_becookies_backendHook.php']);
}
