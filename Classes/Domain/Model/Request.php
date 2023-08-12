<?php declare(strict_types=1);

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
class Request extends AbstractEntity
{
	/**
	 * @var integer
	 */
	protected $tstamp;

	/**
	 * @var integer
	 */
	protected $beuser;

	/**
	 * @var string
	 */
	protected $session;

	/**
	 * @var string
	 */
	protected $domain;

	/**
	 * @param integer $beuser
	 * @param string $session
	 * @param string $domain
	 * @param integer $uid
	 * @param integer $tstamp
	 */
	public function __construct($beuser, $session, $domain, $uid = null, $tstamp = null)
	{
		$this->setBeuser($beuser);
		$this->setSession($session);
		$this->setDomain($domain);
		$this->setUid($uid);
		$this->setTstamp($tstamp);
	}

	/**
	 * Sets the uid.
	 *
	 * @param integer $uid
	 */
	public function setUid($uid)
	{
		$this->uid = $uid;
	}

	/**
	 * Sets the timestamp.
	 *
	 * @param integer $tstamp
	 */
	public function setTstamp($tstamp)
	{
		$this->tstamp = ($tstamp == null) ? time() : $tstamp;
	}

	/**
	 * Sets the backend user id.
	 *
	 * @param integer $beuser
	 */
	public function setBeuser($beuser)
	{
		$this->beuser = $beuser;
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
	 * @param string $session
	 */
	public function setSession($session)
	{
		$this->session = $session;
	}

	/**
	 * Gets the timestamp.
	 *
	 * @return integer
	 */
	public function getTstamp()
	{
		return $this->tstamp;
	}

	/**
	 * Gets the backend user id.
	 *
	 * @return integer
	 */
	public function getBeuser()
	{
		return $this->beuser;
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
	public function getSession()
	{
		return $this->session;
	}
}

