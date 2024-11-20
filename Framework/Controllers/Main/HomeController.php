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

final class HomeController
{
    /**
     * @throws \Exception
     */
    public function index(): void
    {
        $data = [
            'seo_description' => 'Home',
        ];

        echo new HtmlView('home', $data);
    }
}
