<?php
namespace AOE\BeCookies\Domain\Repository;

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
class RequestRepository implements SingletonInterface
{

    /**
     * @const TABLE becookie table.
     */
    const TABLE = 'tx_becookies_request';

    /**
     * @var \TYPO3\CMS\Core\Database\Connection
     */
    protected $connection;

    /**
     * @var \TYPO3\CMS\Core\Database\ConnectionPool
     * @inject
     */
    private $connectionPool;

    /**
     * Initializes class instance.
     */
    public function initializeObject() {
        $this->connection = $this->connectionPool->getConnectionForTable(self::TABLE);
    }

    /*
     * Persists a request element.
     *
     * @param Request $request
     * @return integer
     */
    public function persist(Request $request)
    {
        if (is_integer($request->getIdentifier())) {
            throw new \LogicException('Updating existing elements is not allowed.');
        }

        $fields = [
            'beuser'  => $request->getBackendUserId(),
            'session' => $request->getSessionId(),
            'domain'  => $request->getDomain(),
            'tstamp'  => is_integer($request->getTimeStamp()) ? $request->getTimeStamp() : $GLOBALS['EXEC_TIME'],
        ];

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->insert(self::TABLE)->values($fields);
        $queryBuilder->execute();

        return $this->connection->lastInsertId(self::TABLE);
    }

    /**
     * Removes a request element.
     *
     * @param  Request $request
     * @return void
     */
    public function remove(Request $request)
    {
        if (is_integer($request->getIdentifier())) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder
                ->delete(self::TABLE)
                ->where($queryBuilder->expr()->eq(
                    'uid', $queryBuilder->createNamedParameter($request->getIdentifier(), \PDO::PARAM_INT)
                ))
                ->execute();
        }
    }

    /**
     * Loads a request element by identifier.
     *
     * @param integer $identifier
     * @return Request|null
     */
    public function loadByIdentifier($identifier)
    {
        $request = null;

        $queryBuilder = $this->connection->createQueryBuilder();
        $statement = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($identifier, \PDO::PARAM_INT)))
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
    public function purge($expiresAfter)
    {
        $expiresAfter = intval($expiresAfter);

        if ($expiresAfter <= 0) {
            throw new \LogicException('Elements cannot expire immediately or in the past');
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->delete(self::TABLE)
            ->where($queryBuilder->expr()->lt(
                'tstamp', $queryBuilder->createNamedParameter(($GLOBALS['EXEC_TIME'] - $expiresAfter), \PDO::PARAM_INT)
            ))
            ->execute();
    }
}
