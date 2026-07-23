<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$guard = static function (string $permission): array {
    return [
        'filter' => [
            'auth',
            'permission:' . $permission,
        ],
    ];
};

/* Public website */
$routes->get('/sitemap.xml', 'SeoController::sitemap');
$routes->get('/robots.txt', 'SeoController::robots');
$routes->get('/', 'PublicController::index');
$routes->get('/profil', 'PublicController::profile');
$routes->get('/program', 'PublicController::programs');
$routes->get('/program/(:segment)', 'PublicController::programDetail/$1');
$routes->get('/pengurus', 'PublicController::officials');
$routes->get('/kegiatan', 'PublicController::activities');
$routes->get('/kegiatan/(:num)', 'PublicController::activityDetail/$1');
$routes->get('/kontak', 'PublicContactController::index');
$routes->post('/kontak/kirim', 'PublicContactController::submit');

/* Authentication */
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::attemptLogin');
$routes->get('/logout', 'AuthController::logout');

/* Dashboard */
$routes->get(
    '/dashboard',
    'DashboardController::index',
    $guard('dashboard.view')
);

/* User account management */
$routes->get('/users', 'UserManagementController::index', $guard('users.view'));
$routes->get('/users/create', 'UserManagementController::create', $guard('users.create'));
$routes->post('/users/store', 'UserManagementController::store', $guard('users.create'));
$routes->get('/users/edit/(:num)', 'UserManagementController::edit/$1', $guard('users.update'));
$routes->post('/users/update/(:num)', 'UserManagementController::update/$1', $guard('users.update'));
$routes->post('/users/(:num)/status', 'UserManagementController::updateStatus/$1', $guard('users.status'));
$routes->post('/users/(:num)/reset-password', 'UserManagementController::resetPassword/$1', $guard('users.reset_password'));

/* Members */
$routes->get('/members', 'MemberController::index', $guard('members.view'));
$routes->get('/members/create', 'MemberController::create', $guard('members.create'));
$routes->post('/members/store', 'MemberController::store', $guard('members.create'));
$routes->get('/members/edit/(:num)', 'MemberController::edit/$1', $guard('members.update'));
$routes->post('/members/update/(:num)', 'MemberController::update/$1', $guard('members.update'));
$routes->post('/members/delete/(:num)', 'MemberController::delete/$1', $guard('members.delete'));

/* Organizational structure */
$routes->get('/structures', 'StructureController::index', $guard('structures.view'));
$routes->get('/structures/create', 'StructureController::create', $guard('structures.create'));
$routes->post('/structures/store', 'StructureController::store', $guard('structures.create'));
$routes->get('/structures/edit/(:num)', 'StructureController::edit/$1', $guard('structures.update'));
$routes->post('/structures/update/(:num)', 'StructureController::update/$1', $guard('structures.update'));
$routes->post('/structures/delete/(:num)', 'StructureController::delete/$1', $guard('structures.delete'));

/* Meetings */
$routes->get('/meetings', 'MeetingController::index', $guard('meetings.view'));
$routes->get('/meetings/create', 'MeetingController::create', $guard('meetings.create'));
$routes->post('/meetings/store', 'MeetingController::store', $guard('meetings.create'));
$routes->get('/meetings/edit/(:num)', 'MeetingController::edit/$1', $guard('meetings.update'));
$routes->post('/meetings/update/(:num)', 'MeetingController::update/$1', $guard('meetings.update'));
$routes->post('/meetings/delete/(:num)', 'MeetingController::delete/$1', $guard('meetings.delete'));

/* Attendance */
$routes->get('/attendances', 'AttendanceController::index', $guard('attendances.view'));
$routes->get('/attendances/create', 'AttendanceController::create', $guard('attendances.create'));
$routes->post('/attendances/store', 'AttendanceController::store', $guard('attendances.create'));
$routes->get('/attendances/edit/(:num)', 'AttendanceController::edit/$1', $guard('attendances.update'));
$routes->post('/attendances/update/(:num)', 'AttendanceController::update/$1', $guard('attendances.update'));
$routes->post('/attendances/delete/(:num)', 'AttendanceController::delete/$1', $guard('attendances.delete'));
$routes->get('/attendances/recap/(:num)', 'AttendanceController::recap/$1', $guard('attendances.recap'));
$routes->get('/attendances/recap/(:num)/print', 'AttendanceController::recapPrint/$1', $guard('attendances.recap'));
$routes->get('/attendances/bulk/(:num)', 'AttendanceController::bulk/$1', $guard('attendances.bulk'));
$routes->post('/attendances/bulk-save/(:num)', 'AttendanceController::bulkSave/$1', $guard('attendances.bulk'));

/* Cash */
$routes->get('/cash', 'CashTransactionController::index', $guard('cash.view'));
$routes->get('/cash/create', 'CashTransactionController::create', $guard('cash.create'));
$routes->post('/cash/store', 'CashTransactionController::store', $guard('cash.create'));
$routes->get('/cash/edit/(:num)', 'CashTransactionController::edit/$1', $guard('cash.update'));
$routes->post('/cash/update/(:num)', 'CashTransactionController::update/$1', $guard('cash.update'));
$routes->post('/cash/delete/(:num)', 'CashTransactionController::delete/$1', $guard('cash.delete'));

/* Activities and publication workflow */
$routes->get('/activities', 'ActivityController::index', $guard('activities.view'));
$routes->get('/activities/quality', 'ActivityController::qualityAudit', $guard('activities.view'));
$routes->get('/activities/create', 'ActivityController::create', $guard('activities.create'));
$routes->post('/activities/store', 'ActivityController::store', $guard('activities.create'));
$routes->get('/activities/edit/(:num)', 'ActivityController::edit/$1', $guard('activities.update'));
$routes->post('/activities/update/(:num)', 'ActivityController::update/$1', $guard('activities.update'));
$routes->post('/activities/delete/(:num)', 'ActivityController::delete/$1', $guard('activities.delete'));
$routes->post('/activities/submit-review/(:num)', 'ActivityController::submitReview/$1', $guard('activities.submit_review'));
$routes->post('/activities/publish/(:num)', 'ActivityController::publish/$1', $guard('activities.publish'));
$routes->post('/activities/draft/(:num)', 'ActivityController::draft/$1', $guard('activities.return_to_draft'));
$routes->post('/activities/archive/(:num)', 'ActivityController::archive/$1', $guard('activities.archive'));

/* Activity gallery */
$routes->get('/activities/gallery/(:num)', 'ActivityGalleryController::index/$1', $guard('activities.gallery.view'));
$routes->post('/activities/gallery/(:num)/upload', 'ActivityGalleryController::upload/$1', $guard('activities.gallery.manage'));
$routes->post('/activities/gallery/(:num)/image/(:num)/update', 'ActivityGalleryController::update/$1/$2', $guard('activities.gallery.manage'));
$routes->post('/activities/gallery/(:num)/image/(:num)/cover', 'ActivityGalleryController::setCover/$1/$2', $guard('activities.gallery.manage'));
$routes->post('/activities/gallery/(:num)/image/(:num)/delete', 'ActivityGalleryController::delete/$1/$2', $guard('activities.gallery.manage'));

/* Incoming messages */
$routes->get('/messages', 'ContactMessageController::index', $guard('messages.view'));
$routes->get('/messages/(:num)', 'ContactMessageController::show/$1', $guard('messages.view'));
$routes->post('/messages/(:num)/status', 'ContactMessageController::updateStatus/$1', $guard('messages.manage'));

/* Reports */
$routes->get('/reports', 'ReportController::index', $guard('reports.view'));
$routes->get('/reports/members', 'ReportController::members', $guard('reports.members'));
$routes->get('/reports/cash', 'ReportController::cash', $guard('reports.cash'));
$routes->get('/reports/meetings', 'ReportController::meetings', $guard('reports.meetings'));

/* Import and export */
$routes->get('/exports/members', 'ExportController::members', $guard('members.export'));
$routes->get('/exports/cash', 'ExportController::cash', $guard('cash.export'));
$routes->get('/imports/members', 'ImportController::membersForm', $guard('members.import'));
$routes->get('/imports/members/template', 'ImportController::membersTemplate', $guard('members.import'));
$routes->post('/imports/members', 'ImportController::membersImport', $guard('members.import'));

/* Structured public website CMS */
$routes->get('/website/pages', 'PublicPageController::index', $guard('website.pages.view'));
$routes->get('/website/pages/edit/(:segment)', 'PublicPageController::edit/$1', $guard('website.pages.update'));
$routes->post('/website/pages/update/(:segment)', 'PublicPageController::update/$1', $guard('website.pages.update'));
$routes->post('/website/pages/publish/(:segment)', 'PublicPageController::publish/$1', $guard('website.pages.publish'));
$routes->post('/website/pages/restore/(:segment)', 'PublicPageController::restore/$1', $guard('website.pages.update'));
$routes->get('/website/pages/preview/(:segment)', 'PublicPageController::preview/$1', $guard('website.pages.preview'));

/* Website Navigation Manager */
$routes->get('/website/navigation', 'WebsiteNavigationController::index', $guard('website.navigation.view'));
$routes->get('/website/navigation/edit/(:segment)', 'WebsiteNavigationController::edit/$1', $guard('website.navigation.update'));
$routes->post('/website/navigation/update/(:segment)', 'WebsiteNavigationController::update/$1', $guard('website.navigation.update'));
$routes->post('/website/navigation/publish/(:segment)', 'WebsiteNavigationController::publish/$1', $guard('website.navigation.publish'));
$routes->post('/website/navigation/restore/(:segment)', 'WebsiteNavigationController::restore/$1', $guard('website.navigation.update'));
$routes->get('/website/navigation/preview/(:segment)', 'WebsiteNavigationController::preview/$1', $guard('website.navigation.preview'));

/* SEO and sitemap center */
$routes->get('/website/seo', 'SeoController::index', $guard('website.seo.view'));

/* Social publication and Canva workflow */
$routes->get('/publications', 'SocialPublicationController::index', $guard('publications.view'));
$routes->get('/publications/guide', 'SocialPublicationController::guide', $guard('publications.view'));
$routes->get('/publications/calendar', 'SocialPublicationController::calendar', $guard('publications.view'));
$routes->get('/publications/create', 'SocialPublicationController::create', $guard('publications.create'));
$routes->get('/publications/create/activity/(:num)', 'SocialPublicationController::createFromActivity/$1', $guard('publications.create'));
$routes->post('/publications/store', 'SocialPublicationController::store', $guard('publications.create'));
$routes->get('/publications/analytics', 'SocialPublicationController::analytics', $guard('publications.metrics.view'));
$routes->get('/publications/analytics/export', 'SocialPublicationController::exportAnalytics', $guard('publications.metrics.export'));
$routes->get('/publications/audit', 'SocialPublicationController::audit', $guard('publications.audit.view'));
$routes->get('/publications/deadlines', 'SocialPublicationController::deadlines', $guard('publications.deadlines.view'));
$routes->get('/publications/recommendations', 'SocialPublicationController::recommendations', $guard('publications.recommendations.view'));
$routes->post('/publications/(:num)/metrics', 'SocialPublicationController::storeMetrics/$1', $guard('publications.metrics.manage'));
$routes->post('/publications/(:num)/metrics/(:num)/delete', 'SocialPublicationController::deleteMetric/$1/$2', $guard('publications.metrics.manage'));
$routes->get('/publications/(:num)', 'SocialPublicationController::show/$1', $guard('publications.view'));
$routes->get('/publications/edit/(:num)', 'SocialPublicationController::edit/$1', $guard('publications.update'));
$routes->post('/publications/update/(:num)', 'SocialPublicationController::update/$1', $guard('publications.update'));
$routes->post('/publications/status/(:num)', 'SocialPublicationController::changeStatus/$1', $guard('publications.workflow'));
$routes->post('/publications/(:num)/assets', 'SocialPublicationController::uploadAssets/$1', $guard('publications.assets'));
$routes->post('/publications/(:num)/assets/(:num)/delete', 'SocialPublicationController::deleteAsset/$1/$2', $guard('publications.assets'));

/* AI Content Studio */
$routes->get('/content-studio', 'ContentStudioController::index', $guard('content_studio.view'));
$routes->get('/content-studio/create', 'ContentStudioController::create', $guard('content_studio.create'));
$routes->post('/content-studio/store', 'ContentStudioController::store', $guard('content_studio.create'));
$routes->get('/content-studio/show/(:num)', 'ContentStudioController::show/$1', $guard('content_studio.view'));
$routes->post('/content-studio/generate/(:num)', 'ContentStudioController::generate/$1', $guard('content_studio.update'));
$routes->post('/content-studio/update-text/(:num)', 'ContentStudioController::updateText/$1', $guard('content_studio.update'));
$routes->post('/content-studio/delete/(:num)', 'ContentStudioController::delete/$1', $guard('content_studio.delete'));
$routes->post('/content-studio/render-feed/(:num)', 'ContentStudioController::renderFeed/$1', $guard('content_studio.update'));

/* Programs */
$routes->get('/programs', 'ProgramController::index', $guard('programs.view'));
$routes->get('/programs/create', 'ProgramController::create', $guard('programs.create'));
$routes->post('/programs/store', 'ProgramController::store', $guard('programs.create'));
$routes->get('/programs/edit/(:num)', 'ProgramController::edit/$1', $guard('programs.update'));
$routes->post('/programs/update/(:num)', 'ProgramController::update/$1', $guard('programs.update'));
$routes->post('/programs/publish/(:num)', 'ProgramController::publish/$1', $guard('programs.publish'));
$routes->post('/programs/archive/(:num)', 'ProgramController::archive/$1', $guard('programs.archive'));

/* Dynamic website settings */
$routes->get('/settings/website', 'SiteSettingController::index', $guard('settings.website.manage'));
$routes->post('/settings/website/update', 'SiteSettingController::update', $guard('settings.website.manage'));
