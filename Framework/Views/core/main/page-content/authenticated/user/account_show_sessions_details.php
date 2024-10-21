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

use Framework\Libraries\Utils\UrlUtils;

$details_list = [];
if (isset($custom_data, $custom_data['details_list'])) {
    $details_list = $custom_data['details_list'];
}


?>
<div class="container w-100 w-sm-75 w-md-50 p-0">
    <h1 class="text-end">Active Sessions</h1>
    <table class="table table-sm border">
        <thead>
        <tr>
            <th class="w-10" scope="col">#</th>
            <th scope="col">Session Details</th>
        </tr>
        </thead>
        <tbody class="m-0 text-smaller">
        <?php foreach ($details_list as $key => $session_details): ?>
            <?php if (isset($session_details['is_this_session']) && $session_details['is_this_session'] === true) {
                $row_bg = 'table-success';
            } else {
                $row_bg = $key % 2 === 0 ? 'table-secondary' : 'table-light';
            }
            ?>
            <tr class="<?= $row_bg ?>">
                <th scope="row">#<?php echo($key + 1); ?></th>
                <td>
                    <dl class="row">
                        <dt class="col-md-4">First login</dt>
                        <dd class="col-md-8"><?php
                            echo gmdate(
                                    'Y/m/d H:i:s',
                                    (int)$session_details['session_creation_timestamp'] / 1000
                                ) . ' UTC';
                            ?></dd>
                        <dt class="col-md-4">Last activity</dt>
                        <dd class="col-md-8"><?php
                            echo gmdate(
                                    'Y/m/d H:i:s',
                                    (int)$session_details['last_auth_timestamp'] / 1000
                                ) . ' UTC'; ?></dd>
                        <dt class="col-md-4">Last IP</dt>
                        <dd class="col-md-8"><?php echo $session_details['ip']; ?></dd>
                        <dt class="col-md-4">Persistent</dt>
                        <dd class="col-md-8"><?php echo($session_details['remember_me'] ? 'true' : 'false'); ?></dd>
                        <dt class="col-md-4">MFA session</dt>
                        <dd class="col-md-8"><?php echo(isset($session_details['mfa_timestamp']) &&
                            $session_details['mfa_timestamp'] !== 0 ? 'true' : 'false');
                            ?></dd>
                        <dt class="col-md-4">MFA pending</dt>
                        <dd class="col-md-8"><?php echo(isset($session_details['mfa_code']) &&
                            $session_details['mfa_code'] !== "" ? 'true' : 'false');
                            ?></dd>
                        <dt class="col-md-4">Browser</dt>
                        <dd class="col-md-8"><?php echo $session_details['browser'] . ' ' .
                                $session_details['browser_version']; ?></dd>
                        <dt class="col-md-4">OS</dt>
                        <dd class="col-md-8"><?php echo $session_details['os'] . ' ' .
                                $session_details['os_version']; ?></dd>
                        <?php if (!isset($session_details['is_this_session']) || $session_details['is_this_session'] !== true): ?>
                            <dt class="col-md-4"></dt>
                            <dd class="col-md-8 m-0">
                                <form name="form_del_<?= $key; ?>" method="post"
                                      action="<?= UrlUtils::baseUrl('/logout/session/' . $session_details['internal_id']); ?>">
                                    <?php echo view_main('components/csrf'); ?>
                                    <button class="btn btn-secondary m-0">Delete session</button>
                                </form>
                            </dd>
                        <?php endif; ?>
                    </dl>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
