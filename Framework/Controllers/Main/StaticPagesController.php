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

namespace Framework\Controllers\Main;

use Framework\Libraries\View\HtmlView;

final class StaticPagesController
{
    public function privacy_and_cookies(): void
    {
        echo new HtmlView('main/page-content/privacy-and-cookies');
    }

    public function terms_and_conditions(): void
    {
        echo new HtmlView('main/page-content/terms-and-conditions');
    }

    public function administration(): void
    {
        echo new HtmlView('main/page-content/authenticated/admin/administration');
    }
}
