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

use Aoe\Becookies\Domain\Repository\RequestRepository;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Hook to set backend cookies using a frontend request.
 *
 * @author Oliver Hader <oliver@typo3.org>
 */
class FrontendHook implements SingletonInterface
{
    /**
     * @const VALUE_TimeFrame Request expire time
     */
    const VALUE_TimeFrame = 40;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var BackendUserAuthentication
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
        if (isset($_GET['tx_becookies']) === true && is_array($_GET['tx_becookies']) === true) {
            $this->setArguments(GeneralUtility::_GP('tx_becookies'));
        }
        $this->setBackendUser(GeneralUtility::makeInstance(BackendUserAuthentication::class));
    }

    /**
     * Sets the arguments.
     *
     * @param array $arguments
     * @return void
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
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
     * Processes the request, validates it and send accordant cookie headers.
     *
     * @param array $configuration
     * @return void
     */
    public function process(array $configuration)
    {
        if (is_array($this->arguments) === false || count($this->arguments) === false) {
            return;
        }

        $exceptionMessage = 'Warning: No Backend Cookies were transferred to the domain "' . GeneralUtility::getIndpEnv('HTTP_HOST') . '".';
        if (false === $this->areArgumentsValid()) {
            $this->throwException($exceptionMessage, 'arguments are not valid');
        }

        $this->getRequestRepository()->purge(self::VALUE_TimeFrame);

        $sessionId = $this->getSessionId();
        if (is_string($sessionId) === true) {
            $this->setSessionCookie($sessionId, GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'));
            exit;
        }

        $this->throwException($exceptionMessage, 'no sessionId found');
    }

    /**
     * Determines whether the given arguments are valid.
     *
     * @return boolean
     */
    protected function areArgumentsValid()
    {
        $result = false;
        $arguments = $this->arguments;

        if (isset($arguments['hash']) === true && is_string($arguments['hash']) === true) {
            $hash = $arguments['hash'];
            unset($arguments['hash']);
            ksort($arguments);

            $result = ($hash === sha1(serialize($arguments) . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']));
        }

        return $result;
    }

    /**
     * Determines whether the request is withing a defined time frame of 40 seconds.
     *
     * @return boolean
     */
    protected function isTimeFrameValid()
    {
        return ($GLOBALS['EXEC_TIME'] <= $this->arguments['time'] + self::VALUE_TimeFrame);
    }

    /**
     * Gets the real session ID by the given SHA1 hashed value.
     *
     * @return string
     */
    protected function getSessionId()
    {
        $sessionId = null;
        $requestRepository = $this->getRequestRepository();
        $request = $requestRepository->loadByIdentifier($this->arguments['id']);

        if ($request) {
            $currentHost = GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');

            $isDomainValid = ($request->getDomain() === $currentHost || strpos($request->getDomain(), $currentHost . ':') === 0);
            $isTimeStampValid = ($GLOBALS['EXEC_TIME'] <= $request->getTimeStamp() + self::VALUE_TimeFrame);

            if ($isDomainValid && $isTimeStampValid) {
                $sessionId = $request->getSessionId();
            }

            $requestRepository->remove($request);
        }

        return $sessionId;
    }

    /**
     * Sets the session cookie for the current disposal.
     *
     * @see \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication::setSessionCookie()
     * @param string $sessionId The session ID to be set
     * @param string $cookieDomain Domain to be used for the cookie
     * @return void
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function setSessionCookie($sessionId, $cookieDomain)
    {
        $this->backendUser->newSessionID = true;

        $isSetSessionCookie = $this->backendUser->isSetSessionCookie();
        $isRefreshTimeBasedCookie = $this->backendUser->isRefreshTimeBasedCookie();

        if ($isSetSessionCookie === true || $isRefreshTimeBasedCookie === true) {
            $settings = $GLOBALS['TYPO3_CONF_VARS']['SYS'];

            // If no cookie domain is set, use the base path:
            $cookiePath = (is_string($cookieDomain) === true ? '/' : GeneralUtility::getIndpEnv('TYPO3_SITE_PATH'));
            // If the cookie lifetime is set, use it:
            $cookieExpire = ($isRefreshTimeBasedCookie === true ? $GLOBALS['EXEC_TIME'] + $this->backendUser->lifetime : 0);
            // Use the secure option when the current request is served by a secure connection:
            $cookieSecure = $settings['cookieSecure'] === true && GeneralUtility::getIndpEnv('TYPO3_SSL');
            // Deliver cookies only via HTTP and prevent possible XSS by JavaScript:
            $cookieHttpOnly = (bool)$settings['cookieHttpOnly'];

            // Do not set cookie if cookieSecure is set to "1" (force HTTPS) and no secure channel is used:
            if ((int)$settings['cookieSecure'] !== 1 || is_string(GeneralUtility::getIndpEnv('TYPO3_SSL')) === true) {
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
                throw new \TYPO3\CMS\Core\Exception(
                    'Cookie was not set since HTTPS was forced in $TYPO3_CONF_VARS[SYS][cookieSecure].',
                    1254325546
                );
            }
        }
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        if ($this->objectManager instanceof ObjectManager === false) {
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }

        return $this->objectManager;
    }

    /**
     * @return RequestRepository
     */
    protected function getRequestRepository()
    {
        if ($this->requestRepository instanceof ObjectManager === false) {
            $this->requestRepository = $this->getObjectManager()->get(RequestRepository::class);
        }

        return $this->requestRepository;
    }

    /**
     * @param string $message
     * @param string $reason
     * @throws \RuntimeException
     */
    private function throwException($message, $reason = '')
    {
        if (false === empty($reason)) {
            $message .= ' (reason:' . $reason . ')';
        }
        throw new \RuntimeException($message);
    }
}
