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

use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

$valid_history = false;
$account_history_ordered = [];
if (isset($account_email, $account_history) && is_array($account_history)) {
    $valid_history = true;

    // the order of entries
    $account_history_ordered = [
        "#" => [
            'header' => '#',
            'title' => 'Entry Number',
            'section' => 'common',
        ],
        // common
        'event_timestamp' => [
            'header' => 'Time',
            'title' => 'Event Time',
            'section' => 'common',
        ],
        'modified_by_user_id' => null,
        'modified_by_email' => [
            'header' => 'Author',
            'title' => 'Modified By',
            'section' => 'common',
        ],
        'endpoint' => [
            'header' => 'Endpoint',
            'title' => 'Endpoint',
            'section' => 'common',
        ],
        'modification_type' => [
            'header' => 'Type',
            'title' => 'Event Type',
            'section' => 'common',
        ],
        // user_details
        'email' => [
            'header' => 'Email',
            'title' => 'Email',
            'section' => 'user_details',
        ],
        'username' => [
            'header' => 'Username',
            'title' => 'Username',
            'section' => 'user_details',
        ],
        'firstname' => [
            'header' => 'Firstname',
            'title' => 'Firstname',
            'section' => 'user_details',
        ],
        'lastname' => [
            'header' => 'Lastname',
            'title' => 'Lastname',
            'section' => 'user_details',
        ],
        'company_name' => [
            'header' => 'Company',
            'title' => 'Company Name',
            'section' => 'user_details',
        ],
        'intro' => [
            'header' => 'Intro',
            'title' => 'Intro',
            'section' => 'user_details',
        ],
        'description' => [
            'header' => 'Description',
            'title' => 'Description',
            'section' => 'user_details',
        ],
        'contact_details' => [
            'header' => 'Contact',
            'title' => 'Contact Details',
            'section' => 'user_details',
        ],
        'web_profile' => [
            'header' => 'Web',
            'title' => 'Web Profile',
            'section' => 'user_details',
        ],
        'date_of_birth' => [
            'header' => 'Birthday',
            'title' => 'Birthday',
            'section' => 'user_details',
        ],
        'gender' => [
            'header' => 'Gender',
            'title' => 'Gender',
            'section' => 'user_details',
        ],
        'country' => [
            'header' => 'Country',
            'title' => 'Country',
            'section' => 'user_details',
        ],
        'city' => [
            'header' => 'City',
            'title' => 'City',
            'section' => 'user_details',
        ],
        'address' => [
            'header' => 'Address',
            'title' => 'Address',
            'section' => 'user_details',
        ],
        'postal_code' => [
            'header' => 'Post Code',
            'title' => 'Postal Code',
            'section' => 'user_details',
        ],
        'avatar' => [
            'header' => 'Avatar',
            'title' => 'Avatar',
            'section' => 'user_details',
        ],
        'pictures' => [
            'header' => 'Pictures',
            'title' => 'Pictures',
            'section' => 'user_details',
        ],
        'notification_options' => [
            'header' => 'Notify',
            'title' => 'Notification Options',
            'section' => 'user_details',
        ],
        'agree_tc_timestamp' => [
            'header' => 'Agree TC',
            'title' => 'Time Agree Terms and Conditions',
            'section' => 'user_details',
        ],
        'agree_cookies_timestamp' => [
            'header' => 'Agree Cookies',
            'title' => 'Time Agree Cookies',
            'section' => 'user_details',
        ],
        'agree_privacy_timestamp' => [
            'header' => 'Agree Privacy',
            'title' => 'Time Agree Privacy',
            'section' => 'user_details',
        ],
        'subscribed_newsletter_timestamp' => [
            'header' => 'Newsletter',
            'title' => 'Time Subscribed Newsletter',
            'section' => 'user_details',
        ],
        'subscribed_promotions_timestamp' => [
            'header' => 'Promotions',
            'title' => 'Time Subscribed Promotions',
            'section' => 'user_details',
        ],
        'primary_phone' => [
            'header' => 'Phone',
            'title' => 'Primary Phone',
            'section' => 'user_details',
        ],
        'primary_phone_changes_count' => [
            'header' => 'Phone Changes',
            'title' => 'Count Phone Changes',
            'section' => 'user_details',
        ],
        'new_primary_phone' => [
            'header' => 'New Phone',
            'title' => 'New Primary Phone (to be activated)',
            'section' => 'user_details',
        ],
        'new_primary_phone_activation_hash' => [
            'header' => 'New Phone Hash',
            'title' => 'New Primary Phone Activation Hash',
            'section' => 'user_details',
        ],
        'new_primary_phone_activation_timestamp' => [
            'header' => 'Time Phone Request',
            'title' => 'Time Request New Primary Phone',
            'section' => 'user_details',
        ],
        // emails
        'primary_email' => [
            'header' => 'Primary Email',
            'title' => 'Primary Email',
            'section' => 'emails',
        ],
        'new_primary_email' => [
            'header' => 'New Email Primary',
            'title' => 'New Email Primary',
            'section' => 'emails',
        ],
        'email_1' => [
            'header' => 'Email 1',
            'title' => 'Email 1',
            'section' => 'emails',
        ],
        'email_2' => [
            'header' => 'Email 2',
            'title' => 'Email 2',
            'section' => 'emails',
        ],
        'email_3' => [
            'header' => 'Email 3',
            'title' => 'Email 3',
            'section' => 'emails',
        ],
        'email_4' => [
            'header' => 'Email 4',
            'title' => 'Email 4',
            'section' => 'emails',
        ],
        'email_5' => [
            'header' => 'Email 5',
            'title' => 'Email 5',
            'section' => 'emails',
        ],
        'activation_hash_new_p_e' => [
            'header' => 'Hash Email Primary',
            'title' => 'Activation Hash of New Primary Email',
            'section' => 'emails',
        ],
        'activation_hash_1' => [
            'header' => 'Email 1 Hash',
            'title' => 'Activation Hash of Email 1',
            'section' => 'emails',
        ],
        'activation_hash_2' => [
            'header' => 'Email 2 Hash',
            'title' => 'Activation Hash of Email 2',
            'section' => 'emails',
        ],
        'activation_hash_3' => [
            'header' => 'Email 3 Hash',
            'title' => 'Activation Hash of Email 3',
            'section' => 'emails',
        ],
        'activation_hash_4' => [
            'header' => 'Email 4 Hash',
            'title' => 'Activation Hash of Email 4',
            'section' => 'emails',
        ],
        'activation_hash_5' => [
            'header' => 'Email 5 Hash',
            'title' => 'Activation Hash of Email 5',
            'section' => 'emails',
        ],
        'activation_timestamp_new_p_e' => [
            'header' => 'New Email Time',
            'title' => 'Time Request New Primary Email',
            'section' => 'emails',
        ],
        'activation_timestamp_1' => [
            'header' => 'Email 1 Time',
            'title' => 'Time Request New Email 1',
            'section' => 'emails',
        ],
        'activation_timestamp_2' => [
            'header' => 'Email 2 Time',
            'title' => 'Time Request New Email 2',
            'section' => 'emails',
        ],
        'activation_timestamp_3' => [
            'header' => 'Email 3 Time',
            'title' => 'Time Request New Email 3',
            'section' => 'emails',
        ],
        'activation_timestamp_4' => [
            'header' => 'Email 4 Time',
            'title' => 'Time Request New Email 4',
            'section' => 'emails',
        ],
        'activation_timestamp_5' => [
            'header' => 'Email 5 Time',
            'title' => 'Time Request New Email 5',
            'section' => 'emails',
        ],
    ];
}

?>
<?= UrlUtils::link(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/css/pages/account-history.css'),
    ['type' => 'text/css', 'rel' => 'stylesheet', 'referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']); ?>
<div class="container w-100 w-sm-100 p-2">
    <?php if (!isset($is_admin) || $is_admin !== true) : ?>
        <div class="alert alert-warning" role="alert">
            <p>Insufficient permissions</p>
        </div>
    <?php else: ?>
    <?php if ($valid_history): ?>
        <h1 class="text-end">Account History - <?= $account_email; ?></h1>
        <p class="text-end">Shows changes done to tables <strong>emails</strong> and <strong>user_details</strong> only.
        </p>
    <?php if ($account_history === []): ?>
        <p>Something went wrong. There's nothing to show here.</p>
    <?php else: ?>
        <div class="w-100 m-0 mb-2 p-0 text-smaller d-inline-block align-bottom">
            <div class="m-0 ms-4 mb-1 p-0 float-end">
                <input class="form-check-input" type="checkbox" value="1" id="enable-user-details-history"
                       checked>
                <label for="enable-user-details-history"
                       class="bg-dark text-white p-1 px-3 fw-bold rounded">
                    User Details History</label>
            </div>
            <div class="m-0 mb-1 p-0 float-end">
                <input class="form-check-input" type="checkbox" value="1" id="enable-email-history" checked>
                <label for="enable-email-history"
                       class="bg-primary-contrast text-white p-1 px-3 fw-bold rounded">
                    Email History</label>
            </div>
            <div class="m-0 me-3 mb-1 p-0 float-start">
                        <span class="bg-success-contrast text-white p-1 px-3 fw-bold rounded">
                            Common Details</span>
            </div>
        </div>
        <div class="row w-100 m-0 p-0 text-smaller border border-secondary rounded">
            <div class="col-3 col-lg-2 m-0 p-0 border-end border-secondary overflow-hidden fw-bolder text-nowrap">
                <?php foreach ($account_history_ordered as $definition): ?>
                    <?php if (!is_null($definition)): ?>
                        <?php $section_classes = match ($definition['section']) {
                            'common' => ' text-success-contrast',
                            'emails' => ' text-primary-contrast emails-data',
                            default => ' user-details-data'
                        }; ?>
                        <div class="w-100 m-1 p-1<?= $section_classes; ?> border-bottom">
                            <p class="m-0 p-0" title="<?= $definition['title']; ?>">
                                <?= $definition['header']; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php
            $i = -1;
            $account_history_length = count($account_history);
            ?>
            <div id="scroll-history" class="col-9 col-lg-10 m-0 p-0">
                <?php foreach ($account_history as $history_entry): ?>
                    <?php
                    if (isset($history_entry['primary_email'])) {
                        $bg_emails_history = ' text-primary-contrast';
                        $section_class = ' emails-entry';
                    } else {
                        $bg_emails_history = '';
                        $section_class = ' user-details-entry';
                    }
                    ++$i;
                    ?>
                    <div class="m-0 p-0<?= $bg_emails_history . $section_class; ?> d-inline-block align-top w-25">
                        <?php foreach ($account_history_ordered as $key => $definition): ?>
                            <?php if (is_null($definition)) {
                                continue;
                            } ?>
                            <?php
                            $value = $history_entry[$key] ?? '';
                            $print_value = '';
                            $print_title = '';
                            switch ($key) {
                                case '#':
                                    $print_title = ($account_history_length - $i) . '/' . $account_history_length;
                                    $print_value = '<strong>' . $print_title . '</strong>';
                                    break;
                                case 'event_timestamp':
                                    if (is_int($value) && $value > 0) {
                                        $print_title = TimeUtils::timestampToDateString($value);
                                        $print_value = '<strong>' . $print_title . '</strong>';
                                    } elseif ($value === 0) {
                                        $print_value = 0;
                                        $print_title = $print_value;
                                    }
                                    break;
                                case 'agree_tc_timestamp':
                                case 'agree_cookies_timestamp':
                                case 'agree_privacy_timestamp':
                                case 'subscribed_newsletter_timestamp':
                                case 'subscribed_promotions_timestamp':
                                case 'agree_toe_timestamp':
                                case 'agree_nda_timestamp':
                                case 'new_primary_phone_activation_timestamp':
                                    if (is_int($value) && $value > 0) {
                                        $print_value = TimeUtils::timestampToDateString($value);
                                    } elseif ($value === 0) {
                                        $print_value = 0;
                                    }
                                    $print_title = $print_value;
                                    break;
                                case 'modified_by_email':
                                    $print_value = $value;
                                    $print_title = 'User ID: ' . $history_entry['modified_by_user_id'];
                                    break;
                                default:
                                    $print_value = $value;
                                    $print_title = $print_value;
                                    break;
                            }
                            if ($print_value === '') {
                                $print_value = '&empty;';
                                $print_title = 'Null, default or missing value';
                            }

                            $entry_class = match ($definition['section']) {
                                'common' => '',
                                'emails' => ' emails-data',
                                default => ' user-details-data'
                            };
                            ?>
                            <p class="m-1 p-1 border-bottom text-nowrap overflow-hidden<?= $entry_class; ?>"
                               title="<?= $print_title; ?>"><?= $print_value; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/pages/account-history.js'),
        ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']); ?>
    <?php endif; ?>
    <?php else: ?>
        <h1 class="text-end">Account History</h1>
        <p>Invalid or missing data.</p>
    <?php endif; ?>
    <?php endif; ?>
</div>
