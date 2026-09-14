<?php
// ── Public ────────────────────────────────────────────────────────────────────
$router->get('/',        'HomeController@index');
$router->get('/about',   'HomeController@about');
$router->get('/contact', 'HomeController@contact');
$router->post('/contact','HomeController@sendContact', ['csrf']);

// ── Public job & company pages ────────────────────────────────────────────────
$router->get('/jobs',                   'JobController@index');
$router->get('/jobs/{slug}',            'JobController@show');
$router->get('/jobs/{slug}/apply',      'JobController@show');
$router->post('/jobs/{slug}/apply',     'ApplicationController@applyBySlug', ['auth','csrf']);
$router->get('/companies',         'CompanyController@index');
$router->get('/companies/{slug}',  'CompanyController@show');

// ── Auth (guest only) ─────────────────────────────────────────────────────────
$router->group(['middleware' => ['guest']], function ($r) {
    $r->get('/login',             'AuthController@showLogin');
    $r->post('/login',            'AuthController@login',          ['csrf']);
    $r->get('/register',          'AuthController@showRegister');
    $r->post('/register',         'AuthController@register',       ['csrf']);
    $r->get('/forgot-password',   'AuthController@showForgot');
    $r->post('/forgot-password',  'AuthController@sendReset',      ['csrf']);
    $r->get('/reset-password',    'AuthController@showReset');
    $r->post('/reset-password',   'AuthController@resetPassword',  ['csrf']);
});

$router->get('/verify-email', 'AuthController@verifyEmail');
$router->get('/logout',       'AuthController@logout', ['auth']);
$router->get('/auth/google', 'AuthController@redirectToGoogle');
$router->get('/auth/google/callback', 'AuthController@handleGoogleCallback');
$router->get('/file', 'FileController@serve');

// ── Account Settings (all roles) ──────────────────────────────────────────
$router->group(['prefix' => '/account', 'middleware' => ['auth']], function ($r) {
    $r->get('/settings',       'AccountController@settings');
    $r->post('/password',      'AccountController@changePassword',    ['csrf']);
    $r->post('/email',         'AccountController@changeEmail',       ['csrf']);
    $r->post('/notifications', 'AccountController@updateNotifications',['csrf']);
    $r->post('/delete',        'AccountController@deleteAccount',     ['csrf']);
});

// ── Job Seeker ────────────────────────────────────────────────────────────────
$router->group(['prefix' => '/seeker', 'middleware' => ['auth', 'role:seeker']], function ($r) {
    $r->get('/dashboard',           'SeekerController@dashboard');
    $r->get('/profile',             'SeekerController@profile');
    $r->post('/profile',            'SeekerController@updateProfile',   ['csrf']);
    $r->post('/profile/avatar',     'SeekerController@uploadAvatar',    ['csrf']);
    $r->post('/profile/resume',     'SeekerController@uploadResume',    ['csrf']);
    $r->post('/skills',             'SeekerController@addSkill',        ['csrf']);
    $r->post('/skills/delete',      'SeekerController@deleteSkill',     ['csrf']);
    $r->post('/experience',         'SeekerController@addExperience',   ['csrf']);
    $r->post('/experience/delete',  'SeekerController@deleteExperience',['csrf']);
    $r->post('/education',          'SeekerController@addEducation',    ['csrf']);
    $r->post('/education/delete',   'SeekerController@deleteEducation', ['csrf']);
    $r->get('/applications',        'ApplicationController@seekerIndex');
    $r->post('/apply/{jobId}',      'ApplicationController@apply',      ['csrf']);
    $r->post('/withdraw/{appId}',   'ApplicationController@withdraw',   ['csrf']);
    $r->post('/interviews/{id}/confirm', 'InterviewController@confirm', ['csrf']);
    $r->post('/interviews/{id}/decline', 'InterviewController@decline', ['csrf']);
    $r->get('/saved-jobs',          'SeekerController@savedJobs');
    $r->post('/save-job/{jobId}',   'SeekerController@saveJob',         ['csrf']);
    $r->post('/unsave-job/{jobId}', 'SeekerController@unsaveJob',       ['csrf']);
    $r->get('/alerts',              'SeekerController@alerts');
    $r->post('/alerts',             'SeekerController@createAlert',     ['csrf']);
    $r->post('/alerts/delete',      'SeekerController@deleteAlert',     ['csrf']);
    $r->get('/notifications',       'SeekerController@notifications');
    $r->post('/reviews',            'SeekerController@submitReview',    ['csrf']);
});

// ── Employer ──────────────────────────────────────────────────────────────────
$router->group(['prefix' => '/employer', 'middleware' => ['auth', 'role:employer']], function ($r) {
    $r->get('/dashboard',               'EmployerController@dashboard');
    $r->get('/profile',                 'EmployerController@profile');
    $r->post('/profile',                'EmployerController@updateProfile',      ['csrf']);
    $r->post('/profile/logo',           'EmployerController@uploadLogo',         ['csrf']);
    $r->post('/profile/verify',         'EmployerController@submitVerification', ['csrf']);
    $r->get('/jobs',                    'JobController@employerIndex');
    $r->get('/jobs/create',             'JobController@create');
    $r->post('/jobs',                   'JobController@store',                   ['csrf']);
    $r->get('/jobs/{id}/edit',          'JobController@edit');
    $r->post('/jobs/{id}/update',       'JobController@update',                  ['csrf']);
    $r->post('/jobs/{id}/close',        'JobController@close',                   ['csrf']);
    $r->post('/jobs/{id}/repost',       'JobController@repost',                  ['csrf']);
    $r->post('/jobs/{id}/delete',       'JobController@destroy',                 ['csrf']);
    $r->get('/jobs/{id}/analytics',     'JobController@analytics');
    $r->get('/jobs/{id}/applicants',    'ApplicationController@employerIndex');
    $r->get('/applications/{id}',       'ApplicationController@show');
    $r->post('/applications/{id}/status','ApplicationController@updateStatus',   ['csrf']);
    $r->post('/applications/{id}/notes', 'ApplicationController@updateNotes',    ['csrf']);
    $r->get('/interviews',              'InterviewController@index');
    $r->post('/interviews',             'InterviewController@schedule',          ['csrf']);
    $r->post('/interviews/{id}/cancel', 'InterviewController@cancel',            ['csrf']);
    $r->get('/candidates',              'CandidateController@search');
    $r->get('/candidates/{id}',         'CandidateController@show');
    $r->get('/notifications',           'EmployerController@notifications');
    $r->get('/reviews',                 'EmployerController@reviews');
});

// ── Admin ─────────────────────────────────────────────────────────────────────
$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'role:admin']], function ($r) {
    $r->get('/dashboard',                      'AdminController@dashboard');
    $r->get('/users',                          'AdminController@users');
    $r->get('/users/{id}',                     'AdminController@viewUser');
    $r->post('/users/{id}/toggle',             'AdminController@toggleUser',           ['csrf']);
    $r->post('/users/{id}/delete',             'AdminController@deleteUser',           ['csrf']);
    $r->get('/verifications',                  'AdminController@verifications');
    $r->post('/verifications/{id}/approve',    'AdminController@approveVerification',  ['csrf']);
    $r->post('/verifications/{id}/reject',     'AdminController@rejectVerification',   ['csrf']);
    $r->get('/jobs',                           'AdminController@jobs');
    $r->post('/jobs/{id}/remove',              'AdminController@removeJob',            ['csrf']);
    $r->get('/reports',                        'AdminController@reports');
    $r->get('/logs',                           'AdminController@logs');
    $r->get('/reviews',                        'AdminController@reviews');
    $r->post('/reviews/{id}/hide',             'AdminController@hideReview',           ['csrf']);
    $r->post('/reviews/{id}/unhide',           'AdminController@unhideReview',         ['csrf']);
    $r->post('/reviews/{id}/delete',           'AdminController@deleteReview',         ['csrf']);
});

// ── Job Alert Unsubscribe (public — no login needed) ─────────────────────────────
$router->get('/alerts/unsubscribe', 'SeekerController@unsubscribeAlert');

// ── Resume PDF Builder ────────────────────────────────────────────────────────
$router->get('/seeker/resume/preview',  'ResumePdfController@preview',  ['auth', 'role:seeker']);
$router->get('/seeker/resume/download', 'ResumePdfController@download', ['auth', 'role:seeker']);

// ── Admin CSV Export ──────────────────────────────────────────────────────────
$router->get('/admin/export/users',        'AdminController@exportUsers',       ['auth', 'role:admin']);
$router->get('/admin/export/jobs',         'AdminController@exportJobs',        ['auth', 'role:admin']);
$router->get('/admin/export/applications', 'AdminController@exportApplications',['auth', 'role:admin']);

// ── API (AJAX) Routes ─────────────────────────────────────────────────────────
$router->group(['prefix' => '/api'], function ($r) {
    $r->get('/jobs/search',              'ApiController@jobSearch');
    $r->get('/jobs/suggest',             'JobController@suggest');
    $r->get('/jobs/{id}',                'ApiController@jobDetail');
    $r->get('/notifications/count',      'ApiController@unreadCount',          ['auth']);
    $r->post('/notifications/mark-read', 'ApiController@markNotificationsRead', ['auth', 'csrf']);
    $r->post('/jobs/save',               'ApiController@saveJob',               ['auth', 'csrf']);
    $r->post('/jobs/unsave',             'ApiController@unsaveJob',             ['auth', 'csrf']);
});

// ── Messaging System ──────────────────────────────────────────────────────────
$router->get('/messages',                  'MessageController@inbox',       ['auth']);
$router->post('/messages',                 'MessageController@start',        ['auth', 'csrf']);
$router->get('/messages/{id}',             'MessageController@show',         ['auth']);
$router->post('/messages/{id}/send',       'MessageController@send',         ['auth', 'csrf']);
$router->get('/messages/{id}/poll',        'MessageController@poll',         ['auth']);
$router->get('/api/messages/unread',       'MessageController@unreadCount',  ['auth']);
