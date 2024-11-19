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

$valid_employee = false;
if (isset($employee_details)) {
    $valid_employee = true;
}

$cmsg = CleanDeckStatics::getCookieMessage();
$cmsg_form_data = $cmsg['cmsg_form_data'] ?? [];

$email = CookieMessengerReader::getPreviousFormData($cmsg_form_data,
    'email', $employee_details['email'] ?? null);
$employee_type =
    CookieMessengerReader::getPreviousFormData($cmsg_form_data,
        'employee_type', $employee_details['employee_type'] ?? null);
$employment_official_classification =
    CookieMessengerReader::getPreviousFormData($cmsg_form_data,
        'employment_official_classification', $employee_details['employment_official_classification'] ?? null);
$employment_official_title =
    CookieMessengerReader::getPreviousFormData($cmsg_form_data,
        'employment_official_title', $employee_details['employment_official_title'] ?? null);
$employment_start_date =
    CookieMessengerReader::getPreviousFormData($cmsg_form_data,
        'employment_start_date', $employee_details['employment_start_date'] ?? null);
$employment_end_date =
    CookieMessengerReader::getPreviousFormData($cmsg_form_data,
        'employment_end_date', $employee_details['employment_end_date'] ?? null);

?>

<div class="container w-100 w-md-75 w-lg-50 p-2">
    <h1 class="text-start">Modify Employment Details
        <?php if (isset($employee_details['email'])): ?>
                           of user
        <span class="badge text-bg-secondary"><?= $employee_details['email']; ?></span></h1>
    <?php endif; ?>
    <?php if ($valid_employee): ?>
        <form method="post" enctype="application/x-www-form-urlencoded"
              action="<?= UrlUtils::baseUrl('/admin/employee'); ?>">
            <?php echo view_main('components/csrf'); ?>
            <input type="hidden" name="email" value="<?= $employee_details['email']; ?>"/>
            <div class="container w-100 border rounded m-0 mb-4 p-2">
                <div class="form-group m-0 mb-4">
                    <p class="m-0 p-0 mb-1">
                        <label class="fw-bolder" for="employee_type">Employee Category (internal)</label><br>
                        <label for="employee_type">Now:
                            <span
                                class="badge text-bg-secondary"><?= $employee_details['employee_type'] ?? 'none'; ?></span>
                        </label>
                    </p>
                    <input type="text" class="form-control" minlength="2" maxlength="254" autocomplete="on"
                           id="employee_type" name="employee_type" value="<?= $employee_type; ?>">
                    <p class="small">Applicable agreements are identified using field
                        <span class="fw-bolder">Employee Category</span>.<br>
                                     You can use character <strong>+</strong> (plus sign) to assign multiple categories
                                     to this user i.e. <strong>accountant+english</strong>.<br>
                                     You can remove employee status for user
                        <span class="fw-bolder"><?= $employee_details['email']; ?></span>
                                     if leaving <span class="fw-bolder">Employee Category</span> field empty.
                    </p>
                </div>
                <div class="form-group m-0 mb-4">
                    <p class="m-0 p-0 mb-1">
                        <label class="fw-bolder" for="employment_start_date">Official Employment Start Date</label><br>
                        <label for="employment_start_date">Now:
                            <span class="badge text-bg-secondary">
                                <?= $employee_details['employment_start_date'] ?? 'none'; ?>
                            </span>
                        </label>
                    </p>
                    <input type="date" class="form-control" id="employment_start_date" name="employment_start_date"
                           autocomplete="off" pattern="\d{4}-\d{2}-\d{2}"
                           value="<?= $employment_start_date; ?>">
                </div>
                <div class="form-group m-0 mb-4">
                    <label class="fw-bolder" for="employment_end_date">Official Employment End Date</label>
                    <input type="date" class="form-control" id="employment_end_date" name="employment_end_date"
                           autocomplete="off" pattern="\d{4}-\d{2}-\d{2}"
                           value="<?= $employment_end_date; ?>">
                    <p class="small">Setting Official Employment End Date will remove employee status for user
                        <span class="fw-bolder"><?= $employee_details['email']; ?></span>.</p>
                </div>
                <div class="form-group m-0 mb-4">
                    <p class="m-0 p-0 mb-1">
                        <label class="fw-bolder" for="employment_official_classification">
                            Employment Official Code</label><br>
                        <label for="employment_official_classification">Now:
                            <span class="badge text-bg-secondary">
                                <?= $employee_details['employment_official_classification'] ?? 'none'; ?>
                            </span>
                        </label>
                    </p>
                    <input type="text" class="form-control" maxlength="254" autocomplete="on"
                           id="employment_official_classification" name="employment_official_classification"
                           value="<?= $employment_official_classification; ?>">
                    <p class="small">i.e. COR2131</p>
                </div>
                <div class="form-group m-0 mb-4">
                    <p class="m-0 p-0 mb-1">
                        <label class="fw-bolder" for="employment_official_title">Employment Official Title</label><br>
                        <label for="employment_official_title">Now:
                            <span class="badge text-bg-secondary">
                                <?= $employee_details['employment_official_title'] ?? 'none'; ?>
                            </span>
                        </label>
                    </p>
                    <input type="text" class="form-control" maxlength="2000" autocomplete="on"
                           id="employment_official_title" name="employment_official_title"
                           value="<?= $employment_official_title; ?>">
                    <p class="small">i.e. Biologist, botanist, zoologist and others alike</p>
                </div>
                <div class="form-group m-0 mb-4">
                    <p class="m-0 p-0 mb-1">
                        <label class="fw-bolder" for="other_employment_details">Other Employment Details</label><br>
                        <label for="other_employment_details">Now:
                            <span class="badge text-bg-secondary">
                                <?= $employee_details['other_employment_details'] ?? 'none'; ?>
                            </span>
                        </label>
                    </p>
                    <input type="text" class="form-control" maxlength="2000" autocomplete="on"
                           id="other_employment_details" name="other_employment_details"
                           value="<?= $employment_official_title; ?>">
                </div>
                <hr>
                <?php echo view_main('components/captcha'); ?>
                <div class="form-group clearfix m-0 p-0">
                    <button type="submit" class="btn btn-primary float-end">Modify</button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <p>Invalid employee details.</p>
    <?php endif; ?>
</div>

