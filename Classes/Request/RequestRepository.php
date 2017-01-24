<?php

namespace AOE\BeCookies\Request;

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

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Request repository
 *
 * @author Oliver Hader <oliver@typo3.org>
 * @package becookies
 * @subpackage classes
 *
 */
class RequestRepository implements SingletonInterface {

    const TABLE = 'tx_becookies_request';

    /**
     * @var Connection
     */
    protected $connection;

	public function __construct()
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $this->connection = $connectionPool->getConnectionForTable(self::TABLE);
    }

    /*
     * Persists a request element.
     *
     * @param Request $request
     * @return integer
     */
	public function persist(Request $request) {
		if ($request->getIdentifier()) {
			throw new \LogicException('Updating existing elements is not allowed.');
		}

		$fields = array(
			'beuser' => $request->getBackendUserId(),
			'session' => $request->getSessionId(),
			'domain' => $request->getDomain(),
			'tstamp' => ($request->getTimeStamp() ? $request->getTimeStamp() : $GLOBALS['EXEC_TIME']),
		);

		$qb = $this->connection->createQueryBuilder();
		$qb->insert(self::TABLE)->values($fields);
		$qb->execute();

		return $this->connection->lastInsertId(self::TABLE);
	}

	/**
	 * Removes a request element.
	 *
	 * @param Request $request
	 * @return void
	 */
	public function remove(Request $request) {
		if (!$request->getIdentifier()) {
			throw new \LogicException('Cannot remove element without an identifier.');
		}

		$qb = $this->connection->createQueryBuilder();
		$qb
            ->delete(self::TABLE)
            ->where(
		        $qb->expr()->eq('uid', $qb->createNamedParameter($request->getIdentifier(), \PDO::PARAM_INT))
            )
            ->execute();
	}

	/**
	 * Loads a request element by identifier.
	 *
	 * @param integer $identifier
	 * @return Request|null
	 */
	public function loadByIdentifier($identifier) {
		$request = NULL;

		$qb = $this->connection->createQueryBuilder();
		$statement = $qb
            ->select('*')
            ->from(self::TABLE)
            ->where(
                $qb->expr()->eq('uid', $qb->createNamedParameter($identifier, \PDO::PARAM_INT))
            )
            ->execute();

		while ($rows = $statement->fetch()) {
			/** @var Request $request */
            $request = GeneralUtility::makeInstance(
                Request::class,
                $rows['beuser'],
                $rows['session'],
                $rows['domain'],
                $rows['uid'],
                $rows['tstamp']
            );
		}

		return $request;
	}

	/**
	 * Purges expired request elements.
	 *
	 * @param integer $expiresAfter
	 *
	 * @return void
	 */
	public function purge($expiresAfter) {
		$expiresAfter = intval($expiresAfter);

		if ($expiresAfter <= 0) {
			throw new \LogicException('Elements cannot expire immediatelly or in the past');
		}

		$GLOBALS['TYPO3_DB']->exec_DELETEquery(self::TABLE, 'tstamp < ' . ($GLOBALS['EXEC_TIME'] - $expiresAfter));
	}
}