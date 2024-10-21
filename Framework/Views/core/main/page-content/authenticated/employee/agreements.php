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

use Framework\Libraries\Utils\Pagination;
use Framework\Libraries\Utils\UrlUtils;

if (!defined('CLEANDECK_APP_PATH')) {
    return exit('No direct script access allowed');
}

$is_admin ??= false;
$pagination = [];
if ($is_admin && isset($agreements)) {
    $query_string = UrlUtils::get_query();
    $pagination_base_url = UrlUtils::url_clean() . '?' . $query_string;
    $pagination = Pagination::build(
        $agreements['stats']['total_agreements'],
        $agreements['stats']['page_number'],
        $agreements['stats']['page_entries'],
        $pagination_base_url
    );
}

$page_main_heading = $custom_page_name ??
    ($is_admin ? 'Agreements Administration' : 'Agreements');

$has_query_agreement_title = isset($_GET['agreement_title']);

?>
<div class="container w-100 w-sm-75 w-md-50 p-2">
    <h1 class="text-end"><?= $page_main_heading; ?></h1>
    <?php if (isset($agreements)): ?>
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
        <?php if ($is_admin): ?>
            <div class="w-100 my-4 clearfix">
                <form method="get" action="<?= UrlUtils::url_clean(); ?>" name="main">
                    <div class="form-group text-start w-100 w-sm-75 w-md-50">
                        <label for="agreement_title">Agreement Title (LIKE search)</label>
                        <input type="text" class="form-control" id="agreement_title" name="agreement_title"
                               minlength="2" maxlength="100" autocomplete="on" required>
                    </div>
                    <div class="form-group float-start">
                        <button type="submit" class="btn btn-primary btn-primary-contrast">Search</button>
                    </div>
                </form>
                <?php if ($has_query_agreement_title): ?>
                    <div class="form-group float-end">
                        <a class="btn btn-outline-primary" href="<?= UrlUtils::url_clean(); ?>">Show all agreements</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($has_query_agreement_title) : ?>
                <div>
                    <p>Search results for: <strong><?= $_GET['agreement_title']; ?></strong></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="list-group">
            <?php foreach ($is_admin ? $agreements['result'] : $agreements as $agreement): ?>
                <?php
                $agreement_accepted = isset($agreement['agreement_accepted']) && $agreement['agreement_accepted'] > 0;
                $item_style = $is_admin ? '' :
                    ($agreement_accepted ? ' list-group-item-success' : ' list-group-item-warning');
                $title = $is_admin ? $agreement['agreement_title'] :
                    ($agreement_accepted ? 'Accepted' : 'Pending your action');
                ?>
                <a href="<?= UrlUtils::baseUrl($is_admin ?
                    '/admin/agreements/' . $agreement['agreement_id'] :
                    '/agreements/employee/' . $agreement['agreement_id']); ?>"
                   class="list-group-item<?= $item_style; ?>"
                   title="<?= $title; ?>">
                    <strong><?= $agreement['agreement_title']; ?></strong>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Content unavailable.</p>
    <?php endif; ?>
</div>
