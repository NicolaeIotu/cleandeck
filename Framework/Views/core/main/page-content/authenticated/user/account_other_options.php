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

use Framework\Libraries\Utils\UrlUtils;

?>
<div class="container w-100 w-sm-75 w-md-50 p-2">
    <h1 class="text-end">Other Options</h1>
    <ul class="list-group" id="main-list-group">
        <li class="list-group-item cursor-pointer" aria-current="false">
            <div class="d-flex w-100 justify-content-between">
                <p class="h5 mb-1">Log out from All Other Sessions</p>
            </div>
            <p class="mb-1">Keep only this session.</p>
            <small>Remove any other sessions except this session.</small>
            <div class="tt23699 d-none">
                <hr>
                <p class="mb-1">Are you sure you want to log out from all other sessions?</p>
                <div class="d-flex w-100 justify-content-between">
                    <form name="confirmedAction1" id="confirmedAction1" method="post"
                          action="<?php echo UrlUtils::baseUrl('/logout-all-except-this'); ?>">
                        <?php echo view_main('components/csrf'); ?>
                        <button type="button" form="confirmedAction1"
                                class="btn btn-light ms-5">Yes
                        </button>
                    </form>
                    <button type="button"
                            class="btn btn-light me-5">No
                    </button>
                </div>
            </div>
        </li>
        <li class="list-group-item cursor-pointer" aria-current="false">
            <div class="d-flex w-100 justify-content-between">
                <p class="h5 mb-1">Log Out from All Sessions</p>
            </div>
            <p class="mb-1">You will have to log in again on all devices and applications.</p>
            <div class="tt23699 d-none">
                <hr>
                <p class="mb-1">Are you sure you want to log out from all sessions?</p>
                <div class="d-flex w-100 justify-content-between">
                    <form name="confirmedAction2" id="confirmedAction2" method="post"
                          action="<?php echo UrlUtils::baseUrl('/logout-all'); ?>">
                        <?php echo view_main('components/csrf'); ?>
                        <button type="button" form="confirmedAction2"
                                class="btn btn-light ms-5">Yes
                        </button>
                    </form>
                    <button type="button"
                            class="btn btn-light me-5">No
                    </button>
                </div>
            </div>
        </li>
        <li class="list-group-item cursor-pointer" aria-current="false">
            <div class="d-flex w-100 justify-content-between">
                <p class="h5 mb-1">Hibernate Account</p>
            </div>
            <p class="mb-1">Mark account as hibernating.</p>
            <small>Log in to activate a hibernating account.</small>
            <div class="tt23699 d-none">
                <hr>
                <p class="mb-1">Are you sure you want to hibernate your account?</p>
                <div class="d-flex w-100 justify-content-between">
                    <form name="confirmedAction3" id="confirmedAction3" method="post"
                          action="<?php echo UrlUtils::baseUrl('/hibernate-account'); ?>">
                        <?php echo view_main('components/csrf'); ?>
                        <button type="button" form="confirmedAction3"
                                class="btn btn-light ms-5">Yes
                        </button>
                    </form>
                    <button type="button"
                            class="btn btn-light me-5">No
                    </button>
                </div>
            </div>
        </li>
        <li class="list-group-item list-group-item-warning cursor-pointer" aria-current="false">
            <div class="d-flex w-100 justify-content-between">
                <p class="h5 mb-1">Delete account</p>
            </div>
            <p class="mb-1">Permanently delete your account.</p>
            <div class="tt23699 d-none">
                <hr>
                <p class="mb-1">Are you sure you want to Permanently delete your account?</p>
                <div class="d-flex w-100 justify-content-between">
                    <form name="confirmedAction4" id="confirmedAction4" method="get"
                          action="<?php echo UrlUtils::baseUrl('/delete-account'); ?>">
                        <?php echo view_main('components/csrf'); ?>
                        <button type="button" form="confirmedAction4"
                                class="btn btn-danger ms-5">Yes
                        </button>
                    </form>
                    <button type="button"
                            class="btn btn-success me-5">No
                    </button>
                </div>
            </div>
        </li>
    </ul>
    <?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/pages/account-other-options.js'),
        ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
</div>
