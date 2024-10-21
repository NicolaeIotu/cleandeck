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

use Framework\Libraries\Cookie\CookieMessengerWriter;
use Framework\Libraries\Email\EmailTemplates;
use Framework\Libraries\Email\SendEmail;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Validator\Validator;
use Framework\Libraries\View\HtmlView;

final class ContactController
{
    public function index(): void
    {
        $data = [
            'seo_description' => 'Contact',
        ];

        echo new HtmlView('main/page-content/contact', true, $data);
    }


    public function remote_request(): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/contact');

        $validator = new Validator([
            'email' => ['email'],
            'message' => ['min_length' => 50, 'max_length' => 2000],
        ]);
        if ($validator->redirectOnError($redirect_on_error_url, $_POST)) {
            return;
        }


        $email = $_POST['email'];
        $message = \strip_tags((string)$_POST['message']);

        // send AWS SES email
        try {
            $sendEmailResult = SendEmail::init(
                'Contact Form ' . \env('cleandeck.baseURL'),
                \env('cleandeck.CONTACT_EMAIL', 'Missing CONTACT_EMAIL'),
                EmailTemplates::buildEmail(EmailTemplates::CONTACT_FORM, $email, $email, $message)
            );
        } catch (\Exception $exception) {
            $info_message = 'Error when trying to send your message: ' . $exception->getMessage() . PHP_EOL;
            $sendEmailResult = false;
        }

        if ($sendEmailResult) {
            $info_message = 'Your message was successfully sent.';
        } else {
            if (!isset($info_message)) {
                $info_message = 'There was an error when trying to send your message. ' . PHP_EOL;
            }
            $info_message .= 'Please try again later.' . PHP_EOL;
        }

        CookieMessengerWriter::setMessage(
            null,
            $sendEmailResult === false,
            nl2br($info_message),
            $sendEmailResult === false ? $_POST : null
        );

        HttpResponse::redirectTo(UrlUtils::baseUrl());
    }
}
