<?php

/*
|--------------------------------------------------------------------------
| AuthController
|--------------------------------------------------------------------------
|
| All authentication like client login, admin login, forgot password, Every functions
| are described in this controller
|
*/

//======================================================================
// Client Login
//======================================================================

Route::post('client/get-login', 'AuthController@clientGetLogin');
Route::get('signup', 'AuthController@clientSignUp');
Route::post('user/post-registration', 'AuthController@postUserRegistration');
Route::get('user/registration-verification', 'AuthController@clientRegistrationVerification');
Route::post('user/post-verification-token', 'AuthController@postVerificationToken');
Route::get('verify-user/{token}', 'AuthController@verifyUserAccount');
Route::get('forgot-password', 'AuthController@forgotUserPassword');
Route::post('user/forgot-password-token', 'AuthController@forgotUserPasswordToken');
Route::get('user/forgot-password-token-code/{token}', 'AuthController@forgotUserPasswordTokenCode');


//======================================================================
// Admin Login
//======================================================================
Route::get('admin', 'AuthController@adminLogin');
Route::post('admin/get-login', 'AuthController@adminGetLogin');
Route::get('admin/forgot-password', 'AuthController@forgotPassword');
Route::post('admin/forgot-password-token', 'AuthController@forgotPasswordToken');
Route::get('admin/forgot-password-token-code/{token}', 'AuthController@forgotPasswordTokenCode');


//======================================================================
// Permission Check
//======================================================================

/*Permission Check*/
Route::get('permission-error','AuthController@permissionError');



//======================================================================
// Update Application
//======================================================================

Route::get('update','AuthController@updateApplication');





/*
|--------------------------------------------------------------------------
| ClientDashboardController
|--------------------------------------------------------------------------
|
| Maintain application summery like Total accounts, support tickets, invoice created etc.
| Finally we will find a whole application overview in this controller
|
*/

Route::get('dashboard', 'ClientDashboardController@dashboard');
Route::get('logout', 'ClientDashboardController@logout');
Route::post('user/menu-open-status', 'ClientDashboardController@menuOpenStatus');




Route::get('admin/edit-profile', 'DashboardController@editProfile');
Route::post('admin/post-personal-info', 'DashboardController@postPersonalInfo');
Route::post('admin/update-avatar', 'DashboardController@updateAvatar');
Route::get('admin/change-password', 'DashboardController@changePassword');
Route::post('admin/update-password', 'DashboardController@updatePassword');
Route::post('admin/menu-open-status', 'DashboardController@menuOpenStatus');
/*
|--------------------------------------------------------------------------
| DashboardController
|--------------------------------------------------------------------------
|
| Maintain application summery like Total accounts, support tickets, invoice created etc.
| Finally we will find a whole application overview in this controller
|
*/

Route::get('admin/dashboard', 'DashboardController@dashboard');
Route::get('admin/logout', 'DashboardController@logout');


/*
|--------------------------------------------------------------------------
| ClientController
|--------------------------------------------------------------------------
|
| Store all clients/users information, edit, update, delete these in this controller.
|
*/

//======================================================================
// Client Manage
//======================================================================
Route::get('clients/all', 'ClientController@allClients');
Route::get('clients/add', 'ClientController@addClient');
Route::post('clients/post-new-client', 'ClientController@addClientPost');

//======================================================================
// Profile Manage
//======================================================================
Route::get('clients/view/{id}', 'ClientController@viewClient');
Route::post('clients/update-limit', 'ClientController@updateLimit');
Route::post('clients/update-image', 'ClientController@updateImage');
Route::post('clients/update-client-post', 'ClientController@updateClient');
Route::post('clients/send-sms', 'ClientController@sendSMS');

/*Export N Import wit CSV*/
Route::get('clients/export-n-import', 'ClientController@exportImport');
Route::get('clients/export-clients', 'ClientController@exportClients');
Route::get('clients/download-sample-csv', 'ClientController@downloadSampleCSV');
Route::post('clients/post-new-client-csv', 'ClientController@addNewClientCSV');

//======================================================================
// Client Delete
//======================================================================
Route::get('clients/delete-client/{id}', 'ClientController@deleteClient');


//======================================================================
// Client Group Manage
//======================================================================
Route::get('clients/groups', 'ClientController@clientGroups');
Route::post('clients/add-new-group', 'ClientController@addNewClientGroup');
Route::post('clients/update-group', 'ClientController@updateClientGroup');
Route::get('clients/export-client-group/{id}', 'ClientController@exportClientGroup');
Route::get('clients/delete-group/{id}', 'ClientController@deleteClientGroup');


/*
|--------------------------------------------------------------------------
| InvoiceController
|--------------------------------------------------------------------------
|
| Discuss in future
|
*/

Route::get('invoices/all', 'InvoiceController@allInvoices');
Route::get('invoices/recurring', 'InvoiceController@recurringInvoices');
Route::get('invoices/add', 'InvoiceController@addInvoice');
Route::post('invoices/post-new-invoice', 'InvoiceController@postInvoice');
Route::get('invoices/view/{id}', 'InvoiceController@viewInvoice');
Route::get('invoices/edit/{id}', 'InvoiceController@editInvoice');
Route::post('invoices/post-edit-invoice', 'InvoiceController@postEditInvoice');
Route::get('invoices/client-iview/{id}', 'InvoiceController@clientIView');
Route::get('invoices/iprint/{id}', 'InvoiceController@printView');
Route::get('invoices/download-pdf/{id}', 'InvoiceController@downloadPdf');
Route::get('invoices/mark-paid/{id}', 'InvoiceController@markInvoicePaid');
Route::get('invoices/mark-unpaid/{id}', 'InvoiceController@markInvoiceUnpaid');
Route::get('invoices/mark-partially-paid/{id}', 'InvoiceController@markInvoicePartiallyPaid');
Route::get('invoices/mark-cancelled/{id}', 'InvoiceController@markInvoiceCancelled');
Route::get('invoices/iprint/{id}', 'InvoiceController@printView');
Route::post('invoices/update-invoice', 'InvoiceController@updateInvoice');
Route::post('invoices/invoice-paid', 'InvoiceController@paidInvoice');
Route::post('invoices/invoice-unpaid', 'InvoiceController@unpaidInvoice');
Route::post('invoices/invoice-cancelled', 'InvoiceController@cancelledInvoice');
Route::post('invoices/invoice-partially-paid', 'InvoiceController@partiallyPaidInvoice');
Route::get('invoices/delete-invoice/{id}','InvoiceController@deleteInvoice');
Route::get('invoices/stop-recurring-invoice/{id}','InvoiceController@stopRecurringInvoice');
Route::post('invoices/send-invoice-email','InvoiceController@sendInvoiceEmail');

/*
|--------------------------------------------------------------------------
| AdministratorController
|--------------------------------------------------------------------------
|
| Discuss in future
|
*/

//======================================================================
// Administrator Manage
//======================================================================
Route::get('administrators/all','AdministratorController@allAdministrator');
Route::post('administrators/add-new','AdministratorController@addAdministrator');
Route::get('administrators/manage/{id}','AdministratorController@manageAdministrator');
Route::post('administrators/post-update-admin','AdministratorController@postUpdateAdministrator');
Route::get('administrators/delete-admin/{id}','AdministratorController@deleteAdministrator');

//======================================================================
// Administrator Role Manage
//======================================================================
Route::get('administrators/role','AdministratorController@administratorRole');
Route::post('administrators/add-role','AdministratorController@addAdministratorRole');
Route::post('administrators/update-role','AdministratorController@updateAdministratorRole');
Route::get('administrators/set-role/{id}','AdministratorController@setAdministratorRole');
Route::get('administrators/delete-role/{id}','AdministratorController@deleteAdministratorRole');
Route::post('administrators/update-admin-set-roles','AdministratorController@updateAdministratorSetRole');



/*
|--------------------------------------------------------------------------
| SupportTicketController
|--------------------------------------------------------------------------
|
| Discuss in later
|
*/

Route::get('support-tickets/all','SupportTicketController@all');
Route::get('support-tickets/create-new','SupportTicketController@createNew');
Route::get('support-tickets/view-ticket/{id}','SupportTicketController@viewTicket');
Route::get('support-tickets/department','SupportTicketController@department');
Route::get('support-tickets/view-department/{id}','SupportTicketController@viewDepartment');
Route::get('support-tickets/ticket-department/{id}','SupportTicketController@ticketDepartment');
Route::get('support-tickets/ticket-status/{id}','SupportTicketController@ticketStatus');
Route::post('support-tickets/post-department','SupportTicketController@postDepartment');
Route::post('support-tickets/update-department','SupportTicketController@updateDepartment');
Route::post('support-tickets/post-ticket','SupportTicketController@postTicket');
Route::post('support-tickets/ticket-update-department','SupportTicketController@updateTicketDepartment');
Route::post('support-tickets/ticket-update-status','SupportTicketController@updateTicketStatus');
Route::post('support-tickets/replay-ticket','SupportTicketController@replayTicket');
Route::get('support-tickets/delete-ticket/{id}','SupportTicketController@deleteTicket');
Route::get('support-tickets/delete-department/{id}','SupportTicketController@deleteDepartment');
Route::post('support-ticket/basic-info-post','SupportTicketController@postBasicInfo');
Route::post('support-ticket/post-ticket-files','SupportTicketController@postTicketFiles');
Route::get('support-ticket/download-file/{id}','SupportTicketController@downloadTicketFile');
Route::get('support-ticket/delete-ticket-file/{id}','SupportTicketController@deleteTicketFile');


/*
|--------------------------------------------------------------------------
| SystemSetting Controller
|--------------------------------------------------------------------------
|
| Discuss in future
|
*/

//======================================================================
// General Setting
//======================================================================
Route::get('settings/general','SettingController@general');
Route::post('settings/post-general-setting','SettingController@postGeneralSetting');

//======================================================================
// Localization
//======================================================================
Route::get('settings/localization','SettingController@localization');
Route::post('settings/localization-post','SettingController@localizationPost');



/*Email Template Module*/
Route::get('settings/email-templates','SettingController@emailTemplates');
Route::get('settings/email-template-manage/{id}','SettingController@manageTemplate');
Route::post('settings/email-templates-update','SettingController@updateTemplate');

//======================================================================
// Language Settings
//======================================================================
Route::get('settings/language-settings','SettingController@languageSettings');
Route::post('settings/language-settings/add','SettingController@addLanguage');
Route::get('settings/language-settings-translate/{lid}','SettingController@translateLanguage');
Route::post('settings/language-settings-translate-post','SettingController@translateLanguagePost');
Route::get('settings/language-settings-manage/{lid}','SettingController@languageSettingsManage');
Route::post('settings/language-settings-manage-post','SettingController@languageSettingManagePost');
Route::get('settings/language-settings/delete/{lid}','SettingController@deleteLanguage');

/*Language Change*/
Route::get('language/change/{id}','SettingController@languageChange');

//======================================================================
// Payment Gateway Setting
//======================================================================
Route::get('settings/payment-gateways','SettingController@paymentGateways');
Route::get('settings/payment-gateway-manage/{id}','SettingController@paymentGatewayManage');
Route::post('settings/post-payment-gateway-manage','SettingController@postPaymentGatewayManage');


/*
|--------------------------------------------------------------------------
| SMSController
|--------------------------------------------------------------------------
|
| discuss in future
|
*/

//======================================================================
// Coverage
//======================================================================
Route::get('sms/coverage','SMSController@coverage');
Route::get('sms/manage-coverage/{id}','SMSController@manageCoverage');
Route::post('sms/post-manage-coverage','SMSController@postManageCoverage');

//======================================================================
// SenderID Management
//======================================================================
Route::get('sms/sender-id-management','SMSController@senderIdManagement');
Route::get('sms/add-sender-id','SMSController@addSenderID');
Route::post('sms/post-new-sender-id','SMSController@postNewSenderID');
Route::get('sms/view-sender-id/{id}','SMSController@viewSenderID');
Route::post('sms/post-update-sender-id','SMSController@postUpdateSenderID');
Route::get('sms/delete-sender-id/{id}','SMSController@deleteSenderID');

//======================================================================
// SMS Price Plan
//======================================================================
Route::get('sms/price-plan','SMSController@pricePlan');
Route::get('sms/add-price-plan','SMSController@addPricePlan');
Route::post('sms/post-new-price-plan','SMSController@postNewPricePlan');
Route::get('sms/manage-price-plan/{id}','SMSController@managePricePlan');
Route::post('sms/post-manage-price-plan','SMSController@postManagePricePlan');
Route::get('sms/add-plan-feature/{id}','SMSController@addPlanFeature');
Route::post('sms/post-new-plan-feature','SMSController@postNewPlanFeature');
Route::get('sms/view-plan-feature/{id}','SMSController@viewPlanFeature');
Route::get('sms/delete-plan-feature/{id}','SMSController@deletePlanFeature');
Route::get('sms/manage-plan-feature/{id}','SMSController@managePlanFeature');
Route::post('sms/post-manage-plan-feature','SMSController@postManagePlanFeature');
Route::get('sms/delete-price-plan/{id}','SMSController@deletePricePlan');


//======================================================================
// SMS Gateway Manage
//======================================================================
Route::get('sms/sms-gateway','SMSController@smsGateways');
Route::get('sms/add-sms-gateways','SMSController@addSmsGateway');
Route::post('sms/post-new-sms-gateway','SMSController@postNewSmsGateway');
Route::get('sms/gateway-manage/{id}','SMSController@smsGatewayManage');
Route::get('sms/custom-gateway-manage/{id}','SMSController@customSmsGatewayManage');
Route::post('sms/post-manage-sms-gateway','SMSController@postManageSmsGateway');
Route::post('sms/post-custom-sms-gateway','SMSController@postCustomSmsGateway');
Route::get('sms/delete-sms-gateway/{id}','SMSController@deleteSmsGateway');


//======================================================================
// Send Single SMS (Version 1.1)
//======================================================================
Route::get('sms/send-single-sms','SMSController@sendSingleSMS');
Route::post('sms/post-single-sms','SMSController@postSingleSMS');



//======================================================================
// Send Bulk SMS
//======================================================================
Route::get('sms/send-sms','SMSController@sendBulkSMS');
Route::post('sms/post-bulk-sms','SMSController@postSendBulkSMS');
Route::post('sms/get-template-info','SMSController@postGetTemplateInfo');

//======================================================================
// Send SMS From File
//======================================================================
Route::get('sms/send-sms-file','SMSController@sendBulkSMSFile');
Route::get('sms/download-sample-sms-file','SMSController@downloadSampleSMSFile');
Route::post('sms/post-sms-from-file','SMSController@postSMSFromFile');

//======================================================================
// Send Schedule SMS
//======================================================================
Route::get('sms/send-schedule-sms','SMSController@sendScheduleSMS');
Route::post('sms/post-schedule-sms','SMSController@postScheduleSMS');
Route::get('sms/send-schedule-sms-file','SMSController@sendScheduleSMSFile');
Route::post('sms/post-schedule-sms-from-file','SMSController@postScheduleSMSFile');
Route::get('sms/update-schedule-sms','SMSController@updateScheduleSMS');
Route::get('sms/manage-update-schedule-sms/{id}','SMSController@manageUpdateScheduleSMS');
Route::post('sms/post-update-schedule-sms','SMSController@postUpdateScheduleSMS');
Route::get('sms/delete-schedule-sms/{id}','SMSController@deleteScheduleSMS');
Route::get('sms/delete-schedule-sms-file/{id}','SMSController@deleteScheduleSMSFile');
Route::get('sms/manage-update-schedule-sms-file/{id}','SMSController@manageUpdateScheduleSMSFile');
Route::post('sms/post-update-schedule-sms-file','SMSController@postUpdateScheduleSMSFile');
//======================================================================
// Import Phone Number By Excel/CSV
//======================================================================
Route::get('sms/import-phone-number','SMSController@importPhoneNumber');
Route::post('sms/post-import-phone-number','SMSController@postImportPhoneNumber');
Route::get('sms/delete-import-phone-number/{id}','SMSController@deleteImportPhoneNumber');
Route::get('sms/send-sms-phone-number','SMSController@sendSMSByPhoneNumber');
Route::post('sms/post-sms-by-phone-number','SMSController@postSendSMSByPhoneNumber');

//======================================================================
// Single Schedule SMS (Version 1.1)
//======================================================================
Route::get('sms/send-single-schedule-sms','SMSController@sendSingleScheduleSMS');
Route::post('sms/post-single-schedule-sms','SMSController@postSingleScheduleSMS');

//======================================================================
// Bulk Birthday SMS (Version 1.2)
//======================================================================
Route::get('sms/send-bulk-birthday-sms','SMSController@sendBulkBirthdaySMS');
Route::get('sms/download-sample-birthday-sms-file','SMSController@downloadBirthdaySMSFile');
Route::post('sms/post-bulk-birthday-sms','SMSController@postBirthdaySMS');

//======================================================================
// Bulk SMS Remainder (Version 1.2)
//======================================================================
Route::get('sms/send-bulk-sms-remainder','SMSController@sendBulkSMSRemainder');
Route::get('sms/download-sample-remainder-sms-file','SMSController@downloadRemainderSMSFile');
Route::post('sms/post-bulk-remainder-sms','SMSController@postRemainderSMS');



//======================================================================
// SMS Templates
//======================================================================
Route::get('sms/sms-templates','SMSController@smsTemplates');
Route::get('sms/create-sms-template','SMSController@createSmsTemplate');
Route::post('sms/post-sms-template','SMSController@postSmsTemplate');
Route::get('sms/manage-sms-template/{id}','SMSController@manageSmsTemplate');
Route::post('sms/post-manage-sms-template','SMSController@postManageSmsTemplate');
Route::get('sms/delete-sms-template/{id}','SMSController@deleteSmsTemplate');

//======================================================================
// API Information
//======================================================================
Route::get('ultimate-sms-api/info','SMSController@apiInfo');
Route::post('ultimate-sms-api/update-info','SMSController@updateApiInfo');
Route::any('ultimate-sms/api','PublicAccessController@ultimateSMSApi');

//======================================================================
// Two Way Gateway
//======================================================================
Route::any('sms/reply-twilio','PublicAccessController@replyTwilio');
Route::any('sms/reply-txtlocal','PublicAccessController@replyTxtLocal');
Route::any('sms/reply-smsglobal','PublicAccessController@replySmsGlobal');
Route::any('sms/reply-bulk-sms','PublicAccessController@replyBulkSMS');
Route::any('sms/reply-nexmo','PublicAccessController@replyNexmo');
Route::any('sms/reply-plivo','PublicAccessController@replyPlivo');

//======================================================================
// SMS History
//======================================================================
Route::get('sms/history','ReportsController@smsHistory');
Route::get('sms/view-inbox/{id}','ReportsController@smsViewInbox');
Route::get('sms/delete-sms/{id}','ReportsController@deleteSMS');
Route::get('sms/historyFile','ReportsController@smsHistoryFile');
Route::get('sms/historyFileSchedule','ReportsController@smsHistoryFileSchedule');
//======================================================================
// For Client Portal
//======================================================================
/*
|--------------------------------------------------------------------------
| User Controller
|--------------------------------------------------------------------------
|
| Maintain user from client portal
|
*/

//======================================================================
// Client Manage
//======================================================================
Route::get('user/all','UserController@allUsers');
Route::get('user/add', 'UserController@addUser');
Route::post('user/post-new-user', 'UserController@addUserPost');
Route::get('user/delete-user/{id}', 'UserController@deleteUser');

//======================================================================
// Profile Manage
//======================================================================
Route::get('user/view/{id}', 'UserController@viewUser');
Route::post('user/update-limit', 'UserController@updateLimit');
Route::post('user/update-image', 'UserController@updateImage');
Route::post('user/update-user-post', 'UserController@updateUser');
Route::post('user/send-sms', 'UserController@sendSMS');

/*Export N Import wit CSV*/
Route::get('user/export-n-import', 'UserController@exportImport');
Route::get('user/export-user', 'UserController@exportUsers');
Route::get('user/download-sample-csv', 'UserController@downloadSampleCSV');
Route::post('user/post-new-user-csv', 'UserController@addNewUserCSV');

//======================================================================
// Client Group Manage
//======================================================================
Route::get('users/groups', 'UserController@userGroups');
Route::post('users/add-new-group', 'UserController@addNewUserGroup');
Route::post('users/update-group', 'UserController@updateUserGroup');
Route::get('users/export-user-group/{id}', 'UserController@exportUserGroup');
Route::get('users/delete-group/{id}', 'UserController@deleteUserGroup');


/*
|--------------------------------------------------------------------------
| ClientInvoiceController
|--------------------------------------------------------------------------
|
| Discuss in future
|
*/

Route::get('user/invoices/all', 'ClientInvoiceController@allInvoices');
Route::get('user/invoices/recurring', 'ClientInvoiceController@recurringInvoices');
Route::get('user/invoices/view/{id}', 'ClientInvoiceController@viewInvoice');
Route::get('user/invoices/client-iview/{id}', 'ClientInvoiceController@clientIView');
Route::get('user/invoices/iprint/{id}', 'ClientInvoiceController@printView');
Route::get('user/invoices/download-pdf/{id}', 'ClientInvoiceController@downloadPdf');
Route::get('user/invoices/iprint/{id}', 'ClientInvoiceController@printView');
Route::get('user/invoices/iprint/{id}', 'ClientInvoiceController@printView');
Route::any('user/invoices/pay-invoice', 'PaymentController@payInvoice');
Route::any('user/invoice/success/{id}', 'PaymentController@successInvoice');
Route::any('user/invoice/cancel/{id}', 'PaymentController@cancelledInvoice');
Route::any('user/slydepay/receive-callback', 'PaymentController@slydepayReceiveCallback');
Route::post('user/invoices/pay-with-stripe', 'PaymentController@payWithStripe');


/*
|--------------------------------------------------------------------------
| UserTicketController
|--------------------------------------------------------------------------
|
|
|
*/

Route::get('user/tickets/all','UserTicketController@allSupportTickets');
Route::get('user/tickets/create-new','UserTicketController@createNewTicket');
Route::post('user/tickets/post-ticket','UserTicketController@postTicket');
Route::get('user/tickets/view-ticket/{id}','UserTicketController@viewTicket');
Route::post('user/tickets/replay-ticket','UserTicketController@replayTicket');
Route::post('user/tickets/post-ticket-files','UserTicketController@postTicketFiles');
Route::get('user/tickets/download-file/{id}','UserTicketController@downloadTicketFile');


/*
|--------------------------------------------------------------------------
| UserSMSController
|--------------------------------------------------------------------------
|
|
|
*/

//======================================================================
// Sender ID Management
//======================================================================
Route::get('user/sms/sender-id-management','UserSMSController@senderIdManagement');
Route::post('user/sms/post-sender-id','UserSMSController@postSenderID');

//======================================================================
// Send Single SMS (Version 1.1)
//======================================================================
Route::get('user/sms/send-single-sms','UserSMSController@sendSingleSMS');
Route::post('user/sms/post-single-sms','UserSMSController@postSingleSMS');

//======================================================================
// Send SMS
//======================================================================
Route::get('user/sms/send-sms','UserSMSController@sendBulkSMS');
Route::post('user/sms/post-bulk-sms','UserSMSController@postSendBulkSMS');

//======================================================================
// Send SMS From File
//======================================================================
Route::get('user/sms/send-sms-file','UserSMSController@sendSMSFromFile');
Route::get('user/sms/download-sample-sms-file','UserSMSController@downloadSampleSMSFile');
Route::post('user/sms/post-sms-from-file','UserSMSController@postSMSFromFile');

//======================================================================
// Single Schedule SMS (Version 1.1)
//======================================================================
Route::get('user/sms/send-single-schedule-sms','UserSMSController@sendSingleScheduleSMS');
Route::post('user/sms/post-single-schedule-sms','UserSMSController@postSingleScheduleSMS');

//======================================================================
// Send Schedule SMS
//======================================================================
Route::get('user/sms/send-schedule-sms','UserSMSController@sendScheduleSMS');
Route::post('user/sms/post-schedule-sms','UserSMSController@postScheduleSMS');
Route::get('user/sms/send-schedule-sms-file','UserSMSController@sendScheduleSMSFromFile');
Route::post('user/sms/post-schedule-sms-from-file','UserSMSController@postScheduleSMSFromFile');

/*Version 1.1*/
Route::get('user/sms/update-schedule-sms','UserSMSController@updateScheduleSMS');
Route::get('user/sms/manage-update-schedule-sms/{id}','UserSMSController@manageUpdateScheduleSMS');
Route::post('user/sms/post-update-schedule-sms','UserSMSController@postUpdateScheduleSMS');
Route::get('user/sms/delete-schedule-sms/{id}','UserSMSController@deleteScheduleSMS');
Route::get('user/sms/manage-update-schedule-sms-file/{id}','UserSMSController@manageUpdateScheduleSMSFile');
Route::post('user/sms/post-update-schedule-sms-file','UserSMSController@postUpdateScheduleSMSFile');
Route::get('user/sms/delete-schedule-sms-file/{id}','UserSMSController@deleteScheduleSMSFile');
//======================================================================
// Bulk Birthday SMS (Version 1.2)
//======================================================================
Route::get('user/sms/send-bulk-birthday-sms','UserSMSController@sendBulkBirthdaySMS');
Route::get('user/sms/download-sample-birthday-sms-file','UserSMSController@downloadBirthdaySMSFile');
Route::post('user/sms/post-bulk-birthday-sms','UserSMSController@postBirthdaySMS');

//======================================================================
// Bulk SMS Remainder (Version 1.2)
//======================================================================
Route::get('user/sms/send-bulk-sms-remainder','UserSMSController@sendBulkSMSRemainder');
Route::get('user/sms/download-sample-remainder-sms-file','UserSMSController@downloadRemainderSMSFile');
Route::post('user/sms/post-bulk-remainder-sms','UserSMSController@postRemainderSMS');



//======================================================================
// Import Phone Number By Excel/CSV
//======================================================================
Route::get('user/sms/import-phone-number','UserSMSController@importPhoneNumber');
Route::post('user/sms/post-import-phone-number','UserSMSController@postImportPhoneNumber');
Route::get('user/sms/delete-import-phone-number/{id}','UserSMSController@deleteImportPhoneNumber');
Route::get('user/sms/send-sms-phone-number','UserSMSController@sendSMSByPhoneNumber');
Route::post('user/sms/post-sms-by-phone-number','UserSMSController@postSendSMSByPhoneNumber');


//======================================================================
// SMS History
//======================================================================
Route::get('user/sms/history','UserSMSController@smsHistory');
Route::get('user/sms/view-inbox/{id}','UserSMSController@smsViewInbox');
Route::get('user/sms/delete-sms/{id}','UserSMSController@deleteSMS');
Route::get('user/sms/historyFile','UserSMSController@smsHistoryFile');
Route::get('user/sms/historyFileSchedule','UserSMSController@smsHistoryFileSchedule');
//======================================================================
// Purchase SMS Plan
//======================================================================
Route::get('user/sms/purchase-sms-plan','UserSMSController@purchaseSMSPlan');
Route::get('user/sms/sms-plan-feature/{id}','UserSMSController@smsPlanFeature');
Route::post('user/sms/post-purchase-sms-plan','PaymentController@purchaseSMSPlanPost');
Route::get('user/sms/purchase-plan/success/{id}','PaymentController@successPurchase');
Route::get('user/sms/purchase-plan/cancel/{id}','PaymentController@cancelledPurchase');
Route::post('user/sms/purchase-with-stripe','PaymentController@purchaseWithStripe');

//======================================================================
// API Information
//=========================sms/send-schedule-sms=============================================
Route::get('user/sms-api/info','UserSMSController@apiInfo');
Route::post('user/sms-api/update-info','UserSMSController@updateApiInfo');

//======================================================================
// User Information
//======================================================================
Route::get('user/edit-profile','ClientDashboardController@editProfile');
Route::post('user/post-personal-info', 'ClientDashboardController@postPersonalInfo');
Route::post('user/update-avatar', 'ClientDashboardController@updateAvatar');
Route::get('user/change-password', 'ClientDashboardController@changePassword');
Route::post('user/update-password', 'ClientDashboardController@updatePassword');

Route::get('admin/findMobile', 'ReportsController@findMobile');
Route::get('user/findMobile', 'UserSMSController@findMobile');
Route::get('admin/findMobileSchedule', 'ReportsController@findMobileSchedule');
Route::get('user/findMobileSchedule', 'UserSMSController@findMobileSchedule');

//======================================================================
// crawler
//=========================crawler=============================================
Route::get('craw/craw-blog-truyen', 'CrawController@crawBlogTruyen');
Route::get('craw/craw-avatar-blogtruyen', 'CrawController@crawAvatarBlogTruyen');
Route::get('craw/craw-avatar-truyentranh', 'CrawController@crawAvatarTruyenTranh');
Route::get('craw/craw-total-chap-blogtruyen', 'CrawController@crawTotalChapBlogTruyen');
Route::get('craw/craw-total-chap-truyentranh', 'CrawController@crawTotalChapTruyenTranh');

//======================================================================
// admin
//=========================admin=============================================
Route::get('/truyen-login', 'AuthController@clientLogin');
Route::get('client/them-truyen', 'TruyenController@themTruyen');
Route::post('client/post-them-truyen', 'TruyenController@postThemTruyen');
Route::get('client/danh-sach-truyen', 'TruyenController@listTruyen');
Route::get('client/get-img-avatar', 'TruyenController@getImgAvatar');
Route::get('client/get-total-chap', 'TruyenController@getTotalChap');
Route::get('client/sua-truyen/{id}', 'TruyenController@editTruyen');