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

/*
 * DIFFICULT CODE
 *
 * This page demonstrates the usage of encodings' settings of CMD-Auth,
 * namely *validation->others->character_encoding_per_request* (*true*) and the resulting
 * query variables *character_encoding_upstream* and *character_encoding_downstream*.
 * The scripts on this page will encode UTF16 JavaScript strings
 * (which may include Unicode characters) to base64 in order to survive the transport.
 * The class ArticleLifecycleController forwards the requests to CMD-Auth using
 * character_encoding_upstream=base64 and character_encoding_downstream=utf16le.
 * CMD-Auth will perform the translation of strings before storing the article in the database.
 * The translation is performed only when setting *character_encoding_per_request* is *true*!
 */

// The frontend administration pages for Articles and FAQs are similar. Alter both when required.

if (!defined('CLEANDECK_APP_PATH')) {
    return exit('No direct script access allowed');
}

use Framework\Libraries\CleanDeckStatics;
use Framework\Libraries\Cookie\CookieMessengerReader;
use Framework\Libraries\Utils\UrlUtils;

$cmsg = CleanDeckStatics::getCookieMessage();
$cmsg_form_data = $cmsg['cmsg_form_data'] ?? [];

$is_modify_action = isset($article_details);

// handle lang_code
// update languages as required by your application
$lang_codes_array = [
    'en-US',
    'cn-CN',
    'de-DE',
    'es-ES',
    'fr-FR',
    'it-IT',
    'jp-JP',
    'pt-PT',
    'ro-RO',
    'ru-RU',
];
$lang_code = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'lang_code');
if ($lang_code === '') {
    $lang_code_convert = 'false';
    if (isset($article_details, $article_details['lang_code'])) {
        $lang_code = $article_details['lang_code'];
    } else {
        // assign a default
        $lang_code = 'en-US';
    }
} else {
    $lang_code_convert = 'true';
}

// handle article_title
$article_title = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'article_title');
if ($article_title === '') {
    $article_title_convert = 'false';
    if (isset($article_details, $article_details['article_title'])) {
        $article_title = $article_details['article_title'];
    }
} else {
    $article_title_convert = 'true';
}

// handle author_name
$author_name = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'author_name');
if ($author_name === '') {
    $author_name_convert = 'false';
    if (isset($article_details, $article_details['author_name'])) {
        $author_name = $article_details['author_name'];
    }
} else {
    $author_name_convert = 'true';
}

// handle article_summary
$article_summary = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'article_summary');
if ($article_summary === '') {
    $article_summary_convert = 'false';
    if (isset($article_details, $article_details['article_summary'])) {
        $article_summary = $article_details['article_summary'];
    }
} else {
    $article_summary_convert = 'true';
}

// handle article_content
$article_content = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'article_content');
if ($article_content === '') {
    $article_content_convert = 'false';
    if (isset($article_details, $article_details['article_content'])) {
        $article_content = $article_details['article_content'];
    }
} else {
    $article_content_convert = 'true';
}

// handle tags
$tags = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'tags');
if ($tags === '') {
    $tags_convert = 'false';
    if (isset($article_details, $article_details['tags'])) {
        $tags = $article_details['tags'];
    }
} else {
    $tags_convert = 'true';
}

// handle disable
$disable = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'disable');
if ($disable === '') {
    $disable_convert = 'false';
    if (isset($article_details, $article_details['disabled_timestamp'])) {
        $disable = 'true';
    } else {
        $disable = 'false';
    }
} else {
    $disable_convert = 'true';
}

// handle publish
$publish = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'publish');
if ($publish === '') {
    $publish_convert = 'false';
    if (isset($article_details, $article_details['published_timestamp'])) {
        $publish = 'true';
    } else {
        $publish = 'false';
    }
} else {
    $publish_convert = 'true';
}

// handle show_in_sitemap
$show_in_sitemap =
    CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'show_in_sitemap');
if ($show_in_sitemap === '') {
    $show_in_sitemap_convert = 'false';
    if (isset($article_details, $article_details['show_in_sitemap'])) {
        $show_in_sitemap = $article_details['show_in_sitemap'];
    } else {
        $show_in_sitemap = '1';
    }
} else {
    $show_in_sitemap_convert = 'true';
}

// handle sitemap_changefreq
$sitemap_changefreq =
    CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'sitemap_changefreq');
$sitemap_changefreq_array = [
    'always',
    'hourly',
    'daily',
    'weekly',
    'monthly',
    'yearly',
    'never',
];
if ($sitemap_changefreq === '') {
    $sitemap_changefreq_convert = 'false';
    if (isset($article_details, $article_details['sitemap_changefreq'])) {
        $sitemap_changefreq = $article_details['sitemap_changefreq'];
    } else {
        // assign a default
        $sitemap_changefreq = 'weekly';
    }
} else {
    $sitemap_changefreq_convert = 'true';
}


// handle sitemap_priority
$sitemap_priority =
    CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'sitemap_priority');
if ($sitemap_priority === '') {
    $sitemap_priority_convert = 'false';
    if (isset($article_details, $article_details['sitemap_priority'])) {
        $sitemap_priority = $article_details['sitemap_priority'];
    } else {
        // default priority
        $sitemap_priority = '0.8';
    }
} else {
    $sitemap_priority_convert = 'true';
}

// handle show_in_rss
$show_in_rss = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'show_in_rss');
if ($show_in_rss === '') {
    $show_in_rss_convert = 'false';
    if (isset($article_details, $article_details['show_in_rss'])) {
        $show_in_rss = $article_details['show_in_rss'];
    } else {
        $show_in_rss = '1';
    }
} else {
    $show_in_rss_convert = 'true';
}

if (isset($article_details['article_attachments']) && is_string($article_details['article_attachments'])) {
    $article_attachments = explode(',', $article_details['article_attachments']);
}

?>
<?php if (isset($is_admin) && $is_admin === true) : ?>
    <div class="container w-100 w-sm-75 p-2 safe-min-width">
        <h1 class="text-end">
            <?php if ($is_modify_action) : ?>
                Edit Article
            <?php else : ?>
                New Article
            <?php endif; ?>
        </h1>
        <form id="front_form"></form>
        <form id="main_form" method="post" enctype="multipart/form-data"
              data-modify="<?= $is_modify_action ? 'true' : 'false'; ?>"
              data-article-id="<?= $article_details['article_id']; ?>"
              action="<?= UrlUtils::baseUrl($is_modify_action ?
                  '/admin/article/modify/' . $article_details['article_id'] : '/admin/article/new'); ?>">
            <?php echo view_main('components/csrf'); ?>
            <div class="form-group">
                <label for="lang_code_front" class="fw-bolder">Language</label>
                <input type="hidden" form="main_form" id="lang_code" name="lang_code">
                <select form="front_form" id="lang_code_front" name="lang_code_front"
                        data-convert="<?= $lang_code_convert ?>" data-content="<?= $lang_code; ?>"
                        class="form-select w-auto min-w-25" required>
                    <?php foreach ($lang_codes_array as $lang_code_array) : ?>
                        <option value="<?= $lang_code_array; ?>">
                            <?= ucwords(locale_get_display_name($lang_code_array)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="article_title_front" class="fw-bolder">Title</label>
                <input type="hidden" form="main_form" id="article_title" name="article_title">
                <input type="text" class="form-control" form="front_form" id="article_title_front"
                       name="article_title_front" autocomplete="on"
                       data-convert="<?= $article_title_convert ?>" data-content="<?= $article_title; ?>"
                       required minlength="8" maxlength="3000">
            </div>
            <div class="form-group">
                <label for="author_name_front" class="fw-bolder">Author Name</label>
                <input type="hidden" form="main_form" id="author_name" name="author_name">
                <input type="text" class="form-control" form="front_form" id="author_name_front"
                       name="author_name_front" autocomplete="on"
                       data-convert="<?= $author_name_convert ?>" data-content="<?= $author_name; ?>"
                       minlength="2" maxlength="200">
            </div>
            <div class="form-group mt-3">
                <span class="fw-bolder fs-5">SUMMARY</span>
                <input type="hidden" form="main_form" id="article_summary" name="article_summary">
                <div id="article_summary_original" class="d-none"><?= $article_summary; ?></div>
                <div id="article_summary_front"
                     class="cleandeck-text-editor m-0 mb-1 p-0"></div>
                <p class="text-smaller">
                    In order to improve appearance in RSS readers it is recommended to have a consistent and
                    stylish HTML summary.
                </p>
            </div>
            <div class="form-group mt-4">
                <span class="fw-bolder fs-5">MAIN ARTICLE CONTENT</span>
                <input type="hidden" form="main_form" id="article_content" name="article_content">
                <div id="article_content_original" class="d-none"><?= $article_content; ?></div>
                <div id="article_content_front" class="cleandeck-text-editor m-0 mb-1 p-0"></div>
            </div>
            <?php if (isset($article_attachments)) : ?>
                <div class="form-group border rounded p-2" id="existing-attachments">
                    <div class="m-0 p-0">
                        <p class="m-0 p-0 fw-bolder">Existing Attachments</p>
                        <ul id="existing_attachments_list" class="list-group list-group-flush text-justify">
                            <?php foreach ($article_attachments as $article_attachment) : ?>
                                <li class="ms-5 text-success text-success-contrast"><?= $article_attachment; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" id="button_remove_attachments">
                            Remove Existing Attachments
                        </button>
                    </div>
                    <div class="m-0 p-0 pt-3 w-100 text-center d-none">
                        <p class="border border-danger rounded text-danger text-danger-contrast text-larger fw-bolder w-50 m-auto">
                            Existing attachments will be deleted.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            <div class="form-group border rounded p-2 pb-1">
                <label for="article_attachments" class="fw-bolder fs-5">ATTACHMENTS</label>
                <input type="hidden" form="main_form" id="toggle_remove_attachments"
                       name="remove_attachments" value="false">
                <!-- Filter allowed attachments type as required by your application -->
                <hr class="m-0 mb-2">
                <input type="file" form="main_form" id="article_attachments" name="article_attachments[]"
                       multiple class="form-control-file" accept="audio/*,video/*,image/*,.pdf,.zip"
                       data-umf="<?= $upload_max_filesize ?? '2M'; ?>" data-mfu="<?= $max_file_uploads ?? 20; ?>"
                       data-umfb="<?= $upload_max_filesize_bytes ?? 2097152; ?>">
                <div class="col-12" id="show-files"></div>
                <p class="small m-0">Select the attachments (including pictures) used within the main content of
                                 this article.<br>
                                 By default only the tags &lt;img&gt;, &lt;script&gt;, &lt;a&gt; and &lt;link&gt;
                                 will have their
                    <strong>src</strong> or <strong>href</strong> attributes replaced at presentation with
                                 the real path of a static asset which is attached here.<br>
                                 At the moment this basic editor is unable to display the attachments inline.<br>
                    <strong>Any existing attachments will be replaced by the attachments selected here.</strong>
                </p>
            </div>
            <div class="form-group">
                <label for="tags_front" class="fw-bolder">Tags</label>
                <input type="hidden" form="main_form" id="tags" name="tags">
                <input type="text" class="form-control" form="front_form" id="tags_front" name="tags_front"
                       data-convert="<?= $tags_convert; ?>" data-content="<?= $tags; ?>"
                       maxlength="200" autocomplete="on">
                <span><small>(comma separated list of tags)</small></span>
            </div>
            <?php if ($is_modify_action) : ?>
                <div class="form-group">
                    <?php $disabled = $disable === 'true'; ?>
                    <label for="disable_front" class="fw-bolder">Article Status</label>
                    <input type="hidden" form="main_form" id="disable" name="disable">
                    <select form="front_form" id="disable_front" name="disable_front"
                            data-convert="<?= $disable_convert ?>" data-content="<?= $disable; ?>"
                            class="form-select w-auto min-w-25" required>
                        <option value="false">Active</option>
                        <option value="true">Disabled</option>
                    </select>
                </div>
                <div class="form-group">
                    <?php $published = $publish === 'true'; ?>
                    <label for="publish_front" class="fw-bolder">Publish Article</label>
                    <input type="hidden" form="main_form" id="publish" name="publish">
                    <select form="front_form" id="publish_front" name="publish_front"
                            data-convert="<?= $publish_convert ?>" data-content="<?= $publish; ?>"
                            class="form-select w-auto min-w-25" required>
                        <option value="false">Don't Publish</option>
                        <option value="true">Publish</option>
                    </select>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label for="publish_front" class="fw-bolder">Publish Article</label>
                    <input type="hidden" form="main_form" id="publish" name="publish">
                    <select form="front_form" id="publish_front" name="publish_front"
                            data-convert="<?= $publish_convert ?>" data-content="<?= $publish; ?>"
                            class="form-select w-auto min-w-25 required">
                        <option value="true"<?= $publish === 'true' ? ' selected' : ''; ?>>
                            Publish
                        </option>
                        <option value="false"<?= $publish !== 'true' ? ' selected' : ''; ?>>
                            Do Not Publish
                        </option>
                    </select>
                </div>
            <?php endif; ?>
            <div class="form-group form-check">
                <input type="hidden" form="main_form" id="show_in_sitemap" name="show_in_sitemap">
                <input type="checkbox" class="form-check-input"
                       form="front_form" id="show_in_sitemap_front" name="show_in_sitemap_front"
                       data-convert="<?= $show_in_sitemap_convert ?>" data-content="<?= $show_in_sitemap; ?>"
                       value="1">
                <label class="form-check-label fw-bolder"
                       for="show_in_sitemap_front">Show in Sitemap</label>
            </div>
            <div class="form-group">
                <label for="sitemap_changefreq_front" class="fw-bolder">Sitemap - Article Change Frequency
                                                                                 (estimated)</label>
                <input type="hidden" form="main_form" id="sitemap_changefreq" name="sitemap_changefreq">
                <select form="front_form" id="sitemap_changefreq_front" name="sitemap_changefreq_front"
                        data-convert="<?= $sitemap_changefreq_convert ?>" data-content="<?= $sitemap_changefreq; ?>"
                        class="form-select w-auto min-w-25" required>
                    <?php foreach ($sitemap_changefreq_array as $sitemap_changefreq_entry) : ?>
                        <option value="<?= $sitemap_changefreq_entry; ?>">
                            <?= $sitemap_changefreq_entry; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="hidden" form="main_form" id="sitemap_priority" name="sitemap_priority">
                <label for="sitemap_priority_front" class="fw-bolder">Sitemap Priority</label>
                <input type="number" class="form-control w-auto min-w-25"
                       form="front_form" id="sitemap_priority_front" name="sitemap_priority_front"
                       data-convert="<?= $sitemap_priority_convert ?>" data-content="<?= $sitemap_priority; ?>"
                       step="0.1" min="0" max="1">
            </div>
            <div class="form-group form-check">
                <input type="hidden" form="main_form" id="show_in_rss" name="show_in_rss">
                <input type="checkbox" class="form-check-input"
                       form="front_form" id="show_in_rss_front" name="show_in_rss_front" value="1"
                       data-convert="<?= $show_in_rss_convert ?>" data-content="<?= $show_in_rss; ?>">
                <label class="form-check-label fw-bolder" for="show_in_rss_front">Show in RSS Feed</label>
            </div>
            <?php echo view_main('components/captcha'); ?>
        </form>
        <div class="row">
            <?php if ($is_modify_action): ?>
                <div class="col text-start">
                    <form id="delete_form" method="post"
                          action="<?= UrlUtils::baseUrl('/admin/article/delete/' . $article_details['article_id']); ?>">
                        <?php echo view_main('components/csrf'); ?>
                        <button type="button" id="delete_article_btn" class="btn btn-danger float-start">
                            Delete article
                        </button>
                        <button type="button" id="cancel_delete_article_btn"
                                class="btn btn-outline-danger float-start ms-lg-2 ms-0 mt-lg-0 mt-2 d-none">
                            Cancel Delete
                        </button>
                    </form>
                </div>
            <?php endif; ?>
            <div class="col text-end">
                <button type="button" id="controlled_submit"
                        class="btn btn-primary btn-primary-contrast clearfix ps-5 pe-5"
                        title="Save and continue editing">
                    Save
                </button>
            </div>
        </div>
        <hr class="mt-5">
        <small>
            <a href="<?php echo UrlUtils::baseUrl('articles'); ?>" title="Articles" target="_self">Articles</a>
        </small>
    </div>
    <?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/pages/article-edit.js'),
        ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous', 'type' => 'module']);?>
<?php else: ?>
    <div class="alert alert-warning">
        <p>Insufficient permissions</p>
    </div>
<?php endif; ?>
