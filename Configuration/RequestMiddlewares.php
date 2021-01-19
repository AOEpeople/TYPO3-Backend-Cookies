<?php

$rearrangedMiddlewares = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    TYPO3\CMS\Core\Configuration\Features::class
)->isFeatureEnabled('rearrangedRedirectMiddlewares');

return [
    'frontend' => [
        'aoe/becookies/frontend' => [
            'target' => \Aoe\Becookies\Middleware\Frontend::class,
            'before' => [
                $rearrangedMiddlewares ? 'typo3/cms-frontend/base-redirect-resolver' : 'typo3/cms-frontend/page-resolver',
            ],
            'after' => [
                $rearrangedMiddlewares ? 'typo3/cms-frontend/authentication' : 'typo3/cms-frontend/static-route-resolver',
            ],
        ],
    ],
];