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


$details_list = [];
if (isset($custom_data, $custom_data['details_list'])) {
    $details_list = $custom_data['details_list'];
}
?>
<div class="container w-100 w-sm-75 w-md-50 p-0">
    <h1 class="text-end">Failed Logins</h1>
    <table class="table table-sm border">
        <thead>
        <tr>
            <th class="w-10" scope="col">#</th>
            <th scope="col">Failed Logins</th>
        </tr>
        </thead>
        <tbody class="text-smaller">
        <?php foreach ($details_list as $key => $ufl_details) { ?>
            <tr class="<?php echo($key % 2 === 0 ? 'table-secondary' : 'table-light'); ?>">
                <th scope="row">#<?php echo($key + 1); ?></th>
                <td>
                    <dl class="row">
                        <dt class="col-md-4">IP</dt>
                        <dd class="col-md-8"><?php echo $ufl_details['ip']; ?></dd>

                        <dt class="col-md-4">Temporary ban</dt>
                        <dd class="col-md-8"><?php echo(isset($ufl_details['ban_timestamp']) &&
                            $ufl_details['ban_timestamp'] !== 0 ?
                                gmdate(
                                    'Y/m/d H:i:s',
                                    (int)$ufl_details['ban_timestamp'] / 1000
                                ) . ' UTC' : 'false');
            ?></dd>

                        <dt class="col-md-4">Total failed logins</dt>
                        <dd class="col-md-8"><?php echo $ufl_details['failed_logins']; ?></dd>

                        <dt class="col-md-4">First failed login</dt>
                        <dd class="col-md-8"><?php
                            echo gmdate(
                                'Y/m/d H:i:s',
                                (int)$ufl_details['first_failed_login_timestamp'] / 1000
                            ) . ' UTC';
            ?></dd>

                        <dt class="col-md-4">User Agent</dt>
                        <dd class="col-md-8"><?php echo $ufl_details['user_agent']; ?></dd>
                    </dl>
                </td>
            </tr>
        <?php }
 ?>
        </tbody>
    </table>
</div>
