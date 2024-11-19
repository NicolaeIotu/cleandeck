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

$is_modify_action = isset($agreement_details);

// handle agreement_title
$agreement_title = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'agreement_title');
if ($agreement_title === '') {
    $agreement_title_convert = 'false';
    if (isset($agreement_details, $agreement_details['agreement_title'])) {
        $agreement_title = $agreement_details['agreement_title'];
    }
} else {
    $agreement_title_convert = 'true';
}

// handle agreement_type
$agreement_type = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'agreement_type');
if ($agreement_type === '') {
    $agreement_type_convert = 'false';
    if (isset($agreement_details, $agreement_details['agreement_type'])) {
        $agreement_type = $agreement_details['agreement_type'];
    }
} else {
    $agreement_type_convert = 'true';
}

// handle for_employee_types
$for_employee_types =
    CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'for_employee_types');
if ($for_employee_types === '') {
    $for_employee_types_convert = 'false';
    if (isset($agreement_details, $agreement_details['for_employee_types'])) {
        $for_employee_types = $agreement_details['for_employee_types'];
    }
} else {
    $for_employee_types_convert = 'true';
}

// handle for_email
$for_email =
    CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'for_email');
if ($for_email === '') {
    $for_email_convert = 'false';
    if (isset($agreement_details, $agreement_details['for_email'])) {
        $for_email = $agreement_details['for_email'];
    }
} else {
    $for_email_convert = 'true';
}

// handle agreement_content
$agreement_content = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'agreement_content');
if ($agreement_content === '') {
    $agreement_content_convert = 'false';
    if (isset($agreement_details, $agreement_details['agreement_content'])) {
        $agreement_content = $agreement_details['agreement_content'];
    }
} else {
    $agreement_content_convert = 'true';
}

?>
<?php if (isset($is_admin) && $is_admin === true) : ?>
    <div class="container w-100 w-sm-75 p-2 safe-min-width">
        <h1 class="text-end">
            <?php if ($is_modify_action) : ?>
                Edit Agreement
            <?php else : ?>
                New Agreement
            <?php endif; ?>
        </h1>
        <form id="front_form"></form>
        <form id="main_form" method="post"
              data-modify="<?= $is_modify_action ? 'true' : 'false'; ?>"
              data-agreement-id="<?= $agreement_details['agreement_id']; ?>"
              action="<?= UrlUtils::baseUrl($is_modify_action ?
                  '/admin/agreement/modify/' . $agreement_details['agreement_id'] : '/admin/agreement/new'); ?>">
            <?php echo view_main('components/csrf'); ?>
            <div class="form-group">
                <label for="agreement_title_front" class="fw-bolder">Agreement Title</label>
                <input type="hidden" form="main_form" id="agreement_title" name="agreement_title">
                <input type="text" class="form-control" form="front_form" id="agreement_title_front"
                       name="agreement_title_front" autocomplete="on" aria-required="true"
                       data-convert="<?= $agreement_title_convert ?>" data-content="<?= $agreement_title; ?>"
                       required minlength="2" maxlength="1000">
            </div>
            <div class="form-group">
                <label for="agreement_type_front" class="fw-bolder">Agreement Type</label>
                <input type="hidden" form="main_form" id="agreement_type" name="agreement_type">
                <input type="text" class="form-control" form="front_form" id="agreement_type_front"
                       name="agreement_type_front" autocomplete="on" aria-required="true"
                       data-convert="<?= $agreement_type_convert ?>" data-content="<?= $agreement_type; ?>"
                       required minlength="2" maxlength="200">
                <p class="small">
                    The agreement type is a short code used to categorize this kind of agreement i.e. "nda" or "toe".
                </p>
            </div>
            <div class="form-group">
                <label for="for_employee_types_front" class="fw-bolder">For Employee Categories</label>
                <input type="hidden" form="main_form" id="for_employee_types"
                       name="for_employee_types">
                <input type="text" class="form-control" form="front_form" id="for_employee_types_front"
                       name="for_employee_types_front" autocomplete="on"
                       data-convert="<?= $for_employee_types_convert ?>"
                       data-content="<?= $for_employee_types; ?>"
                       maxlength="3000">
                <p class="small">
                    Comma (,) separated list of target employee categories i.e. "programmer,accountant", and/or other
                    kind of categorization such as "austrian-english-accountant".<br>
                    Wildcard category <strong>all</strong> can be used in order to target all employees.<br>
                    Use lowercase letters, numbers and characters <strong>+,_-</strong>.<br>
                    Use character <strong>+</strong> (plus sign) for compound categories
                    i.e. "accountant+english,administrator+english". In this case the agreement is available
                    only for employees which have <strong>employee_type</strong> entry equal with either
                    "accountant+english", or "administrator+english".<br>
                    No spaces allowed.
                </p>
            </div>
            <div class="form-group">
                <label for="for_email_front" class="fw-bolder">For Target Email</label>
                <input type="hidden" form="main_form" id="for_email" name="for_email">
                <input type="email" class="form-control" form="front_form" id="for_email_front"
                       name="for_email_front" autocomplete="off"
                       data-convert="<?= $for_email_convert ?>" data-content="<?= $for_email; ?>">
                <p class="small">
                    If specified, this field should hold a single valid email of the employee targeted by this
                    agreement.</p>
            </div>
            <div class="form-group mt-4">
                <span class="fw-bolder fs-5">AGREEMENT CONTENT</span>
                <input type="hidden" form="main_form" id="agreement_content" name="agreement_content">
                <div id="agreement_content_original" class="d-none"><?= $agreement_content; ?></div>
                <div id="agreement_content_front" class="cleandeck-text-editor m-0 mb-1 p-0"></div>
            </div>
            <?php echo view_main('components/captcha'); ?>
        </form>
        <div class="row">
            <?php if ($is_modify_action): ?>
                <div class="col text-start">
                    <form id="delete_form" method="post"
                          action="<?= UrlUtils::baseUrl('/admin/agreement/delete/' . $agreement_details['agreement_id']); ?>">
                        <?php echo view_main('components/csrf'); ?>
                        <button type="button" id="delete_agreement_btn" class="btn btn-danger float-start">
                            Delete agreement
                        </button>
                        <button type="button" id="cancel_delete_agreement_btn"
                                class="btn btn-outline-danger float-start ms-lg-2 ms-0 mt-lg-0 mt-2 d-none">
                            Cancel Delete
                        </button>
                    </form>
                </div>
            <?php endif; ?>
            <div class="col text-end">
                <button type="button" id="controlled_submit"
                        class="btn btn-primary btn-primary-contrast clearfix ps-5 pe-5">
                    <?php if ($is_modify_action) : ?>
                        Modify agreement
                    <?php else : ?>
                        Create agreement
                    <?php endif; ?>
                </button>
            </div>
        </div>
        <hr class="mt-5">
        <small>
            <a href="<?php echo UrlUtils::baseUrl('/admin/agreements'); ?>" title="Agreements"
               target="_self">Agreements</a>
        </small>
    </div>
    <?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/pages/agreement-edit.js'),
        ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous', 'type' => 'module']);?>
<?php else: ?>
    <div class="alert alert-warning">
        <p>Insufficient permissions</p>
    </div>
<?php endif; ?>
t
