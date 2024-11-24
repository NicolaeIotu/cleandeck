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

use PHPUnit\Framework\TestCase;

final class IntlUtilsTests extends TestCase
{
    /**
     * @coversDefaultClass
     */
    public function testInitIncompleteParameter(): void
    {
        $numberFormatter = IntlUtils::numberFormatterDecimal();
        $this->assertEquals($numberFormatter->format(12345.6789), '12,345.679');
    }
}
