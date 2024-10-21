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

final class ErrorUtils
{
    public static function prettify(\Error|\Exception $e): string
    {
        $type = $e instanceof \Error ? 'Error' : 'Exception';

        $r = '<div class="alert alert-warning pb-0">';
        $r .= '<h4 class="alert-heading">' . $type . ' @ line ' . $e->getLine() . ' ' . $e->getFile() .
            ' [code ' . $e->getCode() . ']' . '</h4><hr>';
        $r .= '<p>';

        $p = $e;
        while (!\is_null($p)) {
            $r .= PHP_EOL . $p->getMessage() . ' [code ' . $p->getCode() . ']' . PHP_EOL .
                '&nbsp;&nbsp;&nbsp;&nbsp;- line ' . $p->getLine() . ' ' . $p->getFile();
            $p = $p->getPrevious();
        }
        $r .= '</p>';
        $r .= '<p class="small">';
        $r .= 'Trace: ' . PHP_EOL . $e->getTraceAsString();
        $r .= '</p></div>';

        return \nl2br($r);
    }
}
