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

namespace Framework\Libraries\Email;

use Framework\Libraries\Email\SendEmail;
use Framework\Libraries\Utils\UrlUtils;

final class SignupEmail
{
    public static function send(
        string $email,
        string $email_subject,
        string $email_content
    ): string {
        $info_message = 'Signup request received. ' . PHP_EOL;

        // send AWS SES email
        try {
            $sendEmailResult = SendEmail::init(
                $email_subject,
                $email,
                $email_content
            );
        } catch (\Exception) {
            $sendEmailResult = false;
        }

        if ($sendEmailResult) {
            return $info_message . ('Your account must be approved by administrators. Please check the email for signup instructions. ' . PHP_EOL);
        }
        $info_message .= 'There was an error when trying to email signup instructions. ' . PHP_EOL .
            'Please <a title="Contact Us" href="' . UrlUtils::baseUrl('/contact') .
            '">contact us</a>, or try to sign up using another email address.' . PHP_EOL;
        throw new \Exception($info_message);
    }
}
