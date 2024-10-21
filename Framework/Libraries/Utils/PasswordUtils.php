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

namespace Framework\Libraries\Utils;

final class PasswordUtils
{
    /**
     * IMPORTANT!
     * Custom hash for passwords.
     * Adjust if required, but do not change for the same CMD-Auth deployment.
     * CMD-Auth will only be able to recognize passwords hashed using the same logic,
     *  so it's very important to use the same hash logic for an application.
     *
     * Note that CMD-Auth stores only custom hashes of the passwords supplied, so this function is actually
     *  further increasing the security level of the application.
     *
     * CMD-Auth will never store passwords in logs!
     *
     * @param string $password
     * @return string
     * @throws \Exception
     */
    public static function hash(string $password): string
    {
        // If required adjust here.
        // IMPORTANT! When the passwords are hashed or encoded in any way,
        //   CMD-Auth's setting 'validation > password > required_characters' must match the output of this function.
        // CleanDeck checks the original format of the password using own library Validator.php. By default,
        //   the controllers validate passwords using rule 'password', but you can change this to other rules such
        //   as 'alpha_numeric', 'alpha_numeric_basic_punct', or 'alpha_numeric_punct'.

        //// EXAMPLE:
        //$prefix = '';
        //$suffix = '';
        //$p_hash = \password_hash($prefix . $password . $suffix, PASSWORD_DEFAULT);

        //// handle errors
        // if (!\is_string($p_hash)) {
        //    $err_msg = 'Password hash error';
        //    if (\is_null($p_hash)) {
        //        $err_msg .= ': invalid algorithm';
        //    } elseif ($p_hash === false) {
        //        $err_msg .= ': hash failed';
        //    }
        //    throw new \Exception($err_msg);
        //}
        //return $p_hash;
        //// END EXAMPLE:

        // By default, the password is unchanged.
        return $password;
    }
}
