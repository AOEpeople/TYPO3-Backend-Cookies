<?php
namespace Aoe\Becookies\Typo3\Hook;

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

use Aoe\Becookies\Domain\Repository\RequestRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Hook to render IFAMES that call the accordant frontend URLs to set the cookies.
 *
 * @author Oliver Hader <oliver@typo3.org>
 * @package becookies
 *
 */
class BackendHook implements \TYPO3\CMS\Core\SingletonInterface {
	/**
	 * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected $backendUser;

	/**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    private $objectManager;

	/**
	 * @var PersistenceManager
	 */
	private $persistenceManager;

	/**
	 * @var RequestRepository
	 */
	private $requestRepository;

	/**
	 * Creates this object.
	 */
	public function __construct()
	{
		$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $this->requestRepository = $this->objectManager->get(RequestRepository::class);
		$this->setBackendUser($GLOBALS['BE_USER']);
	}

	/**
	 * Sets a backend user.
	 *
	 * @param \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser
	 * @return void
	 */
	public function setBackendUser(\TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser)
	{
		$this->backendUser = $backendUser;
	}

	/**
	 * Sets accordant iframes to have the cookies defined.
	 *
	 * @param array $configuration
	 * @return void
	 */
	public function process(&$configuration)
	{
		$content = '';

		foreach ($this->getAllDomains() as $domainObj) {
			if (!$this->matchesCookieDomain($domainObj->getHost)) {

				$requestId = $this->createRequest($domainObj->getHost());
				$url = $this->generateUrl($domainObj, $requestId);
				$content .= $this->generateIFrame($url);
			}
		}

		if ($content) {
			$configuration['content'] .= "\t<div style=\"width:0; height:0; display:none;\">\n" . $content . "\t</div>\n";
		}
	}

	/**
	 * Creates a request element.
	 *
	 * @param string $domain
	 * @return integer
	 */
	protected function createRequest($domain)
	{
		/* @var $request Request */
        $request = GeneralUtility::makeInstance(
            \Aoe\Becookies\Domain\Model\Request::class,
            $this->backendUser->user['uid'],
            $this->backendUser->id,
            $domain
		);

		if ($request->getUid() !== null) {
			throw new LogicException('Updating existing elements is not allowed.');
		}

		return $this->requestRepository->add($request);
	}

	/**
	 * Determines whether a domain matches the cookieDomain setting.
	 *
	 * @param string $domain Domain to be checked
	 * @return boolean
	 */
	protected function matchesCookieDomain($domain)
	{
		$result = false;
		$cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];

		if ($cookieDomain) {
			if (substr($cookieDomain, 0, 1) == '/') {
				if (@preg_match($cookieDomain, $domain, $match)) {
					$result = true;
				}
			} elseif ($cookieDomain === $domain) {
				$result = true;
			} elseif (preg_match('/' . preg_quote('.' . ltrim($cookieDomain, '.'), '/') . '$/', $domain)) {
				$result = true;
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
	protected function generateIFrame($url)
	{
		$url = htmlspecialchars($url);
		return "\t\t<iframe src=\"" . $url . "\" height=\"0\" width=\"0\" frameborder=\"0\" style=\"width:0;height:0;\"></iframe>\n";
	}

	/**
	 * Generates a frontend URL for a given domain.
	 *
	 * @param \TYPO3\CMS\Core\Http\Uri $domainObj
	 * @param integer $requestId Identifier of the request element
	 * @return string
	 */
	protected function generateUrl($domainObj, $requestId)
	{
		// ToDo: HttpUtility::buildQueryString
		$query = GeneralUtility::implodeArrayForUrl('tx_becookies', $this->generateArguments($requestId));

		return $domainObj->withQuery($query)->__toString();
	}

	/**
	 * Generates the argument required to set the cookies with the frontend request.
	 *
	 * @param integer $requestId Identifier of the request element
	 * @return array
	 */
	protected function generateArguments($requestId)
	{
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
	protected function getAllDomains()
	{
		$domains = [];

		$siteFinder = new \TYPO3\CMS\Core\Site\SiteFinder;
		foreach($siteFinder->getAllSites() as $siteConfiguration) {
			$domains[] = $siteConfiguration->getBase();
		}

		return $domains;
	}
}

