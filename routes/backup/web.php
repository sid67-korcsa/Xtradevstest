<?php

/**
 * @example web
 */

use Illuminate\Support\Facades\Route;

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmailWithActivityCheck')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');


//Invitation guest routes
Route::group(['namespace'=>'Invitation','prefix' => 'invite','middleware' => 'guest'], function () {
    Route::get('/{token}', 'InvitationController@setPassword')->name('setPassword');
    Route::post('/', 'InvitationController@savePassword')->name('savePassword');
});

Route::group(['middleware' => 'auth'], function () {

    //Under development pages
    Route::group(['namespace' => 'UnderDevelopment', 'prefix' => 'under_development'], function () {
        Route::get('/', 'UnderDevelopmentController@index')->name('underDevelopment');
        Route::get('/booking_car', 'UnderDevelopmentController@index')->name('bookingCar');
        Route::get('/booking_meeting_room', 'UnderDevelopmentController@index')->name('bookingMeetingRoom');
        Route::get('/knowledge_base', 'UnderDevelopmentController@index')->name('knowledgeBase');
        Route::get('/task_manager', 'UnderDevelopmentController@index')->name('taskManager');
        Route::get('/inside_forum', 'UnderDevelopmentController@index')->name('insideForum');
        Route::get('/leave_planning', 'UnderDevelopmentController@index')->name('leavePlanning');
    });

    //FullCalendar
    Route::get('/calendar/getFullCalendarEvents','Dashboard\DashboardController@getFullCalendarEvents')->name('getFullCalendarEvents');

    //Profile
    Route::get('/profile/leaves','Profile\ProfileController@index')->middleware('permission:list_own_leave_request')->name('indexOwnLeaves');
    Route::get('/profile','Profile\ProfileController@edit')->name('editProfile');
    Route::post('/profile','Profile\ProfileController@update')->name('updateProfile');
    Route::post('/profile/leave_request_notification', 'Profile\ProfileController@setNotificationUserSetting')->name('setNotificationUserSetting');
    Route::post('/read_notifications', 'Profile\NotificationController@readAllNotifications')->name('readAllNotifications');

    //Customers
    Route::group(['namespace'=>'Customers','prefix' => 'customer'], function () {
        Route::get('/', 'CustomerController@index')->middleware('permission:list_customers')->name('indexCustomer');
        Route::get('/new', 'CustomerController@new')->middleware('permission:create_customers')->name('newCustomer');
        Route::post('/new', 'CustomerController@insert')->middleware('permission:create_customers');
        Route::get('/edit/{id_customer}', 'CustomerController@edit')->middleware('permission:update_customers')->name('editCustomer');
        Route::post('/edit/{id_customer}', 'CustomerController@update')->middleware('permission:update_customers');
        Route::get('/delete/{id_customer}', 'CustomerController@delete')->middleware('permission:delete_customers')->name('deleteCustomer');
        Route::post('/create-or-update-from-email-signature', 'CustomerController@createOrUpdateFromEmailSignature')->middleware('permission:create_customers')->name('createOrUpdateFromEmailSignature');
        Route::get('/premises/edit/{id_premise}', 'CustomerController@getPremiseById')->middleware('permission:list_customer_premises')->name('getPremise');
        Route::get('/get-contacts-by-customer-id/{id_customer}', 'CustomerController@getContactsByCustomerId')->middleware('permission:list_contact')->name('getContactsByCustomerId');
        Route::get('/contact/edit/{id_contact}', 'CustomerController@getContactById')->middleware('permission:update_contact')->name('getContact');
        //Route::get('/contact/customer-card/edit/{id_contact}', 'CustomerCardController@customerCard')->middleware('permission:update_contact')->name('getCustomerCard');
        Route::get('/premises/delete/{id_premise}', 'CustomerController@deletePremiseById')->middleware('permission:delete_customer_premises')->name('deletePremise');
        Route::get('/contact/delete/{id_contact}', 'CustomerController@deleteContactById')->middleware('permission:delete_contact')->name('deleteContact');
        Route::post('/contact/generate-qr', 'CustomerController@generateQRCode')->middleware('permission:create_contact')->name('generateQRCode');
        //list_own_firm
        Route::get('/firm', 'CustomerFirmController@index')->middleware('permission:list_own_firm')->name('indexCustomerFirm');
        Route::get('/firm/new', 'CustomerFirmController@new')->middleware('permission:create_own_firm')->name('newCustomerFirm');
        Route::post('/firm/new', 'CustomerFirmController@insert')->middleware('permission:create_own_firm');
        Route::get('/firm/edit/{id_customer}', 'CustomerFirmController@edit')->middleware('permission:update_own_firm')->name('editCustomerFirm');
        Route::post('/firm/edit/{id_customer}', 'CustomerFirmController@update')->middleware('permission:update_own_firm');
        Route::get('/firm/delete/{id_customer}', 'CustomerFirmController@delete')->middleware('permission:delete_own_firm')->name('deleteCustomerFirm');
        //list_contact
        Route::get('/contact', 'CustomerContactController@index')->middleware('permission:list_contact')->name('indexCustomerContact');
        Route::get('/contact/new', 'CustomerContactController@new')->middleware('permission:create_contact')->name('newCustomerContact');
        Route::post('/contact/new', 'CustomerContactController@insert')->middleware('permission:create_contact');
        Route::get('/contact/present/edit/{id_contact}', 'CustomerContactController@edit')->middleware('permission:update_contact')->name('editPresentCustomerContact');
        Route::post('/contact/present/edit/{id_contact}', 'CustomerContactController@update')->middleware('permission:update_contact');
        Route::get('/contact/present/carton/edit/{id_contact}', 'CustomerContactController@editCarton')->middleware('permission:update_contact')->name('editCartonContact');
        Route::post('/contact/present/carton/edit/{id_contact}', 'CustomerContactController@updateCarton')->middleware('permission:update_contact');
        Route::get('/contact/get-contact', 'CustomerContactController@getContact')->middleware('permission:list_contact')->name('indexCustomerListContact');

        Route::get('/import', 'CustomerController@uploadForm')->middleware('permission:import_customer')->name('importCustomer');
        Route::post('/import', 'CustomerController@uploadFile')->middleware('permission:import_customer')->name('showImportCustomer');
        Route::post('/import/execute','CustomerController@import')->middleware('permission:import_customer')->name('executeImportCustomer');
        Route::get('/download_customer_example', 'CustomerController@downloadExampleCustomers')->name('downloadExampleCustomers');

        Route::get('/{id_customer}/datasheet/{id_task_type?}', 'CustomerDatasheetController@index')->middleware('permission:list_customers')->name('indexCustomerDatasheet');
        Route::post('/send-task-simple-notification', 'CustomerDatasheetController@sendTaskSimpleNotification')->middleware('permission:list_customers')->name('sendTaskSimpleNotification');
        Route::post('/send-task-complex-notification', 'CustomerDatasheetController@sendTaskComplexNotification')->middleware('permission:list_customers')->name('sendTaskComplexNotification');
    });

    //Tasks
    Route::group(['namespace'=>'Task','prefix' => 'task', 'middleware' => ['can:use-simple-task', 'can:use-complex-task', 'can:use-task-category', 'can:use-task-status']], function () {
        //simple task
        Route::get('/st','TaskSimpleController@index')->middleware('permission:list_task_simple')->name('indexSimpleTask');
        Route::get('/st/detail','TaskSimpleController@indexDetail')->middleware('permission:list_task_simple')->name('indexDetailSimpleTask');
        Route::get('/st/new', 'TaskSimpleController@new')->middleware('permission:create_task_simple')->name('newSimpleTask');
        Route::get('/st/show/{id_task}', 'TaskSimpleController@show')->middleware('permission:view-simple-task')->name('showTaskSimple');
        Route::post('/st/new', 'TaskSimpleController@insert')->middleware('permission:create_task_simple')->name('newPostSimpleTask');
        Route::get('/st/edit/{id_task}', 'TaskSimpleController@edit')->middleware('permission:update_task_simple')->name('updateSimpleTask');
        Route::get('/st/detail/edit/{id_task}', 'TaskSimpleController@edit')->middleware('permission:update_task_simple')->name('updateSimpleTaskDetail');
        Route::get('/st/closed/edit/{id_task}', 'TaskSimpleController@editClosed')->middleware('permission:update_task_simple')->name('updateSimpleTaskClosed');
        Route::post('/st/edit/{id_task}', 'TaskSimpleController@update')->middleware('permission:update_task_simple');
        Route::post('/st/detail/edit/{id_task}', 'TaskSimpleController@update')->middleware('permission:update_task_simple');
        Route::get('/st/delete/{id_task}', 'TaskSimpleController@delete')->middleware('permission:delete_task_simple')->name('deleteSimpleTask');
        Route::post('/st/bulk-disable-items', 'TaskSimpleController@bulkActionDisableItems')->middleware('permission:update_task_simple')->name('bulkActionDisableTask');
        Route::post('/st/post-term-date', 'TaskSimpleController@postTermDate')->middleware('permission:update_task_simple')->name('postTermDateRoute');
        Route::get('/st/resolved', 'TaskSimpleController@resolved')->middleware('permission:list_task_simple')->name('listResolvedTask');
        Route::get('/st/prioritization', 'TaskSimpleController@prioritizationPage')->middleware('permission:list_task_simple')->name('prioritizationPage');
        Route::post('/st/change-term-and-priority-of-task', 'TaskSimpleController@changeTermAndPriorityOfTask')->middleware('permission:update_task_simple')->name('changeTermAndPriorityOfTask');
        Route::post('/st/get-task-simple-datas', 'TaskSimpleController@getTaskSimpleDatas')->middleware('permission:list_task_simple')->name('getTaskSimpleDatas');
        Route::post('/st/update-task-simple-datas', 'TaskSimpleController@updateTaskSimpleDatas')->middleware('permission:update_task_simple')->name('updateTaskSimpleDatas');
        Route::post('/st/go-to-make-report', 'TaskSimpleController@goToMakeReport')->middleware('permission:generate_text')->name('goToMakeReport');
        Route::post('/st/set-task-reminder', 'TaskSimpleController@setTaskReminder')->middleware('permission:update_task_simple')->name('setTaskSimpleReminder');
        //complex task
        Route::get('/ct','TaskComplexController@index')->middleware('permission:list_task_complex')->name('indexComplexTask');
        Route::get('/ct/new', 'TaskComplexController@new')->middleware('permission:create_task_complex')->name('newComplexTask');
        Route::get('/ct/newModal', 'TaskComplexController@newModal')->middleware('permission:create_task_complex')->name('newComplexTaskModal');
        Route::post('/ct/newModal', 'TaskComplexController@newModalUpdate')->middleware('permission:create_task_complex');
        Route::post('/ct/new', 'TaskComplexController@insert')->middleware('permission:create_task_complex')->name('newComplexTaskPost');
        Route::get('/ct/edit/{id_task}', 'TaskComplexController@edit')->middleware('permission:update_task_complex')->name('updateComplexTask');
        Route::post('/ct/edit/{id_task}', 'TaskComplexController@update')->middleware('permission:update_task_complex');
        Route::get('/ct/delete/{id_task}', 'TaskComplexController@delete')->middleware('permission:delete_task_complex')->name('deleteComplexTask');
        Route::get('/ct/report-ready/{id_task}', 'TaskComplexController@reportReady')->middleware('permission:update_task_complex')->name('reportReadyComplexTask');
        Route::get('/ct/report-not-ready/{id_task}', 'TaskComplexController@reportNotReady')->middleware('permission:update_task_complex')->name('reportNotReadyComplexTask');
        Route::get('/ct/close/{id_task}', 'TaskComplexController@close')->middleware('permission:update_task_complex')->name('closeComplexTask');
        Route::get('/ct/close-and-reopen/{id_task}', 'TaskComplexController@closeAndReopen')->middleware('permission:update_task_complex')->name('closeAndReopenComplexTask');
        Route::get('/ct/resolved', 'TaskComplexController@resolved')->middleware('permission:list_task_complex')->name('listResolvedTaskComplex');
        Route::get('/ct/show/{id_task}', 'TaskComplexController@show')->middleware('permission:view-complex-task')->name('showTaskComplex');
        Route::get('/ct/autocomplete', 'TaskComplexController@getAutoCompleteProject')->middleware('permission:update_task_complex');
        Route::post('/ct/autocomplete', 'TaskComplexController@postAutoCompleteProject')->middleware('permission:update_task_complex');
        Route::post('/ct/get-related-task-projects', 'TaskComplexController@getRelatedTaskProjects')->middleware('permission:list_task_simple')->name('getRelatedTaskProjects');
        Route::post('/ct/set-task-reminder-responsible', 'TaskComplexController@setTaskComplexResponsibleReminder')->middleware('permission:update_task_complex')->name('setTaskComplexResponsibleReminder');
        //Files
        Route::any('/upload', 'TaskComplexController@upload')->name('taskUpload');
        Route::post('/preview', 'TaskComplexController@getPreview')->name('getFilePreview');
        Route::post('/file/delete', 'TaskComplexController@fileDelete')->name('taskFileDelete');
        //task category
        Route::get('/tc','TaskCategoryController@index')->middleware('permission:list_task_category')->name('indexTaskCategory');
        Route::get('/tc/new', 'TaskCategoryController@new')->middleware('permission:create_task_category')->name('newTaskCategory');
        Route::post('/tc/new', 'TaskCategoryController@insert')->middleware('permission:create_task_category');
        Route::get('/tc/edit/{id_task_category}', 'TaskCategoryController@edit')->middleware('permission:update_task_category')->name('updateTaskCategory');
        Route::post('/tc/edit/{id_task_category}', 'TaskCategoryController@update')->middleware('permission:update_task_category');
        Route::get('/tc/delete/{id_task_category}', 'TaskCategoryController@delete')->middleware('permission:delete_task_category')->name('deleteTaskCategory');
        Route::get('/tc/get-statuses-by-category-id/{id_task_category}', 'TaskCategoryController@getStatusesByCategoryId')->middleware('permission:list_task_category')->name('getStatusesByCategoryId');
        //task status
        Route::get('/ts','TaskStatusController@index')->middleware('permission:list_task_status')->name('indexTaskStatus');
        Route::get('/ts/new', 'TaskStatusController@new')->middleware('permission:create_task_status')->name('newTaskStatus');
        Route::post('/ts/new', 'TaskStatusController@insert')->middleware('permission:create_task_status');
        Route::get('/ts/edit/{id_task_status}', 'TaskStatusController@edit')->middleware('permission:update_task_status')->name('updateTaskStatus');
        Route::post('/ts/edit/{id_task_status}', 'TaskStatusController@update')->middleware('permission:update_task_status');
        Route::get('/ts/delete/{id_task_status}', 'TaskStatusController@delete')->middleware('permission:delete_task_status')->name('deleteTaskStatus');

        Route::get('/tm', 'TaskManagementController@index')->middleware('permission:list_task_simple')->name('indexTaskManagement');
        Route::get('/tm/select-category', 'TaskManagementController@selectCategoryPage')->middleware('permission:list_task_simple')->name('taskManagementSelectCategoryPage');
        Route::post('/tm/change-status-of-task', 'TaskManagementController@changeStatusOfTask')->middleware('permission:list_task_simple')->name('changeTaskStatus');
        Route::post('/tm/get-task-complex-datas', 'TaskManagementController@getTaskComplexDatas')->middleware('permission:list_task_simple')->name('getTaskComplexDatas');
        Route::post('/tm/update-task-complex-datas', 'TaskManagementController@updateTaskComplexDatas')->middleware('permission:update_task_complex')->name('updateTaskComplexDatas');
        Route::post('/tm/submit-task-comment', 'TaskManagementController@submitTaskComment')->middleware('permission:update_task_complex')->name('submitTaskComment');

        // EmployeeTaskWorktime task timers
        Route::get('/etw', 'EmployeeTaskWorktimeController@index')->middleware('permission:list_task_timers')->name('indexEmployeeTaskWorktime');
        Route::get('/etw/new', 'EmployeeTaskWorktimeController@new')->middleware('permission:create_task_timers')->name('newEmployeeTaskWorktime');
        Route::post('/etw/new', 'EmployeeTaskWorktimeController@insert')->middleware('permission:create_task_timers');
        Route::get('/etw/edit/{id_employee_task_worktime}', 'EmployeeTaskWorktimeController@edit')->middleware('permission:update_task_timers')->name('editEmployeeTaskWorktime');
        Route::post('/etw/edit/{id_employee_task_worktime}', 'EmployeeTaskWorktimeController@update')->middleware('permission:update_task_timers');
        Route::get('/etw/delete/{id_employee_task_worktime}', 'EmployeeTaskWorktimeController@delete')->middleware('permission:delete_task_timers')->name('deleteEmployeeTaskWorktime');
        Route::get('/etw/export-employee-task-worktimes', 'EmployeeTaskWorktimeController@export')->name('exportEmployeeTaskWorktime');

        Route::get('/etw/monthly', 'EmployeeTaskWorktimeMonthlyController@index')->middleware('permission:list_task_timers')->name('employeeTaskWorktimeTableMonthly');
        Route::get('/etw/export-monthly-employee-task-worktimes', 'EmployeeTask WorktimeMonthlyController@exportMonthlyEmployeeTaskWorktimes')->name('exportMonthlyEmployeeTaskWorktimes');
    });

    //Reservation
    Route::group(['namespace'=>'Reservation','prefix' => 'reservation', 'middleware' => ['can:use-reservation']], function () {
        Route::get('/','ReservationController@index')->middleware('permission:list_reservation')->name('reservationCalendar');
        Route::post('/view','ReservationController@view')->middleware('permission:list_reservation')->name('reservationCalendarIndex');
        Route::get('/reservcalendar','ReservationController@viewCalendar')->middleware('permission:list_reservation')->name('reservationCalendarView');
        Route::get('/reservcalendar/view','ReservationController@viewUpdateCalendar')->middleware('permission:list_reservation')->name('reservationCalendarUpdateView');
        Route::get('/reservcalendar/getFullCalendarEvents','ReservationController@getFullCalendarEvents')->middleware('permission:list_reservation')->name('getFullReservationCalendarEvents');
        Route::get('/reservcalendar/getFullCalendarEventsById','ReservationController@getFullCalendarEventsById')->middleware('permission:list_reservation')->name('getReservationCalendarEventsById');
        Route::post('/reservcalendar/postReservationByDate','ReservationController@postReservationByDate')->middleware('permission:list_reservation')->name('postReservationByDate');
        Route::post('/checkcalendar','ReservationController@postCheckCalendarReservation')->middleware('permission:list_reservation')->name('checkCalendar');
        Route::get('/new', 'ReservationController@new')->middleware('permission:create_reservation')->name('newReservation');
        //Route::post('/new', 'ReservationController@insert')->middleware('permission:create_reservation')->name('insertReservation');
        //?
        Route::get('/edit/{id_resourcetype}', 'ReservationController@edit')->middleware('permission:update_reservation')->name('editReservation');
        Route::post('/edit/{id_resourcetype}', 'ReservationController@update')->middleware('permission:update_reservation')->name('updateReservation');
        Route::get('/delete/{id_reservation}', 'ReservationController@delete')->middleware('permission:delete_reservation')->name('deleteReservation');
        Route::get('/deletes/{id_reservation}', 'ReservationController@deletes')->middleware('permission:delete_reservation')->name('deleteReservation');
        /*
            get /new    - form lekerese (buildformhelper)
            get /       - lista nézet
        */
    });
    Route::group(['namespace'=>'Reservation','prefix' => 'reservation', 'middleware' => ['can:use-reservation', 'throttle:20,1']], function () {
        Route::post('/new', 'ReservationController@insert')->middleware('permission:create_reservation')->name('insertReservation');
    });

    //Resource - Tools type
    Route::group(['namespace'=>'ResourceType','prefix' => 'resourcetype', 'middleware' => ['can:use-reservation']], function () {
        Route::get('/','ResourceTypeController@index')->middleware('permission:list_resource_type')->name('indexResourceType');
        Route::get('/new', 'ResourceTypeController@new')->middleware('permission:create_resource_type')->name('newResourceType');
        Route::post('/new', 'ResourceTypeController@insert')->middleware('permission:create_resource_type');
        Route::get('/edit/{id_resourcetype}', 'ResourceTypeController@edit')->middleware('permission:update_resource_type')->name('editResourceType');
        Route::post('/edit/{id_resourcetype}', 'ResourceTypeController@update')->middleware('permission:update_resource_type');
        Route::get('/delete/{id_resourcetype}', 'ResourceTypeController@delete')->middleware('permission:delete_resource_type')->name('deleteResourceType');
    });

    //Resource - Tools
    Route::group(['namespace'=>'Resource','prefix' => 'resource', 'middleware' => ['can:use-reservation']], function () {
        Route::get('/', 'ResourceController@index')->middleware('permission:list_resource')->name('indexResource');
        Route::get('/new', 'ResourceController@new')->middleware('permission:create_resource')->name('newResource');
        Route::post('/new', 'ResourceController@insert')->middleware('permission:create_resource');
        Route::get('/edit/{id_resource}', 'ResourceController@edit')->middleware('permission:update_resource')->name('editResource');
        Route::post('/edit/{id_resource}', 'ResourceController@update')->middleware('permission:update_resource');
        Route::get('/delete/{id_resource}', 'ResourceController@delete')->middleware('permission:delete_resource')->name('deleteResource');
    });

    //Holiday
    Route::group(['namespace'=>'Holiday','prefix' => 'holiday'], function () {
        Route::get('/', 'HolidayController@index')->middleware('permission:list_holiday')->name('indexHoliday');
        Route::get('/new', 'HolidayController@new')->middleware('permission:create_holiday')->name('newHoliday');
        Route::post('/new', 'HolidayController@insert')->middleware('permission:create_holiday');
        Route::get('/edit/{id_holiday}', 'HolidayController@edit')->middleware('permission:update_holiday')->name('editHoliday');
        Route::post('/edit/{id_holiday}', 'HolidayController@update')->middleware('permission:update_holiday');
        Route::get('/delete/{id_holiday}', 'HolidayController@delete')->middleware('permission:delete_holiday')->name('deleteHoliday');
        Route::post('/delete/{id_holiday}','HolidayController@resolveContractAndDelete')->middleware('permission:delete_holiday');
    });

    Route::get('/export/download', 'Export\ExportController@download')->name('exportDownload');

    //Employee
    Route::group(['namespace'=>'Employee','prefix' => 'employee'], function () {
        Route::get('/', 'EmployeeController@index')->middleware('permission:list_employee')->name('indexEmployee');
        Route::get('/new', 'EmployeeController@new')->middleware('permission:create_employee')->name('newEmployee');
        Route::post('/new', 'EmployeeController@insert')->middleware('permission:create_employee');
        Route::get('/edit/{id_employee}', 'EmployeeController@edit')->middleware('permission:update_employee')->name('editEmployee');
        Route::post('/edit/{id_employee}', 'EmployeeController@update')->middleware('permission:update_employee');
        Route::get('/delete/{id_employee}', 'EmployeeController@delete')->middleware('permission:delete_employee')->name('deleteEmployee');
        Route::post('/delete/{id_employee}','EmployeeController@resolveContractAndDelete')->middleware('permission:delete_employee');
        Route::get('/inactivate/{id_employee}', 'EmployeeController@inactivate')->middleware('permission:delete_employee')->name('inactivateEmployee');
        Route::get('/activate/{id_employee}', 'EmployeeController@activate')->middleware('permission:create_employee')->name('activateEmployee');
        Route::get('/import', 'EmployeeController@uploadForm')->middleware('permission:import_employee')->name('importEmployee');
        Route::post('/import', 'EmployeeController@uploadFile')->middleware('permission:import_employee')->name('showImportEmployee');
        Route::post('/import/execute','EmployeeController@import')->middleware('permission:import_employee')->name('executeImportEmployee');

        Route::group(['prefix'=>'child'], function () {
            Route::get('/new', 'EmployeeChildController@new')->middleware('permission:create_employee_child')->name('createEmployeeChild');
            Route::post('/new', 'EmployeeChildController@insert')->middleware('permission:create_employee_child');
            Route::get('/edit/{id_employee_child}', 'EmployeeChildController@edit')->middleware('permission:update_employee_child')->name('editEmployeeChild');
            Route::post('/edit/{id_employee_child}', 'EmployeeChildController@update')->middleware('permission:update_employee_child');
            Route::get('/delete/{id_employee_child}', 'EmployeeChildController@deleteChild')->middleware('permission:delete_employee_child')->name('deleteEmployeeChild');
        });

        //EmployeeSettings
        Route::group(['prefix' => 'settings'], function () {
            Route::post('/leftmenucollapse', 'EmployeeSettingsController');
        });

        Route::get('/switchViewLayout', 'EmployeeController@switchViewLayout')->name('switchViewLayout');
    });

    //Roles
    Route::group(['namespace'=>'Permission','prefix' => 'role'], function () {

        //Roles
        Route::get('/', 'RoleController@index')->middleware('permission:list_role')->name('indexRole');
        Route::get('/new', 'RoleController@new')->middleware('permission:create_role')->name('newRole');
        Route::post('/new', 'RoleController@insert')->middleware('permission:create_role');
        Route::get('/edit/{id}', 'RoleController@edit')->middleware('permission:update_role')->name('editRole');
        Route::post('/edit/{id}', 'RoleController@update')->middleware('permission:update_role');
        Route::get('/delete/{id}', 'RoleController@delete')->middleware('permission:delete_role')->name('deleteRole');
        Route::post('/delete/{id}','RoleController@resolveContractAndDelete')->middleware('permission:delete_role');

    });

    //Permissions
    Route::group(['namespace'=>'Permission','prefix' => 'permission'], function () {
        //permissions
        Route::get('/', 'PermissionController@index')->middleware('permission:list_permission')->name('indexPermission');
        Route::get('/new', 'PermissionController@new')->middleware('permission:create_permission')->name('newPermission');
        Route::post('/new', 'PermissionController@insert')->middleware('permission:create_permission');
        Route::get('/edit/{id}', 'PermissionController@edit')->middleware('permission:update_permission')->name('editPermission');
        Route::post('/edit/{id}', 'PermissionController@update')->middleware('permission:update_permission');
        Route::get('/delete/{id}', 'PermissionController@delete')->middleware('permission:delete_permission')->name('deletePermission');
        Route::post('/delete/{id}','PermissionController@resolveContractAndDelete')->middleware('permission:delete_permission');
    });

    //News routes..
    Route::group(['namespace'=>'News','prefix' => 'news'], function () {

        Route::get('/read', 'NewsController@articles')->middleware('permission:list_article')->name('readArticle');
        Route::get('/', 'NewsController@index')->middleware('permission:list_article')->name('indexArticle');
        Route::get('/show/{id_article}','NewsController@show')->middleware('permission:list_article')->name('showArticle');
        Route::get('/new', 'NewsController@new')->middleware('permission:create_article')->name('newArticle');
        Route::post('/new', 'NewsController@insert')->middleware('permission:create_article')->name('createArticle');
        Route::get('/edit/{id_article}', 'NewsController@edit')->middleware('permission:update_article')->name('editArticle');
        Route::post('/edit/{id_article}', 'NewsController@update')->middleware('permission:update_article');
        Route::get('/delete/{id_article}', 'NewsController@delete')->middleware('permission:delete_article')->name('deleteArticle');
        Route::post('/delete/{id_article}','NewsController@resolveContractAndDelete')->middleware('permission:delete_article');

        Route::post('/get-poll-options', 'NewsController@getPollOptions')->middleware('permission:update_poll');

        Route::post('/vote-on-poll', 'NewsController@voteOnPoll')->name('voteOnPoll');
    });

    //Settings routes
    Route::group(['namespace'=>'Settings','prefix' => 'configuration'], function () {
        Route::get('/', 'SettingsController@defaultConfigurationForm')->middleware('permission:configuration')->name('defaultConfigurationFormSettings');
        Route::post('/', 'SettingsController@saveDefaultConfigurationForm')->middleware('permission:configuration')->name('saveDefaultConfigurationFormSettings');
    });

   //Job scopes routes
    Route::group(['namespace'=>'JobScope','prefix' => 'feor'], function () {
        Route::get('/', 'FeorController@index')->middleware('permission:list_feor')->name('indexFeor');
        Route::get('/new', 'FeorController@new')->middleware('permission:create_feor')->name('newFeor');
        Route::post('/new', 'FeorController@insert')->middleware('permission:create_feor')->name('createFeor');
        Route::get('/edit/{id_feor}', 'FeorController@edit')->middleware('permission:update_feor')->name('editFeor');
        Route::post('/edit/{id_feor}', 'FeorController@update')->middleware('permission:update_feor')->name('updateFeor');
        Route::get('/delete/{id_feor}', 'FeorController@delete')->middleware('permission:delete_feor')->name('deleteFeor');
    });

    Route::group(['namespace'=>'JobScope','prefix' => 'jobscope'], function () {
        Route::get('/', 'JobScopeController@index')->middleware('permission:list_job_scope')->name('indexJobScope');
        Route::get('/new', 'JobScopeController@new')->middleware('permission:create_job_scope')->name('newJobScope');
        Route::post('/new', 'JobScopeController@insert')->middleware('permission:create_job_scope')->name('createJobScope');
        Route::get('/edit/{id_job_scope}', 'JobScopeController@edit')->middleware('permission:update_job_scope')->name('editJobScope');
        Route::post('/edit/{id_job_scope}', 'JobScopeController@update')->middleware('permission:update_job_scope')->name('updateJobScope');
        Route::get('/delete/{id_job_scope}', 'JobScopeController@delete')->middleware('permission:delete_job_scope')->name('deleteJobScope');
        Route::get('/description/generate', 'JobScopeGenerateDescriptionController')->middleware('permission:create_job_scope');
    });
    // Dashboard routes
    Route::group(['namespace' => 'Dashboard', 'prefix' => 'dashboard'], function() {
        Route::post('/widgets/exchange_rate', 'WidgetChartController@getExchangeRateChart')->name('getExchangeRateChart');
        Route::post('/widgets/employee_age', 'WidgetChartController@getEmployeeAgeChart')->name('getEmployeeAgeChart');
        Route::post('/widgets/employee_gender', 'WidgetChartController@getEmployeeGenderChart')->name('getEmployeeGenderChart');
        Route::post('/widgets/employee_working_years', 'WidgetChartController@getEmployeeWorkingYearsChart')->name('getEmployeeWorkingYearsChart');
        Route::post('/widgets/get-task-complex-with-timer-datas', 'WidgetChartController@getTaskComplexWithTimerDatas')->name('getTaskComplexWithTimerDatas');
        Route::post('/widgets/get-task-simple-with-task-complex-datas', 'WidgetChartController@getTaskSimpleWithTaskComplexDatas')->name('getTaskSimpleWithTaskComplexDatas');
    });


    //ImageType routes
    Route::group(['namespace' => 'ImageType', 'prefix' => 'imagetype'], function () {
        Route::get('/', 'ImageTypeController@index')->middleware('permission:list_image_type')->name('indexImageType');
        Route::get('/new', 'ImageTypeController@new')->middleware('permission:create_image_type')->name('newImageType');
        Route::post('/new', 'ImageTypeController@insert')->middleware('permission:create_image_type')->name('createImageType');
        Route::get('/edit/{id_image_type}', 'ImageTypeController@edit')->middleware('permission:update_image_type')->name('editImageType');
        Route::post('/edit/{id_image_type}', 'ImageTypeController@update')->middleware('permission:update_image_type');
        Route::get('/delete/{id_image_type}', 'ImageTypeController@delete')->middleware('permission:delete_image_type')->name('deleteImageType');
        Route::post('/delete/{id_image_type}', 'ImageTypeController@resolveContactAndDelete')->middleware('permission:delete_image_type');

        // regenerate images
        Route::get('/regenerate/model', 'ImageTypeController@regenerate')->middleware('permission:regenerate_image')->name('regenerateImage');
        Route::post('/regenerate/model', 'ImageTypeController@regenerateByModel')->middleware('permission:regenerate_image')->name('regenerateByModel');
        Route::get('/regenerate/image_type/{id_image_type}', 'ImageTypeController@regenerateByImageType')->middleware('permission:regenerate_image')->name('regenerateByImageType');
        //bulk action regenerate
        Route::post('/regenerate/bulk-action-type', 'ImageTypeController@bulkActionRegenerateByImageType')->middleware('permission:regenerate_image')->name('bulkActionRegenerateByType');
    });

    Route::post('/save_selected_year', 'Employee\EmployeeController@saveSelectedYear')->name('saveSelectedYear');

    Route::post('/save_font_size', 'Dashboard\DashboardController@saveFontSize')->name('saveFontSize');

    //FileManager routes
    Route::group(['namespace' => 'FileManager', 'prefix' => 'customfilemanager'], function () {
        // Show LFM
        Route::get('/', 'FileManagerController@index')->middleware('permission:')->name('fileManagerIndex');

        // Show integration error messages
        Route::get('/errors', 'FileManagerController@getErrors')->name('getErrors');

        // upload
        Route::any('/upload', 'UploadController@upload')->name('filemanagerUpload');

        // list images & files
        Route::get('/jsonitems', 'ItemsController@getItems');

        // folders
        Route::get('/newfolder', 'FolderController@getAddfolder');

        Route::get('/deletefolder', 'FolderController@getDeletefolder');

        Route::get('/folders', 'FolderController@getFolders');

        // rename
        Route::get('/rename', 'RenameController@getRename');

        // download
        Route::get('/download', 'DownloadController@getDownload');

        // delete
        Route::get('/delete', 'DeleteController@getDelete');

        Route::get('/checkEmpty', 'DeleteController@checkEmpty');

        Route::post('/uploadEditedDocument', 'UploadController@uploadEditedFile')->name('uploadEditedDocument');
    });

    Route::group(['prefix' => 'filemanager', 'middleware' => ['web', 'auth']], function () {
        \UniSharp\LaravelFilemanager\Lfm::routes();
	});

    //Heartbeat
    Route::group(['namespace'=>'Heartbeat','prefix' => 'heartbeats'], function () {
        Route::get('/', 'HeartbeatController@index')->middleware('permission:can-sync')->name('indexHeartbeat');
    });

    // Connection routes
    Route::group(['namespace'=>'Connection', 'prefix' => 'connection'], function () {
        Route::get('/', 'ConnectionController@index')->middleware('permission:can-sync')->name('indexConnection');
        Route::get('/new', 'ConnectionController@new')->middleware('permission:can-sync')->name('newConnection');
        Route::post('/new', 'ConnectionController@insert')->middleware('permission:can-sync')->name('createConnection');
        Route::get('/edit/{id_connection}', 'ConnectionController@edit')->middleware('permission:can-sync')->name('editConnection');
        Route::post('/edit/{id_connection}', 'ConnectionController@update')->middleware('permission:can-sync')->name('updateConnection');
        Route::get('/delete/{id_connection}', 'ConnectionController@delete')->middleware('permission:can-sync')->name('deleteConnection');
    });

    //Manual sync routes
    Route::group(['namespace'=>'ManualSync','prefix' => 'manual-sync'], function () {
        Route::get('/{idNotification?}', 'ManualSyncController@index')->middleware('permission:can-sync')->name('manualSync');
        Route::post('/counts/', 'ManualSyncController@ajaxGetCounts')->middleware('permission:can-sync')->name('getCounts');
        Route::post('/sync/', 'ManualSyncController@ajaxSyncResource')->middleware('permission:can-sync')->name('startSync');
        Route::post('/bgsync/', 'ManualSyncController@ajaxSyncResourceInBackground')->middleware('permission:can-sync')->name('syncInBackground');
    });

    //Workstate
    Route::group(['namespace' => 'WorkState', 'prefix' => 'workstate', 'middleware' => ['can:list-work-state']], function () {
        Route::get('/', 'WorkStateController@index')->middleware('permission:list_work_state')->name('indexWorkState');
        Route::get('/new', 'WorkStateController@new')->middleware('permission:create_work_state')->name('newWorkState');
        Route::post('/new', 'WorkStateController@insert')->middleware('permission:create_work_state')->name('createWorkState');
        Route::get('/edit/{id_work_state}', 'WorkStateController@edit')->middleware('permission:update_work_state')->name('editWorkState');
        Route::post('/edit/{id_work_state}', 'WorkStateController@update')->middleware('permission:update_work_state')->name('updateWorkState');
        Route::get('/delete/{id_work_state}', 'WorkStateController@delete')->middleware('permission:delete_work_state')->name('deleteWorkState');

        Route::get('/changes/raw', 'WorkStateChangeController@raw')->middleware('permission:use-work-state-changes')->name('viewRawChange');
        Route::post('/changes/raw', 'WorkStateChangeController@raw')->middleware('permission:use-work-state-changes');

        Route::post('/changes/daily', 'WorkStateChangeController@daily')->middleware('permission:use-work-state-changes')->name('viewDailyChange');
        Route::post('/changes/insert', 'WorkStateChangeController@insertRow')->middleware('permission:use-work-state-changes');
        Route::post('/changes/update', 'WorkStateChangeController@updateRow')->middleware('permission:use-work-state-changes');
        Route::post('/changes/delete', 'WorkStateChangeController@deleteRow')->middleware('permission:use-work-state-changes');

        Route::get('/changes/correction', 'WorkStateChangeController@correction')->middleware('permission:use-work-state-corrections')->name('viewChangeCorrection');
        Route::post('/changes/correction', 'WorkStateChangeController@correction')->middleware('permission:use-work-state-corrections');
        Route::post('/changes/correction/save', 'WorkStateChangeController@saveCorrection')->middleware('permission:use-work-state-corrections');

        Route::get('/monitor', 'WorkStateMonitorController@index')->middleware('permission:use-employee-monitor')->name('viewWorkStateMonitor');
        Route::get('/incorrect-times', 'WorkStateMonitorController@incorrectTimes')->middleware('permission:use-shifts')->name('viewIncorrectWorkTimes');
        Route::post('/incorrect-times', 'WorkStateMonitorController@incorrectTimes')->middleware('permission:use-shifts');

        Route::get('/statementTableDaily', 'WorkStateStatusDailyController@index')->middleware('permission:work_state_status_daily')->name('statementTableDaily');
        Route::get('/exportDailyEmployeeHasWorkStateChanges', 'WorkStateStatusDailyController@exportDailyEmployeeHasWorkStateChanges')->middleware('permission:work_state_status_daily_export')->name('exportDailyEmployeeHasWorkStateChanges');
        Route::get('/statementTableMonthly', 'WorkStateStatusMonthlyController@index')->middleware('permission:work_state_status_monthly')->name('statementTableMonthly');
        Route::get('/exportMonthlyEmployeeHasWorkStateChanges', 'WorkStateStatusMonthlyController@exportMonthlyEmployeeHasWorkStateChanges')->middleware('permission:work_state_status_monthly_export')->name('exportMonthlyEmployeeHasWorkStateChanges');
    });

    //Open AI
    Route::group(['namespace' => 'OpenAI', 'prefix' => 'openai', 'middleware' => ['can:use-smart-functions']], function () {
        Route::group(['namespace' => 'GenerateAIDA', 'prefix' => 'aida'], function () {
            Route::get('/', 'GenerateAidaController@index')->middleware('permission:generate_aida')->name('generateAida');
            Route::get('/generate', 'AidaProcessingController')->middleware('permission:generate_aida');
            Route::post('/upload', 'GenerateAidaController@uploadData')->middleware('permission:generate_aida');
        });

        Route::group(['namespace' => 'WebshopProductDescription', 'prefix' => 'webshop-product-description'], function () {
            Route::get('/', 'WebshopProductDescriptionController@index')->middleware('permission:generate_text')->name('webshopProductDescription');
            Route::get('/generate', 'WebshopProductDescriptionCreating')->middleware('permission:generate_text');
            Route::post('/upload', 'WebshopProductDescriptionController@uploadData')->middleware('permission:generate_text');
        });

        Route::get('/document/managing', 'DocumentManagingAIController@index')->middleware('permission:generate_text')->name('documentManagingIndex');
        Route::post('/document/managing/upload', 'DocumentManagingAIController@uploadFile')->middleware('permission:generate_text')->name('documentManagingUpload');
        Route::post('/document/managing/handle-request', 'DocumentManagingAIController@handleRequest')->middleware('permission:generate_text');
        Route::group(['namespace' => 'EmailProcessing', 'prefix' => 'email-processing'], function () {
            Route::get('/', 'EmailProcessingController@index')->middleware('permission:generate_text')->name('emailProcessing');
            Route::get('/generate', 'EmailProcessingGenerateController')->middleware('permission:generate_text');
            Route::post('/upload', 'EmailProcessingController@uploadData')->middleware('permission:generate_text');
        });
        Route::group(['namespace' => 'PostGeneration', 'prefix' => 'post-generation'], function () {
            Route::get('/', 'PostGenerationController@index')->middleware('permission:generate_text')->name('postGeneration');
            Route::get('/generate', 'PostGeneratingController')->middleware('permission:generate_text');
            Route::post('/upload', 'PostGenerationController@uploadData')->middleware('permission:generate_text');
        });
        Route::group(['namespace' => 'EmailSignatureRecognition', 'prefix' => 'email-signature-recognition'], function () {
           Route::post('/generate', 'EmailSignatureRecognitionController')->middleware('permission:generate_text');
        });
        Route::group(['namespace' => 'BlogGeneration', 'prefix' => 'blog-generation'], function () {
            Route::get('/', 'BlogGenerationController@index')->middleware('permission:generate_text')->name('blogGeneration');
            Route::get('/generate', 'BlogGeneratingController')->middleware('permission:generate_text');
            Route::post('/upload', 'BlogGenerationController@uploadData')->middleware('permission:generate_text');
        });
        Route::group(['namespace' => 'MakeReport', 'prefix' => 'make-report'], function () {
            Route::get('/', 'MakeReportController@index')->middleware('permission:generate_text')->name('makeReport');
            Route::get('/generate', 'MakingReportController')->middleware('permission:generate_text');
            Route::post('/upload', 'MakeReportController@uploadData')->middleware('permission:generate_text');
        });
        //Articel AI reports
        Route::group(['namespace' => 'ArticleReport', 'prefix' => 'article-report'], function () {
            Route::get('/generate', 'ArticleProcessingController')->middleware('permission:generate_text');
            Route::post('/upload', 'ArticleReportController@uploadData')->middleware('permission:generate_text');
        });
    });


    //OCompany
    Route::group(['namespace' => 'OwnCompany', 'prefix' => 'own-company', 'middleware' => ['can:list_own_company']], function () {
        Route::get('/', 'OwnCompanyController@index')->middleware('permission:list_own_company')->name('indexOwnCompany');
        Route::get('/new', 'OwnCompanyController@new')->middleware('permission:create_own_company')->name('newOwnCompany');
        Route::post('/new', 'OwnCompanyController@insert')->middleware('permission:create_own_company')->name('createOwnCompany');
        Route::get('/edit/{id_own_company}', 'OwnCompanyController@edit')->middleware('permission:update_own_company')->name('editOwnCompany');
        Route::post('/edit/{id_own_company}', 'OwnCompanyController@update')->middleware('permission:update_own_company');
        Route::get('/establishments/{id_own_company?}', 'OwnCompanyController@establishments')->middleware('permission:list_own_company')->name('getEstablishments');
        Route::post('/establishment/get', 'OwnCompanyController@getEstablishment')->middleware('permission:list_own_company');
        Route::post('/establishment/get-leader', 'OwnCompanyController@getEstablishmentLeader')->middleware('permission:list_own_company');
        Route::post('/establishment/delete', 'OwnCompanyController@deleteEstablishment');
    });

    Route::group(['namespace' => 'OwnData', 'prefix' => 'own-data', ], function () {
        Route::get('/', 'OwnDataController@getOwnData')->name('getOwnData');
        Route::post('/', 'OwnDataController@setOwnData');
        Route::post('/get-chart', 'OwnDataController@getChart')->name('getOwnDataChart');
        Route::post('/generate-qr', 'OwnDataController@generateQr');
    });

});

