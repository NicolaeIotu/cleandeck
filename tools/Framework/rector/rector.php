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

use Rector\CodeQuality\Rector\ClassMethod\ExplicitReturnNullRector;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $root_dir = dirname(__DIR__, 3);

    $rectorConfig->paths([
        $root_dir . '/Framework',
        $root_dir . '/bin',
        $root_dir . '/deploy',
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
    $rectorConfig->skip([
        CombineIfRector::class,
        ShortenElseIfRector::class,
        JoinStringConcatRector::class,
        SimplifyIfElseToTernaryRector::class,
        NewlineAfterStatementRector::class,
        RemoveAlwaysTrueIfConditionRector::class,
        ReadOnlyPropertyRector::class => [
            $root_dir . '/Framework/Libraries/Background/DynamicDirectoryCleanup.php'
        ],
        ExplicitReturnNullRector::class => [
            $root_dir . '/Framework/Libraries/Routes/MiddlewareHandler.php',
            $root_dir . '/Framework/Middleware/Main/HttpCaching.php'
        ],
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        RemoveUnusedPrivateMethodRector::class => [
            $root_dir . '/Framework/Middleware/Main/HttpCaching.php'
        ],
        LocallyCalledStaticMethodToNonStaticRector::class => [
            $root_dir . '/Framework/Middleware/Main/HttpCaching.php'
        ]
    ]);

    $rectorConfig->autoloadPaths([
        $root_dir . '/vendor/autoload.php',
    ]);

    $rectorConfig->bootstrapFiles([
        $root_dir . '/Framework/Config/Constants.php',
        $root_dir . '/Framework/Libraries/Utils/DotEnv.php',
        $root_dir . '/Framework/Libraries/Common.php',
        $root_dir . '/Framework/Config/DynamicConstants.php',
    ]);

    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();
};
