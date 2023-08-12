<?php

declare(strict_types=1);

namespace Aoe\Becookies\Backend\EventListener;

use Aoe\Becookies\Domain\Model\Request;
use Aoe\Becookies\Domain\Repository\RequestRepository;
use TYPO3\CMS\Backend\Controller\Event\AfterBackendPageRenderEvent;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

final class BecookieEventListener
{
    /**
     * @var BackendUserAuthentication
     */
    protected $backendUser;

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
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $this->requestRepository = GeneralUtility::makeInstance(RequestRepository::class);
        $this->setBackendUser($GLOBALS['BE_USER']);
    }

    public function __invoke(AfterBackendPageRenderEvent $event): void
    {
        $additionalContent = '';

        foreach ($this->getAllDomains() as $domainObj) {
            if (!$this->matchesCookieDomain($domainObj->getHost())) {

                $requestId = $this->createRequest($domainObj->getHost());
                $url = $this->generateUrl($domainObj, $requestId);
                $additionalContent .= $this->generateIFrame($url);
            }
        }

        if (false === empty($additionalContent)) {
            $content = $event->getContent() . "\t<div style=\"width:0; height:0; display:none;\">\n" . $additionalContent . "\t</div>\n";
            $event->setContent($content);
        }
    }

    /**
     * Sets a backend user.
     *
     * @param BackendUserAuthentication $backendUser
     * @return void
     */
    public function setBackendUser(BackendUserAuthentication $backendUser)
    {
        $this->backendUser = $backendUser;
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
            Request::class,
            $this->backendUser->userSession->getUserId(),
            $this->backendUser->userSession->getIdentifier(),
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

        $siteFinder = new SiteFinder;
        foreach($siteFinder->getAllSites() as $siteConfiguration) {
            $domains[] = $siteConfiguration->getBase();
        }

        return $domains;
    }
}