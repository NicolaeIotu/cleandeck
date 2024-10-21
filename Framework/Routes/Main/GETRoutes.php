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

namespace Application\Instance\Config\Routes;

use Framework\Controllers\Main\AccountHistoryController;
use Framework\Controllers\Main\AgreementLifecycleController;
use Framework\Controllers\Main\AgreementsController;
use Framework\Controllers\Main\ActivateEmailController;
use Framework\Controllers\Main\ActivateUserController;
use Framework\Controllers\Main\ApproveAccountsController;
use Framework\Controllers\Main\ArticleLifecycleController;
use Framework\Controllers\Main\ArticleShowController;
use Framework\Controllers\Main\CaptchaController;
use Framework\Controllers\Main\ChangeMfaController;
use Framework\Controllers\Main\ChangePasswordController;
use Framework\Controllers\Main\ChangePasswordOnResetController;
use Framework\Controllers\Main\ChangePrimaryEmailController;
use Framework\Controllers\Main\ChangeUserDetailsController;
use Framework\Controllers\Main\ChangeUsernameController;
use Framework\Controllers\Main\ConfirmPasswordController;
use Framework\Controllers\Main\ContactController;
use Framework\Controllers\Main\CSRFController;
use Framework\Controllers\Main\EmployeesAdministrationController;
use Framework\Controllers\Main\ErrorController;
use Framework\Controllers\Main\FaqLifecycleController;
use Framework\Controllers\Main\FaqShowController;
use Framework\Controllers\Main\HomeController;
use Framework\Controllers\Main\LoginController;
use Framework\Controllers\Main\Oauth2GoogleController;
use Framework\Controllers\Main\OtherOptionsController;
use Framework\Controllers\Main\ResetPasswordController;
use Framework\Controllers\Main\RssFeedController;
use Framework\Controllers\Main\ShowDetailsController;
use Framework\Controllers\Main\SignupController;
use Framework\Controllers\Main\SitemapController;
use Framework\Controllers\Main\StaticPagesController;
use Framework\Controllers\Main\SupportCasesAdminsController;
use Framework\Controllers\Main\SupportCasesController;
use Framework\Controllers\Main\SupportCasesLifecycleController;
use Framework\Controllers\Main\UserController;
use Framework\Controllers\Main\VerifyAccountsController;
use Framework\Libraries\Routes\RouteCollection;
use Framework\Middleware\Main\AAAInit;
use Framework\Middleware\Main\Admin;
use Framework\Middleware\Main\ApplicationStatusJWT;
use Framework\Middleware\Main\CSP;
use Framework\Middleware\Main\HttpCaching;
use Framework\Middleware\Main\SEO;
use Framework\Middleware\Main\Throttle;
use Framework\Middleware\Main\UserDetails;
use Framework\Support\Controllers\CLIController;

if (\defined('CLEANDECK_LIST_ROUTES')) {
    // RouteCollection class used by utility 'cleandeck-routes' (composer exec cleandeck-routes).
    if (!isset($routes)) {
        $routes = new \Framework\Support\Utils\RouteCollection();
    }
} else {
    // RouteCollection class used for normal routing.
    $routes = new RouteCollection();
}

// Methods with names containing 'ajax_request' are used by ajax utilities.

// For the best performance, frequently used routes should be positioned first below.

// Main Routes = Routes used with the main version of CMD-Auth
// User Routes = Routes created by user
// Addon Routes = Routes used with addons of CMD-Auth i.e. Shop, B2B etc

// ********************* //
//      MAIN ROUTES      //
// ********************* //

$routes->add(
    '/sitemap.xml',
    SitemapController::class,
    'index',
    [
        AAAInit::class,
        HttpCaching::class => ['interval' => 86400],
    ]
);

$routes->add(
    '/',
    HomeController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class => ['interval' => 3600],
        CSP::class,
        SEO::class,
    ],
    [
        'changefreq' => 'daily',
        'priority' => 0.9,
    ]
);

$routes->add(
    '/login',
    LoginController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
        SEO::class,
    ],
    [
        'changefreq' => 'weekly',
        'priority' => 0.8,
    ]
);

$routes->add(
    '/google-oauth',
    Oauth2GoogleController::class,
    'google_oauth',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        CSP::class,
    ]
);

$routes->add(
    '/google-oauth/cb',
    Oauth2GoogleController::class,
    'google_oauth_callback',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        CSP::class,
    ]
);

$routes->add(
    '/login-mfa-step-2',
    LoginController::class,
    'mfa_step_2',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/user',
    UserController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['private' => true, 'interval' => 60],
        CSP::class,
    ]
);

$routes->add(
    '/signup',
    SignupController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
        SEO::class,
    ],
    [
        'changefreq' => 'weekly',
        'priority' => 0.8,
    ]
);

$routes->add(
    '/activate-user',
    ActivateUserController::class,
    'remote_request',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/'],
        CSP::class,
    ]
);

$routes->add(
    '/captcha',
    CaptchaController::class,
    'ajax_request_refresh_captcha',
    [
        Throttle::class,
        AAAInit::class,
        CSP::class,
    ]
);

$routes->add(
    '/csrf',
    CSRFController::class,
    'ajax_request_get_csrf',
    [
        Throttle::class,
        AAAInit::class,
        CSP::class,
    ]
);

$routes->add(
    '/change-password',
    ChangePasswordController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => ['private' => true, 'interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/change-primary-email',
    ChangePrimaryEmailController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => ['private' => true, 'interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/change-username',
    ChangeUsernameController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => ['private' => true, 'interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/confirm-password',
    ConfirmPasswordController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => ['private' => true, 'interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/change-user-details',
    ChangeUserDetailsController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => ['private' => true, 'interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/change-mfa',
    ChangeMfaController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => ['private' => true, 'interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/account-details',
    ShowDetailsController::class,
    'user_details',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => ['private' => true],
        CSP::class,
    ]
);

$routes->add(
    '/active-sessions-details',
    ShowDetailsController::class,
    'active_sessions_details',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'private' => true,
            'interval' => 60,
        ],
        CSP::class,
    ]
);

$routes->add(
    '/user-failed-logins',
    ShowDetailsController::class,
    'user_failed_logins',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => ['private' => true, 'interval' => 60],
        CSP::class,
    ]
);

$routes->add(
    '/privacy-and-cookies',
    StaticPagesController::class,
    'privacy_and_cookies',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/terms-and-conditions',
    StaticPagesController::class,
    'terms_and_conditions',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/support-cases',
    SupportCasesController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['private' => true],
        CSP::class,
    ]
);

$routes->add(
    '/support-cases/case/details/(:segment)',
    SupportCasesController::class,
    'case_details',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['private' => true],
        CSP::class,
    ]
);

$routes->add(
    '/support-cases/new',
    SupportCasesLifecycleController::class,
    'cases_new',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/contact'],
        HttpCaching::class,
        CSP::class,
    ]
);

$routes->add(
    '/other-options',
    OtherOptionsController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/delete-account',
    OtherOptionsController::class,
    'account_delete_confirm',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['private' => true],
        CSP::class,
    ]
);

$routes->add(
    '/contact',
    ContactController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/support-cases'],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
        SEO::class,
    ],
    [
        "changefreq" => "weekly",
        "priority" => 0.8,
    ]
);

$routes->add(
    '/articles',
    ArticleShowController::class,
    'articles_list',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class,
        CSP::class,
    ]
);

$routes->add(
    '/article/(:segment)',
    ArticleShowController::class,
    'article_details',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class,
        CSP::class,
        SEO::class,
    ]
);

$routes->add(
    '/article',
    ArticleShowController::class,
    'article_details_by_title',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class => ['interval' => 3600],
        CSP::class,
        SEO::class,
    ]
);

$routes->add(
    '/faqs',
    FaqShowController::class,
    'faqs_list',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class,
        CSP::class,
    ]
);

$routes->add(
    '/faq/(:segment)',
    FaqShowController::class,
    'faq_details',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class,
        CSP::class,
        SEO::class,
    ]
);

$routes->add(
    '/faq',
    FaqShowController::class,
    'faq_details_by_question',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class => ['interval' => 3600],
        CSP::class,
        SEO::class,
    ]
);

$routes->add(
    '/support-cases/overview',
    SupportCasesAdminsController::class,
    'cases_overview',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['private' => true, 'interval' => 60],
        CSP::class,
    ]
);

$routes->add(
    '/support-cases/search',
    SupportCasesAdminsController::class,
    'cases_search',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/support-cases/search/results',
    SupportCasesAdminsController::class,
    'remote_request_cases_search',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => ['private' => true, 'interval' => 30],
        CSP::class,
    ]
);

$routes->add(
    '/agreements/employee',
    AgreementsController::class,
    'list_agreements',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['private' => true],
        CSP::class,
    ]
);

$routes->add(
    '/agreements/employee/(:segment)',
    AgreementsController::class,
    'view_agreement',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['private' => true],
        CSP::class,
    ]
);

$routes->add(
    '/reset-password',
    ResetPasswordController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/change-password-on-reset',
    ChangePasswordOnResetController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        CSP::class,
    ]
);

$routes->add(
    '/activate-email',
    ActivateEmailController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/admin/article/new',
    ArticleLifecycleController::class,
    'admin_article_new',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/admin/article/modify/(:segment)',
    ArticleLifecycleController::class,
    'admin_article_modify',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        CSP::class,
    ]
);

$routes->add(
    '/admin/faq/new',
    FaqLifecycleController::class,
    'admin_faq_new',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/admin/faq/modify/(:segment)',
    FaqLifecycleController::class,
    'admin_faq_modify',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        CSP::class,
    ]
);

$routes->add(
    '/administration',
    StaticPagesController::class,
    'administration',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/admin/agreements',
    AgreementsController::class,
    'admin_list_agreements',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => ['private' => true],
        CSP::class,
    ]
);

$routes->add(
    '/admin/agreements/(:segment)',
    AgreementsController::class,
    'admin_view_agreement',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        HttpCaching::class => ['private' => true],
        CSP::class,
    ]
);

$routes->add(
    '/admin/agreement/new',
    AgreementLifecycleController::class,
    'admin_agreement_new',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
    ]
);

$routes->add(
    '/admin/agreement/modify/(:segment)',
    AgreementLifecycleController::class,
    'admin_agreement_modify',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        HttpCaching::class => ['private' => true],
        CSP::class,
    ]
);

$routes->add(
    '/admin/accounts/approve',
    ApproveAccountsController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        CSP::class,
    ]
);

$routes->add(
    '/admin/accounts/mark-verified',
    VerifyAccountsController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        CSP::class,
    ]
);

$routes->add(
    '/admin/account/history/search',
    AccountHistoryController::class,
    'account_history_search',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        CSP::class,
    ]
);

$routes->add(
    '/admin/account/history',
    AccountHistoryController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        CSP::class,
    ]
);

$routes->add(
    '/admin/employees',
    EmployeesAdministrationController::class,
    'employees_list',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        HttpCaching::class => ['private' => true],
        CSP::class,
    ]
);

$routes->add(
    '/admin/employee/(:segment)',
    EmployeesAdministrationController::class,
    'employee_modify',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        CSP::class,
    ]
);

$routes->add(
    '/rss.xml',
    RssFeedController::class,
    'index',
    [
        AAAInit::class,
        HttpCaching::class,
    ]
);

$routes->add(
    '/error',
    ErrorController::class,
    'general_errors',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        CSP::class,
    ]
);

// ********************* //
//      CLI ROUTES       //
// ********************* //
// Keep CLI Routes at the end

$routes->cli(
    '/cli/clear-cache',
    CLIController::class,
    'clear_cache'
);
