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

use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\Set\ValueObject\SetList;

$skip_additional = [];

// Ignored errors which are related with user's libraries can be declared in up to 3 files:
//  - rector-skip-additional-1.php
//  - rector-skip-additional-2.php
//  - rector-skip-additional-3.php

$i = 0;
while (++$i <= 3 && file_exists(__DIR__ . '/rector-skip-additional-' . $i . '.php')) {
    // IMPORTANT! Don't use here include_once or require_once.
    include __DIR__ . '/rector-skip-additional-' . $i . '.php';
}

return static function (RectorConfig $rectorConfig) use ($skip_additional): void {
    $root_dir = dirname(__DIR__, 3);

    $rectorConfig->paths([
        $root_dir . '/Application'
    ]);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::STRICT_BOOLEANS,
        SetList::NAMING,
        SetList::PHP_81,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
    ]);

    $rectorConfig->importShortClasses(false);
    $rectorConfig->removeUnusedImports();

    $rectorConfig->parallel();

    $rectorConfig->phpstanConfigs([
        __DIR__ . '/../phpstan/phpstan.neon.dist'
    ]);

    // skip
    $rectorConfig->skip(
        array_merge([
            CombineIfRector::class,
            ShortenElseIfRector::class,
            JoinStringConcatRector::class,
            SimplifyIfElseToTernaryRector::class,
            NewlineAfterStatementRector::class,
            RemoveAlwaysTrueIfConditionRector::class,
            RemoveUselessParamTagRector::class,
            RemoveUselessReturnTagRector::class,
        ], $skip_additional));

    $rectorConfig->autoloadPaths([
        $root_dir . '/vendor/autoload.php',
    ]);

    $rectorConfig->bootstrapFiles([
        $root_dir . '/Framework/Config/constants.php',
        $root_dir . '/Framework/Libraries/common.php',
        $root_dir . '/Framework/Config/dynamic-constants.php',
    ]);

    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();
};
