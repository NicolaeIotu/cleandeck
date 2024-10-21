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
use Framework\Libraries\Utils\Locations;
use Framework\Libraries\Utils\UrlUtils;

// prevent errors in editor
$cleandeck_user_details = CleanDeckStatics::getUserDetails();
$cmsg = CleanDeckStatics::getCookieMessage();
$cmsg_form_data = $cmsg['cmsg_form_data'] ?? [];

/**
 * @param array<string, mixed> $user_details
 * @param array<string, mixed> $cmsg_form_data
 * @param string|null $prop_cfm
 * @param mixed|null $default
 */
function get_user_details_value(array  $user_details, array $cmsg_form_data,
                                string $prop_ud, string $prop_cfm = null, mixed $default = null): mixed
{
    if ($cmsg_form_data !== []) {
        $old_prop = CookieMessengerReader::getPreviousFormData($cmsg_form_data, $prop_cfm ?? $prop_ud);
        if ($old_prop !== '') {
            return $old_prop;
        }
    }
    return $user_details[$prop_ud] ?? $default ?? '';
}

$gender = get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'gender');
$isMale = stripos((string)$gender, 'm') === 0;
$isFemale = stripos((string)$gender, 'f') === 0;
$isOther = stripos((string)$gender, 'other') === 0;


$country = get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'country');
$countries_list = Locations::COUNTRIES;

$is_subscribed_newsletter = get_user_details_value(
    $cleandeck_user_details,
    $cmsg_form_data,
    'subscribed_newsletter_timestamp',
    'subscribed_newsletter',
    0
);
$is_subscribed_promotions = get_user_details_value(
    $cleandeck_user_details,
    $cmsg_form_data,
    'subscribed_promotions_timestamp',
    'subscribed_promotions',
    0
);

// Must be 128 x 128
$img_src_add = UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/images/profile-picture-add.png');
// Must be 128 x 128
$img_src_error = UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/images/profile-picture-error.png');
$has_picture = false;

$picture = get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'picture');

if (is_null($picture) || $picture === '') {
    if (isset($picture_download_error)) {
        $img_src = $img_src_error;
        $has_picture = true;
    } else {
        $img_src = $img_src_add;
    }
} else {
    $img_src = UrlUtils::baseUrl('/misc/user-pics/' . $picture);
    $has_picture = true;
}

$notification_options = get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'notification_options', null, []);
if (is_string($notification_options)) {
    $notification_options = explode(',', $notification_options);
}

?>
<?= UrlUtils::link(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/css/pages/account-change-details.css'),
    ['type' => 'text/css', 'rel' => 'stylesheet', 'referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
<div class="container w-100 w-sm-75 w-md-50 p-2">
    <h1 class="text-end">Change User Details</h1>
    <div id="picture_main_holder" class="m-0 mb-1 p-0">
        <div id="picture_sub_holder" class="m-0 p-0 text-center" data-img-src-add="<?= $img_src_add; ?>">
            <img id="profile_picture_img"
                 src="<?= $img_src; ?>"
                 class=" img-thumbnail m-auto border rounded text-end clearfix cursor-pointer"
                 alt="Profile picture" title="Change profile picture">
        </div>
        <div id="cont-remove-picture"
             class="m-0 p-0 text-center<?= $has_picture ? ' ' : ' d-none'; ?>">
            <button id="remove-picture" type="button" class="btn btn-danger p-0 ps-2 pe-2 text-light"
                    title="Remove picture">x
            </button>
        </div>
    </div>
    <?php if (isset($picture_download_error)) : ?>
        <div class="alert-warning border rounded border-warning p-1 ps-2 pe-2">
            <small><strong>Cannot load picture</strong></small>
            <small> - <?= $picture_download_error; ?></small>
        </div>
    <?php endif; ?>
    <input type="file" class="form-control-file d-none" id="pictures" name="pictures"
           accept=".jpg, .jpeg, .png, .gif"
           title="Select a new profile picture" alt="Select a new profile picture">
    <input type="hidden" id="has_pic" name="has_pic" form="main" value="<?= isset($picture) ? '1' : '0'; ?>">
    <ul class="nav flex-column p-3 w-100">
        <li class="nav-item mb-2">
            <?php echo UrlUtils::anchor_clean(
                '/change-password',
                'Change Password',
                [
                    'title' => 'Change Password',
                ]
            ); ?>
        </li>
        <li class="nav-item mb-2">
            <?php echo UrlUtils::anchor_clean(
                '/change-primary-email',
                'Change Primary Email (' . $cleandeck_user_details['email'] . ')',
                [
                    'title' => 'Change Primary Email',
                ]
            ); ?>
        </li>
        <li class="nav-item mb-2">
            <?php echo UrlUtils::anchor_clean(
                '/change-username',
                'Change Username (' . $cleandeck_user_details['username'] . ')',
                [
                    'title' => 'Change Username',
                ]
            ); ?>
        </li>
        <li class="nav-item mb-2">
            <?php echo UrlUtils::anchor_clean(
                '/activate-email',
                'Activate Email',
                [
                    'title' => 'Activate Email',
                ]
            ); ?>
        </li>
    </ul>

    <form id="main" method="post" enctype="multipart/form-data"
          action="<?= UrlUtils::baseUrl('/change-user-details/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <div class="form-group">
            <label for="firstname">Firstname</label>
            <input type="text" class="form-control" id="firstname" name="firstname" autocomplete="off"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'firstname'); ?>">
        </div>
        <div class="form-group">
            <label for="lastname">Lastname</label>
            <input type="text" class="form-control" id="lastname" name="lastname" autocomplete="off"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'lastname'); ?>">
        </div>
        <div class="form-group">
            <label for="company_name">Company Name</label>
            <input type="text" class="form-control" id="company_name" name="company_name" autocomplete="off"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'company_name'); ?>">
        </div>
        <div class="form-group">
            <label for="intro">Profile Intro</label>
            <textarea class="form-control" id="intro" autocomplete="on"
                      name="intro"><?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'intro'); ?></textarea>
        </div>
        <div class="form-group">
            <label for="description">Profile Description</label>
            <textarea class="form-control" id="description" autocomplete="on"
                      name="description"><?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'description'); ?></textarea>
        </div>
        <div class="form-group">
            <label for="contact_details">Contact Details</label>
            <textarea class="form-control" id="contact_details" autocomplete="off"
                      name="contact_details"><?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'contact_details'); ?></textarea>
        </div>
        <div class="form-group">
            <label for="web_profile">Web Profile</label>
            <textarea class="form-control" id="web_profile" autocomplete="on"
                      name="web_profile"><?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'web_profile'); ?></textarea>
        </div>
        <div class="form-group">
            <label for="date_of_birth">Date of Birth</label>
            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" autocomplete="off"
                   pattern="\d{4}-\d{2}-\d{2}"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'date_of_birth'); ?>">
        </div>
        <div class="form-group">
            <label for="gender">Gender</label>
            <select class="form-select" id="gender" name="gender">
                <option value="">--</option>
                <option value="male"<?= $isMale ? ' selected' : ''; ?>>
                    Male
                </option>
                <option value="female"<?= $isFemale ? ' selected' : ''; ?>>
                    Female
                </option>
                <option value="other"<?= $isOther ? ' selected' : ''; ?>>
                    Other
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="country">Country</label>
            <select class="form-select" id="country" name="country">
                <?php $country_compare = isset($country) ? trim(strtolower((string)$country)) : ''; ?>
                <?php foreach ($countries_list as $country_list) : ?>
                    <?php if (isset($country) && is_string($country) && is_string($country_list)) : ?>
                        <option<?= strtolower($country_list) === $country_compare ? ' selected' : ''; ?>
                            value="<?php echo $country_list; ?>">
                            <?php echo $country_list; ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" class="form-control" id="city" name="city" autocomplete="off"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'city'); ?>">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" class="form-control" id="address" name="address" autocomplete="on"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'address'); ?>">
        </div>
        <div class="form-group">
            <label for="postal_code">Postal Code</label>
            <input type="text" class="form-control" id="postal_code" name="postal_code" autocomplete="off"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'postal_code'); ?>">
        </div>
        <div class="form-group">
            <label for="email_1">Alternative Email 1</label>
            <input type="email" class="form-control" id="email_1" name="email_1" minlength="6" autocomplete="off"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'email_1'); ?>">
        </div>
        <div class="form-group">
            <label for="email_2">Alternative Email 2</label>
            <input type="email" class="form-control" id="email_2" name="email_2" minlength="6" autocomplete="off"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'email_2'); ?>">
        </div>
        <div class="form-group">
            <label for="email_3">Alternative Email 3</label>
            <input type="email" class="form-control" id="email_3" name="email_3" minlength="6" autocomplete="off"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'email_3'); ?>">
        </div>
        <div class="form-group">
            <label for="email_4">Alternative Email 4</label>
            <input type="email" class="form-control" id="email_4" name="email_4" minlength="6" autocomplete="off"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'email_4'); ?>">
        </div>
        <div class="form-group">
            <label for="email_5">Alternative Email 5</label>
            <input type="email" class="form-control" id="email_5" name="email_5" minlength="6" autocomplete="off"
                   value="<?php echo get_user_details_value($cleandeck_user_details, $cmsg_form_data, 'email_5'); ?>">
        </div>

        <div class="form-group mb-0">
            <input class="form-check-input me-1" type="checkbox" value="1" id="subscribed_newsletter"
                   name="subscribed_newsletter"<?= $is_subscribed_newsletter !== 0 ? ' checked' : ''; ?>>
            <label for="subscribed_newsletter">Subscribe to newsletters</label>
        </div>
        <div class="form-group">
            <input class="form-check-input me-1" type="checkbox" value="1" id="subscribed_promotions"
                   name="subscribed_promotions"<?= $is_subscribed_promotions !== 0 ? ' checked' : ''; ?>>
            <label for="subscribed_promotions">Subscribe to promotions</label>
        </div>
        <div class="form-group">
            <p class="form-text m-0 p-0">Notification Options
                <br>
                <input class="form-check-inline ms-1 me-1" type="checkbox" value="email" id="notification_options_1"
                       name="notification_options[]"<?= in_array('email', $notification_options) ? ' checked' : ''; ?>>
                <label class="me-4" for="notification_options_1">Email</label>
                <input class="form-check-inline me-1" type="checkbox" value="sms" id="notification_options_2"
                       name="notification_options[]"<?= in_array('sms', $notification_options) ? ' checked' : ''; ?>>
                <label class="me-4" for="notification_options_2">SMS</label>
                <br>
                <input class="form-check-inline ms-1 me-1" type="checkbox" value="phone" id="notification_options_3"
                       name="notification_options[]"<?= in_array('phone', $notification_options) ? ' checked' : ''; ?>>
                <label class="me-4" for="notification_options_3">Phone</label>
            </p>
        </div>
        <?php echo view_main('components/captcha'); ?>
        <div class="form-group text-end">
            <button type="submit" class="btn btn-primary btn-primary-contrast">Change Details</button>
        </div>
    </form>
</div>
<?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/pages/account-change-details.js'),
    ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
