<?php

declare(strict_types=1);

namespace Aoe\Becookies\Middleware;

use Aoe\Becookies\Domain\Repository\RequestRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class Frontend implements MiddlewareInterface
{
    /**
     * @var int
     */
    const VALUE_TIME_FRAME = 40;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
	 * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
    protected $backendUser;
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->arguments = $request->getQueryParams();

        if (isset($this->arguments['tx_becookies']) && is_array($this->arguments['tx_becookies'])) {
            $this->backendUser = GeneralUtility::makeInstance(BackendUserAuthentication::class);

            $exceptionMessage = 'Warning: No Backend Cookies were transferred to the domain "' . GeneralUtility::getIndpEnv('HTTP_HOST') . '".';
            if(false === $this->areArgumentsValid()) {
                $this->throwException( $exceptionMessage, 'arguments are not valid' );
            }
            if(false === $this->isTimeFrameValid()) {
                $this->throwException( $exceptionMessage, 'timeFrame is not valid: EXEC_TIME is '. $GLOBALS['EXEC_TIME'] . ', argumentsTime is ' . $this->arguments['tx_becookies']['time'] );
            }

            #$this->getRepository()->purge(self::VALUE_TIME_FRAME);

            if ($sessionId = $this->getSessionId()) {
                $this->setSessionCookie($sessionId, GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'));
                exit;
            }

            $this->throwException( $exceptionMessage, 'no sessionId found' );
        }

        return $handler->handle($request);
    }

    /**
	 * Determines whether the given arguments are valid.
	 *
	 * @return boolean
	 */
	protected function areArgumentsValid()
	{
		$result = false;

		$arguments = $this->arguments['tx_becookies'];

		if (isset($arguments['hash']) && $arguments['hash']) {
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
		return ($GLOBALS['EXEC_TIME'] <= $this->arguments['tx_becookies']['time'] + self::VALUE_TIME_FRAME);
	}

	/**
	 * Gets the real session ID by the given SHA1 hashed value.
	 *
	 * @return string
	 */
    protected function getSessionId()
    {
		$sessionId = null;

		$request = $this->getRepository()->findByUid($this->arguments['tx_becookies']['id']);

		if ($request) {
			$currentHost = GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');

			$isDomainValid = ($request->getDomain() === $currentHost || strpos($request->getDomain(), $currentHost . ':') === 0);
			$isTimeStampValid = ($GLOBALS['EXEC_TIME'] <= $request->getTstamp() + self::VALUE_TIME_FRAME);

			if ($isDomainValid && $isTimeStampValid) {
				$sessionId = $request->getSession();
			}

			$this->getRepository()->remove($request);
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
			if ((int)$settings['cookieSecure'] !== 1 || GeneralUtility::getIndpEnv('TYPO3_SSL') === false) {
				if (PHP_VERSION_ID < 70300) {
					setcookie(
						$this->backendUser->name,
						$sessionId,
						$cookieExpire,
						"$cookiePath; samesite=None",
						$cookieDomain,
						$cookieSecure,
						$cookieHttpOnly);
				} else {
					setcookie($this->backendUser->name, $sessionId, [
						'expires' => $cookieExpire,
						'path' => $cookiePath,
						'domain' => $cookieDomain,
						'samesite' => 'None',
						'secure' => $cookieSecure,
						'httponly' => $cookieHttpOnly,
					]);
				}
			} else {
				throw new \TYPO3\CMS\Core\Exception(
					'Cookie was not set since HTTPS was forced in $TYPO3_CONF_VARS[SYS][cookieSecure].',
					1254325546
				);
			}
		}
	}

	/**
	 * @return RequestRepository
	 */
	protected function getRepository()
	{
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        return $objectManager->get(RequestRepository::class);
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
		throw new \RuntimeException( $message );
	}
}
