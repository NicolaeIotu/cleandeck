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
use Framework\Libraries\Cookie\CookieMessengerReader;
use Framework\Libraries\Utils\UrlUtils;

$cmsg = CleanDeckStatics::getCookieMessage();
$cmsg_form_data = $cmsg['cmsg_form_data'] ?? [];

$status = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'status');
$attended = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'attended');
if ($attended === '') {
    // a small adjustment in order to suggest searching for unattended cases first
    $attended = 'no';
}

$ranked = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'ranked');

?>
<div class="container w-100 w-sm-75 w-md-50 p-2">
    <h1 class="text-end">Search Support Cases</h1>
    <form name="main" method="get"
          action="<?php echo UrlUtils::baseUrl('/support-cases/search/results'); ?>">
        <div class="form-group">
            <label for="user_id">User ID</label>
            <input type="text" class="form-control" id="user_id" name="user_id"
                   value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'user_id'); ?>"
                   autocomplete="off">
        </div>
        <div class="form-group">
            <label for="content">Text Content</label>
            <input type="text" class="form-control" id="content" name="content"
                   value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'content'); ?>"
                   autocomplete="on">
        </div>
        <div class="form-group">
            <label for="topic">Topic</label>
            <input type="text" class="form-control" id="topic" name="topic"
                   value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'topic'); ?>"
                   autocomplete="on">
        </div>
        <div class="form-group">
            <label for="status">Case Status</label>
            <select class="form-select px-3 py-1" id="status" name="status">
                <option selected>--</option>
                <option value="opened"<?= $status === 'opened' ? ' selected' : '';?>>
                    Opened
                </option>
                <option value="closed"<?= $status === 'closed' ? ' selected' : '';?>>
                    Closed
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="attended">Attended Status</label>
            <select class="form-select px-3 py-1" id="attended" name="attended">
                <option>--</option>
                <option value="yes"<?= $attended === 'yes' ? ' selected' : '';?>>
                    Attended
                </option>
                <option value="no"<?= $attended === 'no' ? ' selected' : '';?>>
                    Unattended
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="ranked">Ranked Status</label>
            <select class="form-select px-3 py-1" id="ranked" name="ranked">
                <option selected>--</option>
                <option value="yes"<?= $ranked === 'yes' ? ' selected' : '';?>>
                    Ranked
                </option>
                <option value="no"<?= $ranked === 'no' ? ' selected' : '';?>>
                    Unranked
                </option>
                <option value="missing_support"<?= $ranked === 'missing_support' ? ' selected' : '';?>>
                    Missing Support Ranking
                </option>
                <option value="missing_owner"<?= $ranked === 'missing_owner' ? ' selected' : '';?>>
                    Missing Owner Ranking
                </option>
            </select>
        </div>
        <div class="form-group text-end">
            <button type="submit" class="btn btn-primary btn-primary-contrast">Search Support Cases</button>
        </div>
    </form>
</div>
