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

use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

if (!defined('CLEANDECK_APP_PATH')) {
    return exit('No direct script access allowed');
}

$is_admin ??= false;
$agreement_accepted = isset($record_timestamp, $agreement_accepted) &&
    $record_timestamp > 0 && $agreement_accepted > 0;

?>
<div class="container w-100 p-2 text-justify">
    <?php if (isset($agreement_id, $agreement_title, $agreement_content, $creation_timestamp, $created_by_user_id)): ?>
        <h1 class="display-4 pt-4"><?= $agreement_title; ?></h1>
        <?= $agreement_content; ?>
        <?php if ($is_admin): ?>
            <footer class="container m-0 p-0 mt-3 clearfix">
                <ul>
                    <li>
                        <strong>Created: </strong>
                        <time
                            datetime="<?= TimeUtils::timestampToDateString($creation_timestamp, 'Y-m-d H:i'); ?>">
                            <?= TimeUtils::timestampToDateString($creation_timestamp, 'F d, Y'); ?>
                        </time>
                    </li>
                    <li><strong>Created by (user_id)</strong>: <?= $created_by_user_id; ?></li>
                    <li><strong>For employee types</strong>:
                        <?= isset($for_employee_types) ? trim((string) $for_employee_types, ',') : 'N/A'; ?></li>
                    <li><strong>For user id</strong>: <?= $for_user_id ?? 'N/A'; ?></li>
                </ul>
                <a class="btn btn-primary btn-primary-contrast btn-lg"
                   href="<?= UrlUtils::baseUrl('/admin/agreement/modify/' . $agreement_id); ?>">
                    Modify Agreement</a>
            </footer>
        <?php elseif ($agreement_accepted): ?>
            <p class="mt-3 text-success">
                <strong>Accepted</strong>: <?= TimeUtils::timestampToDateString($record_timestamp); ?></p>
        <?php else: ?>
            <form method="post" enctype="application/x-www-form-urlencoded"
                  action="<?= UrlUtils::baseUrl('/agreements/employee/' . $agreement_id); ?>">
                <div class="w-100 w-sm-50 p-0 m-auto mt-4">
                    <?php echo view_main('components/csrf'); ?>
                    <div class="form-check form-switch m-0 mb-3 fs-5 align-middle">
                        <input id="accept" name="accept" class="form-check-input" type="checkbox"
                               role="switch">
                        <label class="form-check-label" for="accept">Understand and Accept Agreement</label>
                    </div>
                    <hr>
                    <?php echo view_main('components/captcha'); ?>
                    <div>
                        <button class="flex-fill btn btn-success btn-success-contrast">
                            Submit
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <p>Content unavailable.</p>
    <?php endif; ?>
</div>
