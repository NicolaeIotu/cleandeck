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

?>
<div class="container w-100 w-sm-75 w-md-50 p-2">
    <h1 class="text-end">Administration</h1>

    <ul class="list-group">
        <?php if ($cleandeck_account_rank >= 50000): ?>
            <li class="list-group-item list-group-item-success mb-2">
                <h2 class="m-0">Employees</h2>
                <ul>
                    <li><a href="<?= UrlUtils::baseUrl('/admin/employees'); ?>" class="fs-4">Employees</a></li>
                </ul>
            </li>
            <li class="list-group-item list-group-item-light mb-2">
                <h2 class="m-0">Agreements</h2>
                <ul>
                    <li><a href="<?= UrlUtils::baseUrl('/admin/agreements'); ?>"
                           class="fs-4">List Agreements</a></li>
                    <li><a href="<?= UrlUtils::baseUrl('/admin/agreement/new'); ?>"
                           class="fs-4">New Agreement</a></li>
                </ul>
            </li>
        <?php endif; ?>
        <li class="list-group-item list-group-item-success">
            <h2 class="m-0">Accounts</h2>
            <ul>
                <li><a href="<?= UrlUtils::baseUrl('/admin/accounts/approve'); ?>"
                       class="fs-4">Approve Accounts</a></li>
                <li><a href="<?= UrlUtils::baseUrl('/admin/accounts/mark-verified'); ?>"
                       class="fs-4">Verify Accounts</a></li>
                <li><a href="<?= UrlUtils::baseUrl('/admin/account/history/search'); ?>"
                       class="fs-4">Account History</a></li>
            </ul>
        </li>
    </ul>
</div>
