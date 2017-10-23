<?php
namespace Aoe\Becookies\Domain\Model;

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

/**
 * Request object
 *
 * @author Oliver Hader <oliver@typo3.org>
 */
class Request
{
    /**
     * @var integer
     */
    protected $identifier;

    /**
     * @var integer
     */
    protected $timeStamp;

    /**
     * @var integer
     */
    protected $backendUserId;

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @param integer $backendUserId
     * @param string  $sessionId
     * @param string  $domain
     * @param integer $identifier
     * @param integer $timeStamp
     */
    public function __construct($backendUserId, $sessionId, $domain, $identifier = null, $timeStamp = null)
    {
        $this->setBackendUserId($backendUserId);
        $this->setSessionId($sessionId);
        $this->setDomain($domain);
        $this->setIdentifier($identifier);
        $this->setTimeStamp($timeStamp);
    }

    /**
     * Sets the identifier.
     *
     * @param integer $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Sets the timestamp.
     *
     * @param integer $timeStamp
     */
    public function setTimeStamp($timeStamp)
    {
        $this->timeStamp = $timeStamp;
    }

    /**
     * Sets the backend user id.
     *
     * @param integer $backendUserId
     */
    public function setBackendUserId($backendUserId)
    {
        $this->backendUserId = $backendUserId;
    }

    /**
     * Sets the domain.
     *
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Sets the session id.
     *
     * @param string $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Gets the identifier.
     *
     * @return integer
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Gets the timestamp.
     *
     * @return integer
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * Gets the backend user id.
     *
     * @return integer
     */
    public function getBackendUserId()
    {
        return $this->backendUserId;
    }

    /**
     * Gets the domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Gets the session id.
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }
}
