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

use Framework\Libraries\Utils\UrlUtils;

final class EmailTemplates
{
    // The templates below are used as '$format' with function 'sprintf',
    //   so a simple '%' character (with no intended specifiers) must be escaped using '%%'.
    public const CONTACT_FORM = <<<TPL
        <h1 style="font-size: 18px;">Contact form of %BASEURL%</h1>
        <p style="margin-top: 20px;">Message from <a href="mailto:%s">%s</a>:</p>
        <p> %s</p>
        <p>End of user message.</p>
        TPL;

    public const ACTIVATE_ACCOUNT = <<<TPL
        <p>A fresh account was created for you at <em>%BASEURL%</em>.<br>
        Your account will first be verified by administrators.<br>
        <a target="_blank" href="%s">Activate your account now</a>.</p>
        <p style="margin-top: 20px;">Alternatively you may copy the link below 
        to the address bar of your browser:<br>
        <a target="_blank" href="%s">%s</a></p>
        <p style="margin-top: 20px;"><sub>Your email address was used to create 
        a new account at <a target="_blank" href="%BASEURL%">%BASEURL%</a>.
        If you do not recognize this activity please ignore the email and optionally
        <a target="_blank" href="%BASEURL%/support-cases/new">inform us</a>.</sub></p>
        TPL;

    public const AUTO_SIGNUP = <<<TPL
        <p>A fresh account was created for you at <em>%BASEURL%</em>.<br>
        Your account will first be verified by administrators.</p>
        <p style="margin-top: 20px;">When your account will be approved
        <a target="_blank" href="%BASEURL%/login">login</a> 
        using the following password:<br>
        <span style="font-weight: bolder; font-size: 140%%; margin-top: 20px;">%s</span></p>
        <p style="margin-top: 20px;"><sub>Your email address was used to create 
        a new account at <a target="_blank" href="%BASEURL%">%BASEURL%</a>.
        If you do not recognize this activity please ignore the email and optionally
        <a target="_blank" href="%BASEURL%/support-cases/new">inform us</a>.</sub></p>
        TPL;

    public const MFA_LOGIN = <<<TPL
        <p>Your login code for <em>%BASEURL%</em>:</p>
        <p style="font-weight: bolder; font-size: 200%%; margin-top: 20px;">%s</p>
        <p style="margin-top: 20px;"><sub>You received this email because 
        your account at <em>%BASEURL%</em>
        has MFA by email enabled and a login request was received.
        If you do not recognize this activity it means somebody else 
        might be trying to access your account
        at which point you should ignore the email and optionally
        <a target="_blank" href="%BASEURL%/support-cases/new">inform us</a>.</sub></p>
        TPL;

    public const ACTIVATE_EMAIL = <<<TPL
        <p>A fresh email address was added to your account at <em>%BASEURL%</em>.<br>
        <a target="_blank" href="%s">Activate your email now</a>.</p>
        <p style="margin-top: 20px;">Alternatively you may copy the link below 
        to the address bar of your browser:<br>
        <a target="_blank" href="%s">%s</a></p>
        <p style="margin-top: 20px;"><sub>If you do not recognize this activity 
        please ignore the email and optionally
        <a target="_blank" href="%BASEURL%/support-cases/new">inform us</a>.</sub></p>
        TPL;

    public const ACTIVATE_NEW_PRIMARY_EMAIL = <<<TPL
        <p>A request to change the primary email for your account 
        was received at <em>%BASEURL%</em>.<br>
        <a target="_blank" href="%s">Activate your new primary email now</a>.</p>
        <p style="margin-top: 20px;">Alternatively you may copy the link below 
        to the address bar of your browser:<br>
        <a target="_blank" href="%s">%s</a></p>
        <p style="margin-top: 20px;"><sub>If you do not recognize this activity 
        please ignore the email and optionally
        <a target="_blank" href="%BASEURL%/support-cases/new">inform us</a>.</sub></p>
        TPL;

    public const RESET_PASSWORD = <<<TPL
        <p>A password reset request was received for your account <em>%s</em>.<br>
        <a target="_blank" href="%s">Change Password</a>.</p>
        <p style="margin-top: 20px;">Alternatively you may copy the link below 
        to the address bar of your browser:<br>
        <a target="_blank" href="%s">%s</a></p>
        <p style="margin-top: 20px;"><sub>If you do not recognize this activity 
        it means somebody else might be trying to access your account
        at which point you should ignore the email and optionally
        <a target="_blank" href="%BASEURL%/support-cases/new">inform us</a>.</sub></p>
        TPL;

    public const SEND_INVOICE = <<<TPL
        <p>A new invoice was generated for
        <a target="_blank" href="%s">%s</a>.</p>
        <p style="margin-top: 10px;">You can find the invoice attached 
        to this email and <a target="_blank" href="%s">online</a>.</p>
        TPL;

    // /////////////////////// Methods /////////////////////////
    /**
     * @param string[] $arr
     */
    private static function sprintf_array(string $format, array $arr): string
    {
        return \sprintf($format, ...$arr);
    }

    public static function buildEmail(string $template, mixed ...$vars): string
    {
        $adj_template = \str_replace(
            [
                '%BASEURL%',
            ],
            [
                UrlUtils::baseUrl(),
            ],
            $template
        );

        return self::sprintf_array($adj_template, $vars);
    }
}
