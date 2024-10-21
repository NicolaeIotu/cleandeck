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

namespace Framework\Libraries\Validator;

use Framework\Libraries\Cookie\CookieMessengerWriter;
use Framework\Libraries\Http\HttpResponse;

/**
 * Use this class to add utilities that depend on other libraries.
 *
 * Remember to keep parent class 'ValidatorCore' free of any dependencies including Framework dependencies.
 */
final class Validator extends ValidatorCore
{
    /**
     * @param array<string, string>|null $previous_form_data
     */
    public function redirectOnError(string $redirect_to, array $previous_form_data = null): bool
    {
        if ($this->hasErrors()) {
            $form_validation_errors = $this->getErrors();
            $full_error_msg = 'Please correct the following errors and try again: ';
            foreach ($form_validation_errors as $form_validation_error) {
                $full_error_msg .= PHP_EOL . ' - ' . $form_validation_error;
            }

            CookieMessengerWriter::setMessage(
                null,
                true,
                $full_error_msg,
                $previous_form_data
            );
            HttpResponse::redirectTo($redirect_to);
        }

        return $this->hasErrors();
    }
}
