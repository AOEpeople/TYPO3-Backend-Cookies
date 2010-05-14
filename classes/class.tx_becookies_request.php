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

/**
 * Request objects
 *
 * @author Oliver Hader <oliver@typo3.org>
 * @package becookies
 * @subpackage classes
 *
 */
class tx_becookies_request {
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
	 * @param string $sessionId
	 * @param string $hash
	 * @param integer $identifier
	 * @param integer $timeStamp
	 */
	public function __construct($backendUserId, $sessionId, $domain, $identifier = NULL, $timeStamp = NULL) {
		$this->setBackendUserId($backendUserId);
		$this->setSessionId($sessionId);
		$this->setDomain($domain);
		$this->setIdentifier($identifier);
		$this->setTimeStamp($timeStamp);
	}

	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	public function setTimeStamp($timeStamp) {
		$this->timeStamp = $timeStamp;
	}

	public function setBackendUserId($backendUserId) {
		$this->backendUserId = $backendUserId;
	}

	public function setDomain($domain) {
		$this->domain = $domain;
	}

	public function setSessionId($sessionId) {
		$this->sessionId = $sessionId;
	}

	public function getIdentifier() {
		return $this->identifier;
	}

	public function getTimeStamp() {
		return $this->timeStamp;
	}

	public function getBackendUserId() {
		return $this->backendUserId;
	}

	public function getDomain() {
		return $this->domain;
	}

	public function getSessionId() {
		return $this->sessionId;
	}

	public function persist() {
		return $this->getRepository()->persist($this);
	}

	public function remove() {
		return $this->getRepository()->remove($this);
	}

	/**
	 * @return tx_becookies_requestRepository
	 */
	protected function getRepository() {
		return new tx_becookies_requestRepository();
		return t3lib_div::makeInstance('tx_becookies_requestRepository');
	}
}