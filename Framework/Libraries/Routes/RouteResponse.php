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

namespace Framework\Libraries\Routes;

use Framework\Libraries\Utils\ErrorUtils;

class RouteResponse extends MiddlewareHandler
{
    /**
     * @param string $controller
     * @param string $function
     * @param array<mixed>|null $function_parameters
     * @param string[]|null $middleware
     */
    public function __construct(string $controller, string $function,
                                array  $function_parameters = null, array $middleware = null)
    {
        parent::__construct($middleware);
        // Middleware 'before'
        try {
            $bh_result = $this->runBeforeMiddleware();
            if (\is_string($bh_result)) {
                echo $bh_result;
                return;
            }
        } catch (\Error|\Exception $e) {
            \http_response_code(500);
            echo ErrorUtils::prettify($e);
        }

        // main content
        $this->response_body = $this->processMainContent($controller, $function, $function_parameters);


        // Middleware 'after'
        try {
            $this->runAfterMiddleware();
        } catch (\Error|\Exception $e) {
            \http_response_code(500);
            echo ErrorUtils::prettify($e);
        }

        echo $this->response_body;

        // development info bar
        if (\env('cleandeck.ENVIRONMENT') === 'development' &&
            $this->isHtmlContent()) {
            $_ENV['dev_info_bar_data'] = [
                'controller' => $controller,
                'method' => $function,
            ];
            $this->showDevelopmentInfo();
        }
    }

    private function isHtmlContent(): bool
    {
        foreach (\headers_list() as $header_value) {
            if (\preg_match('/^content-type:.*html/i', $header_value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $controller
     * @param string $function
     * @param array<mixed>|null $function_parameters
     * @return string
     */
    private function processMainContent(string $controller, string $function,
                                        array  $function_parameters = null): string
    {
        \ob_start();
        try {
            if (\is_null($function_parameters)) {
                (new $controller())->$function();
            } else {
                (new $controller())->$function(...$function_parameters);
            }
            $main_content = \ob_get_contents();
            \ob_end_clean();
            if ($main_content === false) {
                \http_response_code(500);
                return 'Cannot retrieve the main content';
            }

            return $main_content;
        } catch (\Error|\Exception $e) {
            \ob_end_clean();
            \http_response_code(500);
            if (\env('cleandeck.ENVIRONMENT') !== 'production') {
                return ErrorUtils::prettify($e);
            }
            return 'Internal Server Error';
        }
    }

    private function showDevelopmentInfo(): void
    {
        require_once CLEANDECK_FRAMEWORK_PATH . '/Support/Views/components/dev-info-bar.php';
    }
}
