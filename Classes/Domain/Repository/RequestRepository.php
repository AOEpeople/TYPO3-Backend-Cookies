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

        $GLOBALS['TYPO3_DB']->exec_INSERTquery(self::TABLE, $fields);
        return $GLOBALS['TYPO3_DB']->sql_insert_id();

    }

    /**
     * Removes a request element.
     *
     * @param  Request $request
     * @return void
     */
    public function remove(Request $request)
    {
        if (!$request->getIdentifier()) {
			throw new LogicException('Cannot remove element without an identifier.');
        }
        
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(self::TABLE, 'uid=' . intval($request->getIdentifier()));
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
	    $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', self::TABLE, 'uid=' . intval($identifier));
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

	    $GLOBALS['TYPO3_DB']->exec_DELETEquery(self::TABLE, 'tstamp < ' . ($GLOBALS['EXEC_TIME'] - $exiresAfter));

    }
}
