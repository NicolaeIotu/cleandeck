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

use Framework\Libraries\Utils\ContentUtils;
use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

$has_details = false;
$has_tags = false;
$tags_array = [];
if (isset($faq_details) && is_array($faq_details) &&
    isset($faq_details['faq_id'], $faq_details['question'], $faq_details['answer'],
        $faq_details['format'], $faq_details['creation_timestamp'], $faq_details['views_count'])) {
    $has_details = true;

    $faq_format = $faq_details['format'];
    $faq_attachments_array = isset($faq_details['faq_attachments']) ?
        explode(',', (string)$faq_details['faq_attachments']) : [];
    $answer_html = ContentUtils::adjustMainContent(
        $faq_details['answer'],
        $faq_format,
        $faq_attachments_array,
        UrlUtils::baseUrl(),
        '/misc/faqs/' . $faq_details['faq_id']
    );

    $has_h1_title = stripos($answer_html, '</h1>') !== false;

    if (isset($faq_details['tags']) && is_string($faq_details['tags'])) {
        $tags_array = explode(',', $faq_details['tags']);
        $has_tags = true;
    }
}

?>
<div class="container-xxl w-100 w-lg-75 m-auto p-0 mb-5">
    <?php if ($has_details): ?>
        <?= UrlUtils::link(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/css/images-autoload.css'),
            ['type' => 'text/css', 'rel' => 'stylesheet', 'referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
        <?php if (!$has_h1_title): ?>
            <h1 class="display-4 pt-4 mb-2 text-wrap text-break"><?= ucfirst((string)$faq_details['question']); ?></h1>
        <?php endif; ?>
        <div class="w-100 border rounded m-0 mb-3 p-3 clearfix text-wrap text-break">
            <?= $answer_html; ?>
        </div>
        <footer class="container w-100 m-0 mb-3 p-3 pb-1">
            <ul class="m-0">
                <?php if ($has_tags): ?>
                    <li><strong>Tags: </strong>
                        <?php foreach ($tags_array as $tag_array): ?>
                            <a class="small badge text-bg-light text-decoration-none border border-secondary m-0 ms-1 p-1"
                               href="<?= UrlUtils::baseUrl('/faqs') . '?tags=' . $tag_array; ?>">
                                <?= $tag_array; ?>
                            </a>
                        <?php endforeach; ?>
                    </li>
                <?php endif; ?>
                <!--START-SEO-IGNORE-->
                <li><strong>Language: </strong><?= ucwords(locale_get_display_name($faq_details['lang_code'])); ?>
                </li>
                <?php if ($faq_details['views_count'] > 100): ?>
                    <li><strong>Views: </strong><?= $faq_details['views_count']; ?></li>
                <?php endif; ?>
                <?php if (isset($faq_details['author_name']) && $faq_details['author_name'] !== '') : ?>
                    <li>
                        <address class="m-0 p-0">
                            <strong>Author: </strong><?= $faq_details['author_name']; ?>
                        </address>
                    </li>
                <?php endif; ?>
                <?php if (isset($is_admin) && $is_admin === true) : ?>
                    <li>
                        <strong>Format: </strong><?= $faq_format; ?>
                    </li>
                    <li>
                        <strong>Status: </strong><?= isset($faq_details['disabled_timestamp']) ? 'Disabled' : 'Published'; ?>
                    </li>
                    <li>
                        <strong>Shows in Sitemap: </strong>
                        <?= isset($faq_details['show_in_sitemap']) && $faq_details['show_in_sitemap'] === 1 ? 'true' : 'false'; ?>
                    </li>
                    <li>
                        <strong>Sitemap Change Frequency: </strong><?= $faq_details['sitemap_changefreq'] ?? 'N/A'; ?>
                    </li>
                    <li>
                        <strong>Sitemap Priority: </strong><?= $faq_details['sitemap_priority'] ?? 'N/A'; ?>
                    </li>
                    <li>
                        <strong>Shows in RSS Feed: </strong>
                        <?= isset($faq_details['show_in_rss']) && $faq_details['show_in_rss'] === 1 ? 'true' : 'false'; ?>
                    </li>
                <?php endif; ?>
                <li>
                    <strong>Created: </strong>
                    <time
                        datetime="<?= TimeUtils::timestampToDateString($faq_details['creation_timestamp'], 'Y-m-d H:i'); ?>">
                        <?= TimeUtils::timestampToDateString($faq_details['creation_timestamp'], 'F d, Y'); ?>
                    </time>
                </li>
                <?php if (isset($faq_details['modified_timestamps'])) : ?>
                    <?php
                    $modified_timestamps = explode(',', (string)$faq_details['modified_timestamps']);
                    $count_modified_timestamps = count($modified_timestamps);
                    $latest_modified_timestamp = (int)$modified_timestamps[$count_modified_timestamps - 1]; ?>
                    <li>
                        <strong>Modified: </strong>
                        <time
                            datetime="<?= TimeUtils::timestampToDateString($latest_modified_timestamp, 'Y-m-d H:i'); ?>">
                            <?= TimeUtils::timestampToDateString($latest_modified_timestamp, 'F d, Y'); ?>
                        </time>
                    </li>
                <?php endif; ?>
                <!--END-SEO-IGNORE-->
            </ul>
        </footer>
        <!--START-SEO-IGNORE-->
        <div class="clearfix mb-2 p-0">
            <a class="btn btn-success btn-success-contrast float-start"
               href="<?= UrlUtils::baseUrl('/support-cases/new'); ?>">
                Add Your Question
            </a>
            <?php if (isset($is_admin) && $is_admin === true) : ?>
                <a class="btn btn-primary btn-primary-contrast float-end"
                   href="<?= UrlUtils::baseUrl('/admin/faq/modify/' . $faq_details['faq_id']); ?>">
                    Edit FAQ
                </a>
            <?php endif; ?>
        </div>
        <div class="clearfix">
            <a href="<?= UrlUtils::baseUrl('/faqs'); ?>"
               class="btn btn-outline-dark float-start" role="button" title="FAQs">FAQs</a>
            <button id="scroll_to_top" type="button" class="btn btn-outline-secondary float-end">
                Back to Top
            </button>
        </div>
        <!--END-SEO-IGNORE-->
    <?php else: ?>
        <p>This FAQ has invalid details. Please retry.</p>
    <?php endif; ?>
</div>
<?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/images-autoload.js'),
    ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous', 'type' => 'module']);?>
<?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/scroll-to-top.js'),
    ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
