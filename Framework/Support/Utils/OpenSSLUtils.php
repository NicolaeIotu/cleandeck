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

namespace Framework\Support\Utils;

require_once __DIR__ . '/CDMessageFormatter.php';
require_once __DIR__ . '/../../Libraries/Utils/WarningHandler.php';

use Framework\Libraries\Utils\WarningHandler;

class OpenSSLUtils
{
    public static function showSSLErrors(string $title = 'SSL Errors: ', bool $log = false): void
    {
        $errors = '';

        while (($e = \openssl_error_string()) !== false) {
            $errors .= $e . PHP_EOL;
        }

        if ($errors !== '') {
            $errors = $title . PHP_EOL . $errors;
            if ($log) {
                \error_log($errors);
            } else {
                echo $errors;
            }
        }
    }

    /**
     * @throws \Exception
     */
    public static function generateSelfSignedCertificate(): void
    {
        $ssl_settings_ini_basename = 'ssl-settings.ini';
        $ssl_settings_ini_relative_path = 'deploy/ssl/' . $ssl_settings_ini_basename;
        $ssl_settings_ini_path = __DIR__ . '/../../../' . $ssl_settings_ini_relative_path;
        if (!\file_exists($ssl_settings_ini_path)) {
            throw new \Exception('Missing file ' . $ssl_settings_ini_relative_path);
        }
        $ssl_settings_ini = \parse_ini_file($ssl_settings_ini_path, true, INI_SCANNER_TYPED);
        if (!\is_array($ssl_settings_ini)) {
            throw new \Exception('Cannot parse file ' . $ssl_settings_ini_relative_path);
        }

        // Generate a Private+Public key pair
        $private_key = \openssl_pkey_new($ssl_settings_ini['openssl-key-settings']);
        if ($private_key === false) {
            throw new \Exception('Cannot generate key pair');
        }

        // Generate a certificate signing request
        $csr = \openssl_csr_new($ssl_settings_ini['certificate-details'],
            $private_key, $ssl_settings_ini['openssl-other-settings']);
        if ($csr === false) {
            throw new \Exception('Cannot generate certificate signing request');
        }

        // Generate a self-signed certificate
        $x509 = \openssl_csr_sign($csr, null, $private_key,
            $ssl_settings_ini['certificate-settings']['days'], $ssl_settings_ini['openssl-other-settings']);
        if ($x509 === false) {
            throw new \Exception('Cannot generate self-signed certificate');
        }


        $private_key_path = 'deploy/ssl/generated/cleandeck.key';
        $certificate_signing_request_path = 'deploy/ssl/generated/cleandeck.csr';
        $self_signed_certificate_path = 'deploy/ssl/generated/cleandeck.crt';

        // cleanup
        if (\file_exists($private_key_path)) {
            if (!WarningHandler::run(static fn (): bool => \unlink($private_key_path))) {
                throw new \Exception('Cannot remove previous private key: ' . $private_key_path);
            }
        }
        if (\file_exists($certificate_signing_request_path)) {
            if (!WarningHandler::run(static fn (): bool => \unlink($certificate_signing_request_path))) {
                throw new \Exception('Cannot remove previous certificate signing request: ' .
                    $certificate_signing_request_path);
            }
        }
        if (\file_exists($self_signed_certificate_path)) {
            if (!WarningHandler::run(static fn (): bool => \unlink($self_signed_certificate_path))) {
                throw new \Exception('Cannot remove previous self-signed certificate: ' .
                    $self_signed_certificate_path);
            }
        }
        // end cleanup

        // Save certificate components:
        //   - save private key
        if (\openssl_pkey_export_to_file($private_key, $private_key_path,
            $ssl_settings_ini['certificate-settings']['password'])) {
            if (!\chmod($private_key_path, 0o400)) {
                \printf("\033[1;31m%s\033[0m\n",
                    'Cannot set permissions 0400 for file ' . $private_key_path);
            }
        } else {
            self::showSSLErrors();
            throw new \Exception('Cannot save the private key');
        }
        //   - save CSR
        if (\openssl_csr_export_to_file($csr, $certificate_signing_request_path)) {
            if (!\chmod($certificate_signing_request_path, 0o400)) {
                \printf("\033[1;31m%s\033[0m\n",
                    'Cannot set permissions 0400 for file ' . $certificate_signing_request_path);
            }
        } else {
            self::showSSLErrors();
            throw new \Exception('Cannot save certificate signing request');
        }
        //   - save self-signed certificate
        if (\openssl_x509_export_to_file($x509, $self_signed_certificate_path)) {
            if (!\chmod($self_signed_certificate_path, 0o400)) {
                \printf("\033[1;31m%s\033[0m\n",
                    'Cannot set permissions 0400 for file ' . $self_signed_certificate_path);
            }
        } else {
            self::showSSLErrors();
            throw new \Exception('Cannot save the self-signed certificate');
        }

        self::showSSLErrors('SSL Errors found after running generateSelfSignedCertificate: ', true);
    }
}
