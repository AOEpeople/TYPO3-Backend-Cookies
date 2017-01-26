<?php
namespace Aoe\Becookies\Hook;

/*
 * Copyright notice
 *
 * (c) 2010 Oliver Hader <oliver@typo3.org>
 * All rights reserved
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Aoe\Becookies\Domain\Model\Request;
use Aoe\Becookies\Domain\Repository\RequestRepository;
use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Hook to render iFrames that call the accordant frontend URLs to set the cookies.
 *
 * @author Oliver Hader <oliver@typo3.org>
 */
class BackendHook implements SingletonInterface
{
    /**
     * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected $backendUser;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Aoe\Becookies\Domain\Repository\RequestRepository
     */
    private $requestRepository;

    /**
     * Creates this object.
     */
    public function __construct()
    {
        $this->setBackendUser($GLOBALS['BE_USER']);
    }

    /**
     * Sets a backend user.
     *
     * @param  BackendUserAuthentication $backendUser
     * @return void
     */
    public function setBackendUser(BackendUserAuthentication $backendUser)
    {
        $this->backendUser = $backendUser;
    }

    /**
     * Sets accordant iFrames to have the cookies defined.
     *
     * @param  array $configuration
     * @param  BackendController $parent
     * @return void
     */
    public function process(array $configuration, BackendController $parent)
    {
        $content = '';

        foreach ($this->getAllDomains() as $domain) {
            if ($this->isRequired($domain)) {
                $requestId = $this->createRequest($domain);
                $url = $this->generateUrl($domain, $requestId);
                $content .= $this->generateIFrame($url);
            }
        }

        if ($content) {
            $GLOBALS['TBE_TEMPLATE']->postCode .= '<div style="width:0; height:0; display:none;">' . $content . '</div>';
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
        /* @var Request $request */
        $request = GeneralUtility::makeInstance(
            Request::class,
            $this->backendUser->user['uid'],
            $this->backendUser->id,
            $domain
        );
        return $this->requestRepository->persist($request);
    }

    /**
     * Determines whether it is required to set cookies for a domain.
     *
     * @param string $domain Domain to be checked
     * @return boolean
     */
    protected function isRequired($domain)
    {
        list($domain) = GeneralUtility::trimExplode(':', $domain, true, 2);
        $isCurrentHost = (GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY') === $domain);

        return (!$isCurrentHost && !$this->matchesCookieDomain($domain));
    }

    /**
     * Determines whether a domain matches the cookieDomain setting.
     *
     * @param  string $domain Domain to be checked
     * @return boolean
     */
    protected function matchesCookieDomain($domain)
    {
        $result = false;
        $cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];

        if ($cookieDomain) {
            if ($cookieDomain{0} == '/') {
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
     * @param  string $url URL to be used as source
     * @return string
     */
    protected function generateIFrame($url)
    {
        $url = htmlspecialchars($url);
        return '<iframe src="' . $url . '" height="0" width="0" frameborder="0" style="width:0;height:0;"></iframe>"';
    }

    /**
     * Generates a frontend URL for a given domain.
     *
     * @param  string $domain Domain to be used
     * @param  integer $requestId Identifier of the request element
     * @return string
     */
    protected function generateUrl($domain, $requestId)
    {
        $scheme = (GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https' : 'http');
        $port = GeneralUtility::getIndpEnv('TYPO3_PORT');
        $host = $domain . (strpos($domain, ':') === false && $port && $port != '80' ? ':' . $port : '');
        $query = GeneralUtility::implodeArrayForUrl('tx_becookies', $this->generateArguments($requestId));

        $url = $scheme . '://' . $host . '/index.php?' . $query;

        return $url;
    }

    /**
     * Generates the argument required to set the cookies with the frontend request.
     *
     * @param  integer $requestId Identifier of the request element
     * @return array
     */
    protected function generateArguments($requestId)
    {
        $arguments = [
            'id' => (string)$requestId,
            'time' => (string)$GLOBALS['EXEC_TIME'],
        ];

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
        $rows = BackendUtility::getRecordsByField('sys_domain', 'tx_becookies_login', 1, 'redirectTo="" AND hidden = 0');

        if (is_array($rows)) {
            foreach ($rows as $row) {
                $domains[] = $row['domainName'];
            }

            $domains = array_unique($domains);
        }

        return $domains;
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        if (!($this->objectManager instanceof ObjectManager)) {
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }

        return $this->objectManager;
    }

    /**
     * @return RequestRepository
     */
    protected function getRequestRepository()
    {
        if (!($this->requestRepository instanceof ObjectManager)) {
            $this->requestRepository = $this->getObjectManager()->get(RequestRepository::class);
        }

        return $this->requestRepository;
    }
}
