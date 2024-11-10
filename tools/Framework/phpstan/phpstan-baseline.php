<?php declare(strict_types=1);

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

$ignoreErrors = [];

defined('CLEANDECK_FRAMEWORK_PATH') || define('CLEANDECK_FRAMEWORK_PATH', dirname(__DIR__, 3) . '/Framework');

/*
 * Add ignored errors below.
 */
$ignoreErrors[] = [
    'message' => '#^Variable \\$fileOpsQueue in isset\\(\\) is never defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Controllers/Main/ArticleShowController.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$fileOpsQueue might not be defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Controllers/Main/ArticleShowController.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$fileOpsQueue in isset\\(\\) is never defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Controllers/Main/FaqShowController.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$fileOpsQueue might not be defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Controllers/Main/FaqShowController.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$fileOpsQueue in isset\\(\\) is never defined\\.$#',
    'count' => 2,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Controllers/Main/ChangeUserDetailsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$articles might not be defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Views/core/main/page-content/articles_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$account_email might not be defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Views/core/main/page-content/authenticated/admin/account_history.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$account_history might not be defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Views/core/main/page-content/authenticated/admin/account_history.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$unapproved_details might not be defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Views/core/main/page-content/authenticated/admin/approve_accounts.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$support_cases might not be defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Views/core/main/page-content/authenticated/admin/support_cases_search_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$unverified_details might not be defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Views/core/main/page-content/authenticated/admin/verify_accounts.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$case_details might not be defined\\.$#',
    'count' => 3,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Views/core/main/page-content/authenticated/user/support_cases_case_details.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$support_cases might not be defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Views/core/main/page-content/authenticated/user/support_cases_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$faqs might not be defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Views/core/main/page-content/faqs_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Framework\\\Libraries\\\Tasks\\\DynamicDirectoryCleanup ' .
        'has an uninitialized readonly property \\$(.)+\\. Assign it in the constructor\\.$#',
    'count' => 5,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Libraries/Tasks/DynamicDirectoryCleanup.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Libraries/Utils/UrlUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Static method Framework\\\Middleware\\\Main\\\HttpCaching::translateTags\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Middleware/Main/HttpCaching.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$url in isset\\(\\) is never defined\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Middleware/Main/HttpCaching.php',
];
$ignoreErrors[] = [
    'message' => '#^Dead catch - Error is never thrown in the try block\\.$#',
    'count' => 2,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Support/Controllers/CLIController.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$callback of function set_error_handler expects' .
        ' \\(callable\\(int, string, string, int\\): bool\\)|null, Closure\\(int, string\\): void given\\.$#',
    'count' => 1,
    'path' => CLEANDECK_FRAMEWORK_PATH . '/Libraries/Utils/WarningHandler.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
