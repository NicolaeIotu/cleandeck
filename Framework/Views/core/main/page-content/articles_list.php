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
use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

$is_admin_route = isset($is_admin) && $is_admin === true;
$default_search_disabled = null;
$default_search_published = null;
if ($is_admin_route) {
    if (isset($_GET['disabled']) && $_GET['disabled'] !== '--') {
        $default_search_disabled = $_GET['disabled'] === 'true';
    }
    if (isset($_GET['published']) && $_GET['published'] !== '--') {
        $default_search_published = $_GET['published'] === 'true';
    }
}

$articles_base_url = UrlUtils::url_clean();
$default_sort_order = 'DESC';
if (isset($_GET['sortorder']) && $_GET['sortorder'] === 'ASC') {
    $default_sort_order = 'ASC';
}

$has_search_details = false;
$search_details = [];
if ($_GET !== []) {
    if ($is_admin_route) {
        $search_details = array_filter($_GET, static function ($v, $k): bool {
            return in_array($k, ['tags', 'content', 'disabled', 'published']) && strlen((string)$v) > 0 && $v !== '--';
        }, ARRAY_FILTER_USE_BOTH);
    } else {
        $search_details = array_filter($_GET, static function ($v, $k): bool {
            return in_array($k, ['tags', 'content']) && strlen((string)$v) > 0;
        }, ARRAY_FILTER_USE_BOTH);
    }

    if ($search_details !== []) {
        $has_search_details = true;
    }
}

$has_articles = false;
if (isset($articles) && is_array($articles) &&
    isset($articles['stats']) && is_array($articles['stats']) &&
    isset($articles['stats']['total_articles']) && is_int($articles['stats']['total_articles']) &&
    $articles['stats']['total_articles'] > 0 && isset($articles['result']) &&
    is_array($articles['result']) && $articles['result'] !== []) {
    $has_articles = true;

    $pagination_base_url = $articles_base_url . '?' . UrlUtils::get_query();
    $pagination = Pagination::build(
        $articles['stats']['total_articles'],
        $articles['stats']['page_number'],
        $articles['stats']['page_entries'],
        $pagination_base_url
    );
}

?>

<div class="container w-100 w-md-75 p-2">
    <h1 class="text-end">Articles</h1>

    <form method="get" action="<?= $articles_base_url; ?>">
        <div class="container w-100 w-md-75 border rounded m-0 mb-4 p-2 me-auto ms-auto">
            <div class="form-group clearfix m-0 mb-2">
                <input type="text" class="form-control" name="content"
                       title="Search articles" placeholder="Search articles" autocomplete="on">
                <select class="form-select w-auto m-0 mt-1 ms-2 p-0 pe-2 ps-2 float-end"
                        name="sortorder" title="Sort order">
                    <option value="DESC"<?= $default_sort_order === 'DESC' ? ' selected' : ''; ?>>
                        Latest First&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </option>
                    <option value="ASC"<?= $default_sort_order !== 'DESC' ? ' selected' : ''; ?>>
                        Older First
                    </option>
                </select>
                <?php if ($is_admin_route): ?>
                    <select class="form-select w-auto m-0 mt-1 ms-2 p-0 pe-2 ps-2 float-end"
                            name="disabled" title="Show disabled articles">
                        <option value="--"<?= isset($default_search_disabled) ? '' : ' selected' ?>>
                            filter disabled&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </option>
                        <option
                                value="true"<?= isset($default_search_disabled) && $default_search_disabled ? ' selected' : ''; ?>>
                            Only Disabled
                        </option>
                        <option
                                value="false"<?= isset($default_search_disabled) && !$default_search_disabled ? ' selected' : ''; ?>>
                            Only Active
                        </option>
                    </select>
                    <select class="form-select w-auto m-0 mt-1 p-0 pe-2 ps-2 float-end"
                            name="published" title="Show published articles">
                        <option value="--"<?= isset($default_search_published) ? '' : ' selected'; ?>>
                            filter published&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </option>
                        <option
                                value="false"<?= isset($default_search_published) && !$default_search_published ? ' selected' : ''; ?>>
                            Only Unpublished
                        </option>
                        <option
                                value="true"<?= isset($default_search_published) && $default_search_published ? ' selected' : ''; ?>>
                            Only Published
                        </option>
                    </select>
                <?php endif; ?>
            </div>
            <div class="form-group clearfix m-0 p-0">
                <?php if ($has_search_details): ?>
                    <a class="btn btn-secondary float-start" href="<?= $articles_base_url; ?>"
                       title="Reset search">Reset search</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary btn-primary-contrast float-end">Search</button>
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

        <?php if ($has_articles): ?>
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
            <div class="list-group">
                <?php foreach ($articles['result'] as $result): ?>
                    <?php
                    $marking = '';
                    $marking_title = '';
                    if ($is_admin_route) {
                        if (isset($result['disabled_timestamp'])) {
                            $marking .= ' list-group-item-secondary';
                            $marking_title .= '[Disabled] ';
                        }
                        if (isset($result['published_timestamp'])) {
                            $marking .= ' border rounded';
                            $marking_title .= '[Published] ';
                        } else {
                            $marking_title .= '[Not-Published] ';
                        }
                    }

                    // retrieve articles by ID
                    // $result_url = UrlUtils::baseUrl('article/' . $result['article_id']);
                    // , or retrieve articles by the content of the title
                    $result_url = UrlUtils::baseUrl('article?' . http_build_query(['title' => $result['article_title']]));
                    $short_title = strlen((string)$result['article_title']) > 250 ?
                        substr(strip_tags((string)$result['article_title']), 0, 250) . '...' :
                        $result['article_title'];
                    ?>
                    <div class="list-group-item mb-1 border rounded<?= $marking; ?>">
                        <div class="d-flex w-100">
                            <a href="<?= $result_url; ?>" class="fs-5 mb-1 text-wrap text-break text-decoration-none"
                               title="<?= $marking_title . $short_title; ?>">
                                    <?= $short_title; ?>
                            </a>
                        </div>
                        <?php if (isset($result['article_summary'])) : ?>
                            <?php $article_summary_text = strip_tags((string)$result['article_summary']); ?>
                            <div class="d-flex w-100 justify-content-between small text-wrap text-break">
                                <a href="<?= $result_url; ?>" class="text-secondary text-decoration-none"
                                   title="<?= $marking_title . $short_title; ?>">
                                    <?php if (strlen($article_summary_text) > 250): ?>
                                        <?php echo substr($article_summary_text, 0, 250) . '...'; ?>
                                    <?php else: ?>
                                        <?php echo $article_summary_text; ?>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($result['tags'])) : ?>
                            <?php $tags = explode(',', (string)$result['tags']); ?>
                            <?php if ($tags !== []) : ?>
                                <div class="w-100 text-break">
                                    <span class="small fw-bolder">Tags: </span>
                                    <?php foreach ($tags as $tag): ?>
                                        <a class="small badge text-bg-light border border-secondary m-0 ms-1 p-1 text-decoration-none"
                                           href="<?= $articles_base_url . '?tags=' . $tag; ?>"
                                           title="Search tag '<?= $tag; ?>'">
                                            <?php echo $tag; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between float-end mt-1">
                            <small class="text-wrap text-break text-end">
                                <?php echo TimeUtils::timestampToDateString($result['creation_timestamp'], 'Y-m-d T') ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <?php if ($has_search_details): ?>
                <p>No articles with such details. Please try another search.</p>
            <?php else: ?>
                <p>No articles at the moment.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
