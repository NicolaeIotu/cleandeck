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

$mfa = null;
$agree_tc = null;
$agree_cookies = null;
$agree_privacy = null;
$subscribed_newsletter = null;
$subscribed_promotions = null;
$activation_timestamp_1 = null;
$activation_timestamp_2 = null;
$activation_timestamp_3 = null;
$activation_timestamp_4 = null;
$activation_timestamp_5 = null;
$notification_options = null;

$details_list =
    isset($custom_data, $custom_data['details_list']) ? $custom_data['details_list'] : [];
if (isset($custom_data, $custom_data['details_list'])) {
    $details_list = $custom_data['details_list'];

    $mfa = isset($details_list['mfa_option']) && is_string($details_list['mfa_option']) ? ucfirst($details_list['mfa_option']) : 'None';
    $agree_tc = isset($details_list['agree_tc_timestamp']) && $details_list['agree_tc_timestamp'] > 0;
    $agree_cookies = isset($details_list['agree_cookies_timestamp']) && $details_list['agree_cookies_timestamp'] > 0;
    $agree_privacy = isset($details_list['agree_privacy_timestamp']) && $details_list['agree_privacy_timestamp'] > 0;
    $subscribed_newsletter = isset($details_list['subscribed_newsletter_timestamp']) &&
        $details_list['subscribed_newsletter_timestamp'] > 0;
    $subscribed_promotions = isset($details_list['subscribed_promotions_timestamp']) &&
        $details_list['subscribed_promotions_timestamp'] > 0;
    $activation_timestamp_1 = isset($details_list['activation_timestamp_1']) && $details_list['activation_timestamp_1'] !== '';
    $activation_timestamp_2 = isset($details_list['activation_timestamp_2']) && $details_list['activation_timestamp_2'] !== '';
    $activation_timestamp_3 = isset($details_list['activation_timestamp_3']) && $details_list['activation_timestamp_3'] !== '';
    $activation_timestamp_4 = isset($details_list['activation_timestamp_4']) && $details_list['activation_timestamp_4'] !== '';
    $activation_timestamp_5 = isset($details_list['activation_timestamp_5']) && $details_list['activation_timestamp_5'] !== '';

    $notification_options = $details_list['notification_options'] ?? 'none';

    if (isset($picture) && is_string($picture)) {
        $img_src = UrlUtils::baseUrl('/misc/user-pics/' . $picture);
    }
}

?>
<?= UrlUtils::link(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/css/pages/account-full-details.css'),
    ['type' => 'text/css', 'rel' => 'stylesheet', 'referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
<div class="container w-100 w-sm-75 w-lg-50 p-2">
    <h1 class="text-end">Account Details</h1>
    <?php if (isset($img_src)): ?>
        <div id="profile_picture_img_holder" class="m-0 p-0 text-center">
            <img id="profile_picture_img" alt="Profile picture"
                 class="img-thumbnail m-auto border rounded text-end clearfix"
                 data-src="<?= $img_src; ?>">
        </div>
    <?php endif; ?>
    <p class="pb-2">
        <sub class="float-end text-end">
            <a href="<?php echo UrlUtils::baseUrl('change-user-details'); ?>"
               title="Change Details" target="_self" class="btn text-bg-primary mb-3">Change Details</a>
        </sub>
    </p>
    <table class="table table-sm table-striped table-bordered">
        <tbody class="text-break">
        <tr>
            <th scope="row">Primary Email</th>
            <td><?= $details_list['email'] ?? ''; ?></td>
        </tr>
        <tr>
            <th scope="row">Username</th>
            <td><?= $details_list['username'] ?? ''; ?></td>
        </tr>
        <tr>
            <th scope="row">User ID</th>
            <td><?= $details_list['user_id'] ?? ''; ?></td>
        </tr>
        <tr>
            <th scope="row">MFA</th>
            <td class="fw-bold <?= $mfa !== 'None' ? 'text-success' : 'text-warning'; ?>">
                <?= $mfa; ?></td>
        </tr>
        <tr>
            <th scope="row">First name</th>
            <td><?= $details_list['firstname'] ?? ''; ?></td>
        </tr>
        <tr>
            <th scope="row">Last name</th>
            <td><?= $details_list['lastname'] ?? ''; ?></td>
        </tr>
        <tr>
            <th scope="row">Company name</th>
            <td><?= $details_list['company_name'] ?? ''; ?></td>
        </tr>
        <tr>
            <th scope="row">Profile Intro</th>
            <td><?= nl2br($details_list['intro'] ?? ''); ?></td>
        </tr>
        <tr>
            <th scope="row">Profile Description</th>
            <td><?= nl2br($details_list['description'] ?? ''); ?></td>
        </tr>
        <tr>
            <th scope="row">Contact</th>
            <td><?= nl2br($details_list['contact_details'] ?? ''); ?></td>
        </tr>
        <tr>
            <th scope="row">Web Profile</th>
            <td><?= nl2br($details_list['web_profile'] ?? ''); ?></td>
        </tr>
        <tr>
            <th scope="row">Date of Birth</th>
            <td><?= $details_list['date_of_birth'] ?? ''; ?></td>
        </tr>
        <tr>
            <th scope="row">Gender</th>
            <td><?= $details_list['gender'] ?? ''; ?></td>
        </tr>
        <tr>
            <th scope="row">Country</th>
            <td><?= ucfirst((string)($details_list['country'] ?? '')); ?></td>
        </tr>
        <tr>
            <th scope="row">City</th>
            <td><?= ucfirst((string)($details_list['city'] ?? '')); ?></td>
        </tr>
        <tr>
            <th scope="row">Address</th>
            <td><?= $details_list['address'] ?? ''; ?></td>
        </tr>
        <tr>
            <th scope="row">Postal Code</th>
            <td><?= $details_list['postal_code'] ?? ''; ?></td>
        </tr>
        <tr>
            <th scope="row">Secondary email 1</th>
            <td><?php echo $details_list['email_1'] ?? '';
                if ($activation_timestamp_1 === true) {
                    echo ' *';
                } ?></td>
        </tr>
        <tr>
            <th scope="row">Secondary email 2</th>
            <td><?php echo $details_list['email_2'] ?? '';
                if ($activation_timestamp_2 === true) {
                    echo ' *';
                } ?></td>
        </tr>
        <tr>
            <th scope="row">Secondary email 3</th>
            <td><?php echo $details_list['email_3'] ?? '';
                if ($activation_timestamp_3 === true) {
                    echo ' *';
                } ?></td>
        </tr>
        <tr>
            <th scope="row">Secondary email 4</th>
            <td><?php echo $details_list['email_4'] ?? '';
                if ($activation_timestamp_4 === true) {
                    echo ' *';
                } ?></td>
        </tr>
        <tr>
            <th scope="row">Secondary email 5</th>
            <td><?php echo $details_list['email_5'] ?? '';
                if ($activation_timestamp_5 === true) {
                    echo ' *';
                } ?></td>
        </tr>
        <tr>
            <th scope="row">Agree with Terms and Condition</th>
            <td class="<?= $agree_tc === true ? 'text-success fw-bold' : 'text-warning'; ?>">
                <?= $agree_tc === true ? 'YES' : 'NO'; ?>
            </td>
        </tr>
        <tr>
            <th scope="row">Agree with Cookies Policy</th>
            <td class="<?= $agree_cookies === true ? 'text-success fw-bold' : 'text-warning'; ?>">
                <?= $agree_cookies === true ? 'YES' : 'NO'; ?></td>
        </tr>
        <tr>
            <th scope="row">Agree with Privacy Policy</th>
            <td class="<?= $agree_privacy === true ? 'text-success fw-bold' : 'text-warning'; ?>">
                <?= $agree_privacy === true ? 'YES' : 'NO'; ?></td>
        </tr>
        <tr>
            <th scope="row">Subscribed to newsletters</th>
            <td class="<?= $subscribed_newsletter === true ? 'text-success fw-bold' : 'text-warning'; ?>">
                <?= $subscribed_newsletter === true ? 'YES' : 'NO'; ?></td>
        </tr>
        <tr>
            <th scope="row">Subscribed to promotions</th>
            <td class="<?= $subscribed_promotions === true ? 'text-success fw-bold' : 'text-warning'; ?>">
                <?= $subscribed_promotions === true ? 'YES' : 'NO'; ?></td>
        </tr>
        <tr>
            <th scope="row">Notification Options</th>
            <td><?php echo $notification_options; ?></td>
        </tr>
        <?php if (isset($details_list['employee_type'])): ?>
            <tr>
                <th scope="row">Employment Position</th>
                <td><?= ucfirst((string) $details_list['employee_type']); ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <sub>* email pending activation</sub>
</div>
<?= UrlUtils::link(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/css/images-autoload.css'),
    ['type' => 'text/css', 'rel' => 'stylesheet', 'referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
<?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/images-autoload.js'),
    ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous', 'type' => 'module']);?>
