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

$case_title = CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'case_title');
if ($case_title === '') {
    // reset this value for fast evaluation later
    $case_title = null;
}
$case_topic ??= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'case_topic');

$topics = [
    'Errors',
    'Product',
    'Order',
    'Employment',
    'Finances',
    'Business',
    'Others',
];
sort($topics);

?>
<div class="container w-100 w-md-50 p-2">
    <h1 class="text-end">New Support Case</h1>
    <form method="post" enctype="application/x-www-form-urlencoded"
          action="<?php echo UrlUtils::baseUrl('/support-cases/new/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <div class="form-group">
            <label for="case_title">Case Title</label>
            <input type="text" class="form-control" id="case_title" name="case_title" required minlength="8"
                   maxlength="500"<?= isset($case_title) ? ' value="' . $case_title . '"' : ''; ?>
                   autocomplete="on">
        </div>
        <div class="form-group">
            <label for="case_topic">Case Topic</label>
            <select id="case_topic" name="case_topic" class="form-select">
                <?php foreach ($topics as $topic) : ?>
                    <?php if (is_string($topic)) : ?>
                        <option value="<?php echo $topic; ?>"<?= strtolower($topic) === $case_topic ? ' selected' : '';?>>
                            <?= $topic; ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <div class="p-1 m-0 w-100 overflow-auto">
                <p id="topic_instructions" class="m-0 p-0 d-none"></p>
            </div>
        </div>
        <?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/pages/support-case-new.js'),
            ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
        <div class="form-group">
            <label for="message_content">Your Message</label>
            <textarea class="form-control" id="message_content" name="message_content"
                      required minlength="40" maxlength="2000" rows="5" autocomplete="on"
                      aria-required="true"><?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'message_content'); ?></textarea>
        </div>
        <?php echo view_main('components/captcha'); ?>
        <div class="form-group text-end">
            <button type="submit" class="btn btn-primary btn-primary-contrast">Create Support Case</button>
        </div>
    </form>
    <hr class="mt-5">
    <small>
        <a href="<?php echo UrlUtils::baseUrl('faqs'); ?>" title="FAQs" target="_self">FAQs</a>
    </small>
</div>
