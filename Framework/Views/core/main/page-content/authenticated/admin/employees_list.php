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

use Framework\Libraries\Utils\Pagination;
use Framework\Libraries\Utils\UrlUtils;

$employees_base_url = UrlUtils::url_clean();

$has_search_details = false;
$search_details = [];
if (isset($_GET['email']) && $_GET['email'] !== '') {
    $search_details['email'] = $_GET['email'];
    $has_search_details = true;
}

$has_employees = false;
if (isset($employees) && is_array($employees) &&
    isset($employees['stats']) && is_array($employees['stats']) &&
    isset($employees['stats']['total_employees']) && is_int($employees['stats']['total_employees']) &&
    $employees['stats']['total_employees'] > 0 &&
    isset($employees['result']) && is_array($employees['result']) && $employees['result'] !== []) {
    $has_employees = true;

    $pagination_base_url = $employees_base_url . '?' . UrlUtils::get_query();
    $pagination = Pagination::build(
        $employees['stats']['total_employees'],
        $employees['stats']['page_number'],
        $employees['stats']['page_entries'],
        $pagination_base_url
    );
}

?>

<div class="container w-100 w-md-75 w-lg-50 p-2">
    <h1 class="text-end">Employees</h1>
    <form method="get" action="<?php echo $employees_base_url; ?>">
        <div class="container w-100 m-0 mb-4 p-2">
            <div class="form-group clearfix m-0 mb-2">
                <label class="mb-1" for="email-s">Search users (employees or not yet employees) by email or part of email</label>
                <input type="text" class="form-control" id="email-s" name="email" autocomplete="on"
                       title="Search users by email" placeholder="User email">
            </div>
            <div class="form-group clearfix m-0 p-0">
                <?php if ($has_search_details): ?>
                    <a class="btn btn-secondary float-start" href="<?= $employees_base_url; ?>"
                       title="Reset search">Reset search</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary float-end">Search</button>
            </div>
        </div>
    </form>
    <div>
        <?php if ($has_search_details): ?>
            <hr class="m-2">
            <span>Showing:</span>
            <ul class="m-0 mb-2">
                <?php foreach ($search_details as $key => $value): ?>
                    <li><span class="fw-bold"><?= $key; ?></span>: <?= $value; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if (isset($employees) && $has_employees): ?>
            <?php if (count($pagination) > 1): ?>
                <nav>
                    <ul class="pagination pagination-sm justify-content-center">
                        <?php
                        foreach ($pagination as $button_description): ?>
                            <li class="page-item <?php echo $button_description["active"] === true ? "active" :
                                ($button_description["disabled"] === true ? "disabled" : ""); ?>">
                                <?php if ($button_description["active"] === true) : ?>
                                    <span class="page-link"><?php echo $button_description["symbol"]; ?></span>
                                <?php else : ?>
                                    <a class="page-link"
                                       href="<?php echo $button_description["link"]; ?>"><?php echo $button_description["symbol"]; ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            <ul class="list-group" id="employees-list">
                <?php foreach ($employees['result'] as $result): ?>
                    <?php $is_employee = isset($result['employee_type']) && $result['employee_type'] !== ''; ?>
                    <li class="list-group-item fs-5 mb-1 px-3 border rounded text-wrap text-break cursor-pointer"
                        title="<?= $is_employee ? 'See employment details' : 'Not yet recorded as employee';?>">
                        <span class="fw-bolder<?= $is_employee ? '' : ' badge text-bg-warning'; ?>">
                            <?= $result['email']; ?></span>
                        <div class="m-0 p-0 my-3 d-none">
                            <ul class="ist-group list-group-flush small">
                                <li class="list-group-item"><span class="fw-bolder">Email: </span>
                                    <?= $result['email']; ?></li>
                                <li class="list-group-item"><span class="fw-bolder">Username: </span>
                                    <?= $result['username']; ?></li>
                                <li class="list-group-item"><span class="fw-bolder">Employee Category: </span>
                                    <?php if ($is_employee): ?>
                                        <?= $result['employee_type']; ?>
                                    <?php else: ?>
                                        <span class="badge text-bg-warning">Not yet recorded as employee</span>
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item"><span
                                        class="fw-bolder">Official Employment Start Date: </span>
                                    <?= $result['employment_start_date'] ?? 'N/A'; ?></li>
                                <li class="list-group-item"><span class="fw-bolder">Official Classification of Employment: </span>
                                    <?= $result['employment_official_classification'] ?? 'N/A'; ?></li>
                                <li class="list-group-item"><span
                                        class="fw-bolder">Official Title of Employment: </span>
                                    <?= $result['employment_official_title'] ?? 'N/A'; ?></li>
                                <li class="list-group-item"><span class="fw-bolder">Other Employment Details: </span>
                                    <?= $result['other_employment_details'] ?? 'N/A'; ?></li>
                            </ul>
                            <a class="btn btn-primary float-end" target="_blank"
                               href="<?= UrlUtils::baseUrl('/admin/employee/' . $result['email']); ?>">
                                Modify employment details
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <?php if ($has_search_details): ?>
                <p>No employees with such details. Please try another search.</p>
            <?php else: ?>
                <p>No employees at the moment.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/pages/admin-employees-list.js'),
        ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
</div>

