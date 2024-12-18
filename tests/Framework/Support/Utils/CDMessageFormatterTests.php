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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CDMessageFormatter::class)]
final class CDMessageFormatterTests extends TestCase
{
    public function testHeader(): void
    {
        ob_start();
        $cdMessageFormatter = new CDMessageFormatter('Header', 'Success', 'Fail');
        $cdMessageFormatter->header('HEADER_2' . PHP_EOL . 'Multi-line');
        $cdMessageFormatter->success();

        $content = ob_get_clean();

        $this->assertStringContainsString('HEADER_2', $content);
        $this->assertStringContainsString('Success', $content);
    }

    public function testRoute_details(): void
    {
        ob_start();
        $cdMessageFormatter = new CDMessageFormatter('Header', 'Success', 'Fail');
        $cdMessageFormatter->route_details('GET', '/endpoint', [
            'controller' => '\\Path\\ControllerClass',
            'method' => 'index',
        ]);
        $cdMessageFormatter->fail();

        $content = ob_get_clean();

        $this->assertStringContainsString('ControllerClass', $content);
        $this->assertStringContainsString('Fail', $content);
    }

    public function testMiniFunctions(): void
    {
        ob_start();
        $cdMessageFormatter = new CDMessageFormatter('Header', 'Success', 'Fail');
        $cdMessageFormatter->subsection('Subsection');
        $cdMessageFormatter->bold('Bold');
        $cdMessageFormatter->code('Code');
        $cdMessageFormatter->content('Content');
        $cdMessageFormatter->critical('Critical');
        $cdMessageFormatter->error('Error');
        $cdMessageFormatter->important('Important');
        $cdMessageFormatter->prompt('Prompt');
        $cdMessageFormatter->remark('Remark');
        $cdMessageFormatter->warn('Warn');
        $cdMessageFormatter->success();

        $content = ob_get_clean();

        $this->assertStringContainsString('SUBSECTION', $content);
        $this->assertStringContainsString('Bold', $content);
        $this->assertStringContainsString('Code', $content);
        $this->assertStringContainsString('Content', $content);
        $this->assertStringContainsString('Critical', $content);
        $this->assertStringContainsString('Error', $content);
        $this->assertStringContainsString('Important', $content);
        $this->assertStringContainsString('Prompt', $content);
        $this->assertStringContainsString('Remark', $content);
        $this->assertStringContainsString('Warn', $content);
        $this->assertStringContainsString('Success', $content);
    }
}
