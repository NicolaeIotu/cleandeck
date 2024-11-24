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

$project_root_dir = dirname(__DIR__, 3);

$finder = (new PhpCsFixer\Finder())
    ->in([
        $project_root_dir . '/tests/Application',
        $project_root_dir . '/tests/Framework',
    ])
    ->name(['*.php']);

$setup = (new PhpCsFixer\Config())
    ->setCacheFile($project_root_dir . '/build/tests/php-cs-fixer/.php-cs-fixer.cache')
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        '@PHP82Migration' => true,
        'braces_position' => [
            'functions_opening_brace' => 'next_line_unless_newline_at_signature_end'
        ],
        'method_argument_space' => false,
        'no_leading_import_slash' => false,
        'statement_indentation' => false,
    ])
    ->setUsingCache(true);
