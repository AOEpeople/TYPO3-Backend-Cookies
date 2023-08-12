<?php
namespace Aoe\Becookies\Domain\Repository;

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

use Aoe\Becookies\Domain\Model\Request;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Request repository
 *
 * @author Oliver Hader <oliver@typo3.org>
 * @package becookies
 *
 */
class RequestRepository {
    /**
     * @var string
     */
    const TABLE = 'tx_becookies_domain_model_request';

    /*
     * Persists a request element.
     *
     * @param Request $request
     * @return integer
     */
    public function add(Request $request)
    {
        if ($request->getUid()) {
            throw new LogicException('Updating existing elements is not allowed.');
        }

        $fields = array(
            'beuser' => $request->getBeuser(),
            'session' => $request->getSession(),
            'domain' => $request->getDomain(),
            'tstamp' => ($request->getTstamp() ? $request->getTstamp() : $GLOBALS['EXEC_TIME']),
        );

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE);
        $queryBuilder
            ->insert(self::TABLE)
            ->values($fields)
            ->execute();

        return $queryBuilder->getConnection()->lastInsertId();
    }

    /**
     * Removes a request element.
     *
     * @param Request $request
     * @return void
     */
    public function remove(Request $request)
    {
        if (!$request->getUid()) {
            throw new LogicException('Cannot remove element without an identifier.');
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE);
        $queryBuilder
            ->delete(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($request->getUid(), \PDO::PARAM_INT))
            )
            ->execute();
    }

    /**
     * Loads a request element by identifier.
     *
     * @param integer $uid
     * @return Request
     */
    public function findByUid($uid)
    {
        $request = null;

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE);
        $rows = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAll();

        if (count($rows)) {
            $request = GeneralUtility::makeInstance(
                Request::class,
                $rows[0]['beuser'],
                $rows[0]['session'],
                $rows[0]['domain'],
                $rows[0]['uid'],
                $rows[0]['tstamp']
            );
        }

        return $request;
    }

    /**
     * Purges expired request elements.
     *
     * @param integer $exiresAfter
     * @return void
     */
    public function purge($exiresAfter)
    {
        $exiresAfter = intval($exiresAfter);

        if ($exiresAfter <= 0) {
            throw new LogicException('Elements cannot expire immediatelly or in the past');
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE);
        $queryBuilder
            ->delete(self::TABLE)
            ->where(
                $queryBuilder->expr()->lt('tstamp', ($GLOBALS['EXEC_TIME'] - $exiresAfter))
            )
            ->execute();
    }
}

