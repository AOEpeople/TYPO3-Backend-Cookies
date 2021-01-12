<?php
namespace Aoe\Becookies\Domain\Model;

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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Request object
 *
 * @author Oliver Hader <oliver@typo3.org>
 * @package becookies
 *
 */
class Request extends AbstractEntity {

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
	 * @param string $sessionId
	 * @param string $hash
	 * @param integer $uid
	 * @param integer $timeStamp
	 */
	public function __construct($backendUserId, $sessionId, $domain, $uid = NULL, $timeStamp = NULL) {
		$this->setBackendUserId($backendUserId);
		$this->setSessionId($sessionId);
		$this->setDomain($domain);
		$this->setUid($uid);
		$this->setTimeStamp($timeStamp);
	}

	/**
	 * Sets the uid.
	 *
	 * @param integer $uid
	 */
	public function setUid($uid) {
		$this->uid = $uid;
	}

	/**
	 * Sets the timestamp.
	 *
	 * @param integer $timeStamp
	 */
	public function setTimeStamp($timeStamp) {
		$this->timeStamp = $timeStamp;
	}

	/**
	 * Sets the backend user id.
	 *
	 * @param integer $backendUserId
	 */
	public function setBackendUserId($backendUserId) {
		$this->backendUserId = $backendUserId;
	}

	/**
	 * Sets the domain.
	 *
	 * @param string $domain
	 */
	public function setDomain($domain) {
		$this->domain = $domain;
	}

	/**
	 * Sets the session id.
	 *
	 * @param string $sessionId
	 */
	public function setSessionId($sessionId) {
		$this->sessionId = $sessionId;
	}

	/**
	 * Gets the timestamp.
	 *
	 * @return integer
	 */
	public function getTimeStamp() {
		return $this->timeStamp;
	}

	/**
	 * Gets the backend user id.
	 *
	 * @return integer
	 */
	public function getBackendUserId() {
		return $this->backendUserId;
	}

	/**
	 * Gets the domain.
	 *
	 * @return string
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * Gets the session id.
	 *
	 * @return string
	 */
	public function getSessionId() {
		return $this->sessionId;
	}
}

