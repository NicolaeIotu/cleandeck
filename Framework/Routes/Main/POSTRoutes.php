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

use Framework\Controllers\Main\ActivateEmailController;
use Framework\Controllers\Main\AgreementLifecycleController;
use Framework\Controllers\Main\AgreementsController;
use Framework\Controllers\Main\ApproveAccountsController;
use Framework\Controllers\Main\ArticleLifecycleController;
use Framework\Controllers\Main\ChangeMfaController;
use Framework\Controllers\Main\ChangePasswordController;
use Framework\Controllers\Main\ChangePasswordOnResetController;
use Framework\Controllers\Main\ChangePrimaryEmailController;
use Framework\Controllers\Main\ChangeUserDetailsController;
use Framework\Controllers\Main\ChangeUsernameController;
use Framework\Controllers\Main\ConfirmPasswordController;
use Framework\Controllers\Main\ContactController;
use Framework\Controllers\Main\EmployeesAdministrationController;
use Framework\Controllers\Main\FaqLifecycleController;
use Framework\Controllers\Main\LoginController;
use Framework\Controllers\Main\LogoutController;
use Framework\Controllers\Main\OtherOptionsController;
use Framework\Controllers\Main\ResetPasswordController;
use Framework\Controllers\Main\SignupController;
use Framework\Controllers\Main\SupportCasesLifecycleController;
use Framework\Controllers\Main\SupportCasesRankController;
use Framework\Controllers\Main\SupportCasesReplyController;
use Framework\Controllers\Main\VerifyAccountsController;
use Framework\Libraries\Routes\RouteCollection;
use Framework\Middleware\Main\AAAInit;
use Framework\Middleware\Main\Admin;
use Framework\Middleware\Main\ApplicationStatusJWT;
use Framework\Middleware\Main\CaptchaEnd;
use Framework\Middleware\Main\CSRFEnd;
use Framework\Middleware\Main\HttpCaching;
use Framework\Middleware\Main\Throttle;
use Framework\Middleware\Main\UserDetails;

if (\defined('CLEANDECK_LIST_ROUTES')) {
    // RouteCollection class used by utility 'cleandeck-routes' (composer exec cleandeck-routes).
    $routes = new \Framework\Support\Utils\RouteCollection();
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
    '/login/request',
    LoginController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        CSRFEnd::class => ['/login'],
        CaptchaEnd::class => ['/login'],
    ]
);

$routes->add(
    '/login-mfa-step-2/request',
    LoginController::class,
    'mfa_step_2_remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        CSRFEnd::class => ['/'],
    ]
);

$routes->add(
    '/login-mfa-cancel/request',
    LoginController::class,
    'mfa_cancel_remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        CSRFEnd::class => ['/'],
    ]
);

$routes->add(
    '/logout',
    LogoutController::class,
    'remote_request_this',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-tags' => [
                '$UID',
            ],
        ],
    ]
);

$routes->add(
    '/logout/session/(:segment)',
    LogoutController::class,
    'remote_request_session',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/user', '/active-sessions-details',
            ],
        ],
    ]
);

$routes->add(
    '/logout-all-except-this',
    LogoutController::class,
    'remote_request_all_except_this',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/user', '/active-sessions-details',
            ],
        ],
    ]
);

$routes->add(
    '/logout-all',
    LogoutController::class,
    'remote_request_all',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-tags' => [
                '$UID',
            ],
        ],
    ]
);

$routes->add(
    '/signup/request',
    SignupController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        CSRFEnd::class => ['/signup'],
        CaptchaEnd::class => ['/signup'],
    ]
);

$routes->add(
    '/reset-password/request',
    ResetPasswordController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        CSRFEnd::class => ['/reset-password'],
        CaptchaEnd::class => ['/reset-password'],
    ]
);

$routes->add(
    '/change-password-on-reset/request',
    ChangePasswordOnResetController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        CSRFEnd::class => ['/'],
        CaptchaEnd::class => ['/'],
    ]
);

$routes->add(
    '/activate-email/request',
    ActivateEmailController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/user', '/account-details', '/change-user-details',
            ],
        ],
        CSRFEnd::class => ['/'],
        CaptchaEnd::class => ['/'],
    ]
);

$routes->add(
    '/change-password/request',
    ChangePasswordController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        CSRFEnd::class => ['/change-password'],
        CaptchaEnd::class => ['/change-password'],
    ]
);

$routes->add(
    '/change-primary-email/request',
    ChangePrimaryEmailController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        CSRFEnd::class => ['/change-primary-email'],
        CaptchaEnd::class => ['/change-primary-email'],
    ]
);

$routes->add(
    '/change-username/request',
    ChangeUsernameController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-tags' => [
                '$UID',
            ],
        ],
        CSRFEnd::class => ['/change-username'],
        CaptchaEnd::class => ['/change-username'],
    ]
);

$routes->add(
    '/confirm-password/request',
    ConfirmPasswordController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        CSRFEnd::class => ['/confirm-password'],
        CaptchaEnd::class => ['/confirm-password'],
    ]
);

$routes->add(
    '/change-user-details/request',
    ChangeUserDetailsController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/user', '/account-details', '/change-user-details',
            ],
        ],
        CSRFEnd::class => ['/change-user-details'],
        CaptchaEnd::class => ['/change-user-details'],
    ]
);

$routes->add(
    '/change-mfa/request',
    ChangeMfaController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/change-mfa', '/account-details',
            ],
        ],
        CSRFEnd::class => ['/change-mfa'],
        CaptchaEnd::class => ['/change-mfa'],
    ]
);

$routes->add(
    '/support-cases/new/request',
    SupportCasesLifecycleController::class,
    'remote_request_cases_new',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/user', '/support-cases',
            ],
        ],
        CSRFEnd::class => ['/support-cases/new'],
        CaptchaEnd::class => ['/support-cases/new'],
    ]
);

$routes->add(
    '/support-cases/case/close',
    SupportCasesLifecycleController::class,
    'remote_request_case_close',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/user',
                '/support-cases',
            ],
            'clear-private-tags' => [
                '/support-cases/case/details/$_POST:case_id',
            ],
        ],
        CSRFEnd::class => ['/'],
    ]
);

$routes->add(
    '/support-cases/case/reply/request',
    SupportCasesReplyController::class,
    'remote_request_case_reply',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/support-cases',
            ],
            'clear-private-tags' => [
                '/support-cases/case/details/$_POST:case_id',
            ],
        ],
        CSRFEnd::class => ['/'],
    ]
);

$routes->add(
    '/support-cases/case/rank-client',
    SupportCasesRankController::class,
    'ajax_request_case_rank_client',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-tags' => [
                '/support-cases/case/details/$_POST:case_id',
            ],
        ],
        CSRFEnd::class => ['/'],
    ]
);

$routes->add(
    '/support-cases/case/rank-support',
    SupportCasesRankController::class,
    'ajax_request_case_rank_support',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/'],
        HttpCaching::class => [
            'clear-private-tags' => [
                '/support-cases/case/details/$_POST:case_id',
            ],
        ],
        CSRFEnd::class => ['/'],
    ]
);

$routes->add(
    '/hibernate-account',
    OtherOptionsController::class,
    'remote_request_account_hibernate',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        CSRFEnd::class => ['/'],
    ]
);

$routes->add(
    '/confirm-delete-account',
    OtherOptionsController::class,
    'remote_request_account_delete_final',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        CSRFEnd::class => ['/'],
        CaptchaEnd::class => ['/'],
    ]
);

$routes->add(
    '/contact/request',
    ContactController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        CSRFEnd::class => ['/contact'],
        CaptchaEnd::class => ['/contact'],
    ]
);

$routes->add(
    '/admin/article/new',
    ArticleLifecycleController::class,
    'remote_request_admin_article_edit',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        CSRFEnd::class => ['/admin/article/new'],
        CaptchaEnd::class => ['/admin/article/new'],
    ]
);

$routes->add(
    '/admin/article/modify/(:segment)',
    ArticleLifecycleController::class,
    'remote_request_admin_article_modify',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        HttpCaching::class => [
            'clear-public-urls' => [
                '/articles',
            ],
            'clear-public-tags' => [
                '/article/$_POST:article_id',
            ],
        ],
        CSRFEnd::class => ['#REDIRECT_TO_GET#'],
        CaptchaEnd::class => ['#REDIRECT_TO_GET#'],
    ]
);

$routes->add(
    '/admin/article/delete/(:segment)',
    ArticleLifecycleController::class,
    'remote_request_admin_article_delete',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        HttpCaching::class => [
            'clear-public-urls' => [
                '/articles',
            ],
            'clear-public-tags' => [
                '/article/$_POST:article_id',
            ],
        ],
        CSRFEnd::class => ['/articles'],
        CaptchaEnd::class => ['/articles'],
    ]
);

$routes->add(
    '/admin/faq/new',
    FaqLifecycleController::class,
    'remote_request_admin_faq_edit',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        CSRFEnd::class => ['/admin/faq/new'],
        CaptchaEnd::class => ['/admin/faq/new'],
    ]
);

$routes->add(
    '/admin/faq/modify/(:segment)',
    FaqLifecycleController::class,
    'remote_request_admin_faq_modify',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        HttpCaching::class => [
            'clear-public-urls' => [
                '/faqs',
            ],
            'clear-public-tags' => [
                '/faq/$_POST:faq_id',
            ],
        ],
        CSRFEnd::class => ['#REDIRECT_TO_GET#'],
        CaptchaEnd::class => ['#REDIRECT_TO_GET#'],
    ]
);

$routes->add(
    '/admin/faq/delete/(:segment)',
    FaqLifecycleController::class,
    'remote_request_admin_faq_delete',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        HttpCaching::class => [
            'clear-public-urls' => [
                '/faqs',
            ],
            'clear-public-tags' => [
                '/faq/$_POST:faq_id',
            ],
        ],
        CSRFEnd::class => ['/faqs'],
        CaptchaEnd::class => ['/faqs'],
    ]
);

$routes->add(
    '/agreements/employee/(:segment)',
    AgreementsController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/agreements/employee',
            ],
            'clear-private-tags' => [
                '/agreements/employee/$_POST:agreement_id',
            ],
        ],
        CSRFEnd::class => ['#REDIRECT_TO_GET#'],
        CaptchaEnd::class => ['#REDIRECT_TO_GET#'],
    ]
);

$routes->add(
    '/admin/agreement/new',
    AgreementLifecycleController::class,
    'remote_request_admin_agreement_edit',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/admin/agreements',
            ],
        ],
        CSRFEnd::class => ['#REDIRECT_BACK#'],
        CaptchaEnd::class => ['#REDIRECT_BACK#'],
    ]
);

$routes->add(
    '/admin/agreement/modify/(:segment)',
    AgreementLifecycleController::class,
    'remote_request_admin_agreement_modify',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/admin/agreements',
            ],
            'clear-private-tags' => [
                '/agreements/employee/$_POST:agreement_id',
            ],
        ],
        CSRFEnd::class => ['#REDIRECT_TO_GET#'],
        CaptchaEnd::class => ['#REDIRECT_TO_GET#'],
    ]
);

$routes->add(
    '/admin/agreement/delete/(:segment)',
    AgreementLifecycleController::class,
    'remote_request_admin_agreement_delete',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/admin/agreements',
            ],
            'clear-private-tags' => [
                '/agreements/employee/$_POST:agreement_id',
            ],
        ],
        CSRFEnd::class => ['/admin/agreements'],
        CaptchaEnd::class => ['/admin/agreements'],
    ]
);

$routes->add(
    '/admin/accounts/approve/request',
    ApproveAccountsController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        CSRFEnd::class => ['/admin/accounts/approve'],
        CaptchaEnd::class => ['/admin/accounts/approve'],
    ]
);

$routes->add(
    '/admin/accounts/mark-verified/request',
    VerifyAccountsController::class,
    'remote_request',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [1000],
        CSRFEnd::class => ['/admin/accounts/mark-verified'],
        CaptchaEnd::class => ['/admin/accounts/mark-verified'],
    ]
);

$routes->add(
    '/admin/employee',
    EmployeesAdministrationController::class,
    'remote_request_employee_modify',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        Admin::class => [50000],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/admin/employees',
            ],
        ],
        CSRFEnd::class => ['#REDIRECT_BACK#'],
        CaptchaEnd::class => ['#REDIRECT_BACK#'],
    ]
);


// ********************* //
//      USER ROUTES      //
// ********************* //


// ********************* //
//      ADDON ROUTES     //
// ********************* //
