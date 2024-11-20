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

namespace Framework\Libraries\View;

use Framework\Interfaces\HtmlViewInterface;

require_once CLEANDECK_FRAMEWORK_PATH . '/Libraries/common.php';

/**
 * This class can construct complex views with components and page-content
 *  which belong exclusively to one of the categories: Framework, Application or Addon.
 * HtmlView will use the template selected in file .env.ini.
 *
 * In order to construct complex views with mixed components and page-content
 *  you have to create your own HtmlView preferably in directory Application/Instance/Libraries/View.
 * When creating a custom HtmlView, start by copying this class and adjust it as required.
 */
class HtmlView implements HtmlViewInterface
{
    protected string $html_view;

    public const VIEWTYPE_FRAMEWORK = 1;
    public const VIEWTYPE_APPLICATION = 2;


    /**
     * Usage: <code>echo new HtmlView('home', $data);</code>
     * @param string $main_content_file Do not include file extension '.php'.
     * @param array<string, mixed> $data
     * @param int|string $view_type One of HtmlView::VIEWTYPE_FRAMEWORK (for Framework components),
     *  HtmlView::VIEWTYPE_APPLICATION (for custom components in Application),
     *  or a custom string designating the selected Framework addon (no addons at the moment).
     * @throws \Exception
     */
    public function __construct(string     $main_content_file,
                                array      $data = [],
                                int|string $view_type = self::VIEWTYPE_FRAMEWORK)
    {
        $source_directory = match ($view_type) {
            self::VIEWTYPE_FRAMEWORK => CLEANDECK_ROOT_PATH . '/Framework/Views/' .
                env('cleandeck.template', 'core') . '/main',
            self::VIEWTYPE_APPLICATION => CLEANDECK_ROOT_PATH . '/Application/Instance/Views/' .
                env('cleandeck.template', 'core'),
            default => CLEANDECK_ROOT_PATH . '/Framework/Views/' .
                env('cleandeck.template', 'core') . '/addon/' . $view_type,
        };

        $this->html_view = \view(
            [
                $source_directory . '/components/head',
                '<body>',
                $source_directory . '/components/navigation',
                '<main class="container-xxl mt-6 p-2" role="main">',
                $source_directory . '/components/alerts',
                $source_directory . '/components/seo-inspect',
                $source_directory . '/page-content/' . $main_content_file,
                '</main>',
                $source_directory . '/components/noscript',
                $source_directory . '/components/footer',
                $source_directory . '/components/global-cookies',
                '</body>',
                '</html>',
            ],
            $data);
    }

    public function __toString(): string
    {
        return $this->html_view;
    }
}
