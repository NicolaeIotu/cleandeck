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

// IMPORTANT!
// Multiple instances of this script on the same page are allowed.


if (!defined('CLEANDECK_APP_PATH')) {
    return exit('No direct script access allowed');
}

use Framework\Libraries\CSRF\CSRFConstants;
use Framework\Libraries\Utils\UrlUtils;

?>

<input type="hidden" name="<?= CSRFConstants::CSRF_TOKEN ?>" value="1" data-csrf
       data-url="<?= UrlUtils::baseUrl('/csrf'); ?>"/>
<?php if (!defined('CSRF_JS_SCRIPT')): ?>
<?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/csrf.js'),
    ['defer' => 'true', 'referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
<?php define('CSRF_JS_SCRIPT', true); endif; ?>
