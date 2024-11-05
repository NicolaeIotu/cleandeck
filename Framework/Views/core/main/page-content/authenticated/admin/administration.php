<?php

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

if (!defined('CLEANDECK_APP_PATH')) {
    return exit('No direct script access allowed');
}

use Framework\Libraries\CleanDeckStatics;
use Framework\Libraries\Utils\UrlUtils;

$cleandeck_account_rank = CleanDeckStatics::getAccountRank();

$structure = [
    'Support' => [
        'rank' => 50,
        'links' => [
            '/support-cases/overview' => 'Support Cases Overview',
            '/support-cases/search' => 'Search Support Cases',
        ],
    ],
    'Accounts' => [
        'rank' => 50000,
        'links' => [
            '/admin/accounts/approve' => 'Approve Accounts',
            '/admin/accounts/mark-verified' => 'Verify Accounts',
            '/admin/account/history/search' => 'Account History',
        ],
    ],
    'FAQs' => [
        'rank' => 50,
        'links' => [
            '/faqs' => 'Search FAQs',
            '/admin/faq/new' => 'New FAQ',
        ],
    ],
    'Articles' => [
        'rank' => 50,
        'links' => [
            '/articles' => 'Search Articles',
            '/admin/article/new' => 'New Article',
        ],
    ],
    'Employees' => [
        'rank' => 50000,
        'links' => [
            '/admin/employees' => 'Employees Administration',
        ],
    ],
    'Employee Agreements' => [
        'rank' => 50000,
        'links' => [
            '/admin/agreements' => 'List Agreements',
            '/admin/agreement/new' => 'New Agreement',
        ],
    ],
];

?>
<div class="container w-100 w-md-75 p-2">
    <h1 class="text-end">Administration</h1>
    <div class="row clearfix mb-3">
        <?php foreach ($structure as $section => $section_details): ?>
            <?php if ($cleandeck_account_rank >= $section_details['rank']): ?>
                <div class="col-sm-4 mb-2">
                    <div class="card h-100">
                        <div class="card-header h5 fw-bold text-bg-secondary">
                            <p class="m-0 p-0 text-bg-secondary"><?= $section; ?></p>
                        </div>
                        <div class="card-body w-100 p-0 list-group">
                            <?php if (is_array($section_details['links'])): ?>
                                <?php foreach ($section_details['links'] as $href => $title): ?>
                                    <a href="<?= UrlUtils::baseUrl($href); ?>" title="<?= $title; ?>"
                                       class="list-group-item list-group-item-action text-primary"><?= $title; ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
