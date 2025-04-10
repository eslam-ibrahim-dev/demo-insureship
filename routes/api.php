<?php

use App\Http\Controllers\ModuleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\Bugs\BugsController;
use App\Http\Controllers\Admin\Test\TestController;
use App\Http\Controllers\Admin\Admin\AdminController;
use App\Http\Controllers\Admin\Offer\OfferController;
use App\Http\Controllers\Admin\Claims\ClaimsController;
use App\Http\Controllers\Admin\Reports\ReportsController;
use App\Http\Controllers\Admin\Prospect\ProspectController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Submission\SubmissionsController;
use App\Http\Controllers\Admin\Superclient\SuperclientController;
use App\Http\Controllers\Admin\Notification\NotificationController;
use App\Http\Controllers\Admin\Documentation\DocumentationController;
use App\Http\Controllers\Admin\Administration\AdministrationController;
use App\Http\Controllers\Admin\Client\ClientController as AdminClientController;
use App\Http\Controllers\Admin\Order\OrderController as AdminOrderController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});




Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/updateAdmin/{id}', [AuthController::class, 'update'])->name('register');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/modules', [ModuleController::class, 'index']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/admin/settings', [AdminController::class, 'showSettings']);
    Route::get('/admin/{id}', [AdminController::class, 'showAdmin']);
    Route::get('/admins', [AdminController::class, 'getAdmins']);
    Route::post('/admin/settings/submit', [AdminController::class, 'updateSettings']);



    Route::get('/admin/bugs/list', [BugsController::class, 'listPage']);
    Route::get('/admin/bugs/new', [BugsController::class, 'newPage']);
    Route::post('/admin/bugs/submit', [BugsController::class, 'newSubmit']);


    Route::prefix('admin')->group(function () {
        Route::get('/index-page', [TestController::class, 'indexPage']);
        Route::get('/subclients/{client_id}', [TestController::class, 'testOrderGetSubclients']);
        Route::get('/offers/{subclient_id}', [TestController::class, 'testOrderGetOffers']);
        Route::post('/submit', [TestController::class, 'testSubmit']);
    });



    Route::group([
        'prefix' => 'admin/dashboard',
    ], function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/list/{start_date?}/{end_date?}', [DashboardController::class, 'getListData']);
        Route::get('/graph/{start_date?}/{end_date?}/{subclient_id?}', [DashboardController::class, 'getLineGraphData']);
        Route::get('/insureship', [DashboardController::class, 'getInsureShipListData']);
    });

    /**
     * 
     * 1/12
     * 
     */
    /* Administration controller */
    Route::get('/settings', [AdministrationController::class, 'settingsPage'])->name('admin_settings');
    Route::post('/settings/submit', [AdministrationController::class, 'settingsSubmit'])->name('admin_settings_submit');
    Route::get('/settings/init_save_profile_pic', [AdministrationController::class, 'initSaveProfilePic'])->name('admin_settings_init_save_profile_pic');
    Route::post('/settings/crop_profile_pic', [AdministrationController::class, 'cropProfilePic'])->name('admin_settings_crop_profile_pic');
    /* Notification controller */
    Route::get('/notifications/list', [NotificationController::class, 'get_notifications'])->name('admin_notification_list');
    Route::get('/notifications/redirect/{notification_id}', [NotificationController::class, 'redirect_notification'])->name('admin_notification_redirect');
    /* Offer Controller */
    Route::get('/offer', [OfferController::class, 'indexPage'])->name('admin_offer');
    Route::get('/offers', [OfferController::class, 'listPage'])->name('admin_offers_list');
    Route::get('/offer/new', [OfferController::class, 'newPage'])->name('admin_offer_new');
    Route::post('/offer/new/submit', [OfferController::class, 'newSubmit'])->name('admin_offer_new_submit');
    Route::get('/offer/{offer_id}', [OfferController::class, 'detailPage'])->name('admin_offer_detail');
    Route::post('/offer/{offer_id}/update', [OfferController::class, 'updateSubmit'])->name('admin_offer_update');
    /* Reports Controller */
    Route::get('/reports/trends', [ReportsController::class, 'trendsPage'])->name('admin_reports_trends');
    Route::get('/reports/trends/data', [ReportsController::class, 'trendsReport'])->name('admin_reports_trends_data');
    Route::get('/reports/trends/data/{start_date}/{end_date}/{subclient_id}', [ReportsController::class, 'trendsReport'])->name('admin_reports_trends_data_filter');
    Route::get('/reports/threshold', [ReportsController::class, 'thresholdPage'])->name('admin_reports_threshold');
    Route::get('/reports/summary', [ReportsController::class, 'summaryPage'])->name('admin_reports_summary');
    Route::get('/reports/updown', [ReportsController::class, 'updownPage'])->name('admin_reports_updown');
    Route::get('/reports/threshold/client', [ReportsController::class, 'thresholdClientPage'])->name('admin_reports_threshold_client');
    Route::get('/reports/threshold/subclient', [ReportsController::class, 'thresholdSubclientPage'])->name('admin_reports_threshold_subclient');
    Route::get('/reports/date-range-summary', [ReportsController::class, 'dateRangeSummaryPage'])->name('admin_reports_date_range_summary');
    Route::post('/reports/date-range-summary/submit', [ReportsController::class, 'dateRangeSummarySubmit'])->name('admin_reports_date_range_summary_submit');
    Route::get('/reports/claims', [ReportsController::class, 'claimsPage'])->name('admin_reports_claims');
    Route::get('/reports/claims/get_subclients/{client_id}', [ReportsController::class, 'claimsPageGetSubclients'])->name('admin_reports_claims_get_subclients');
    Route::get('/reports/claims/refine', [ReportsController::class, 'claimsRefine'])->name('admin_reports_claims_refine');
    /* Submissions Controller */
    Route::get('/submissions/contact', [SubmissionsController::class, 'contactPage'])->name('admin_submissions_contact');
    Route::get('/submissions/contact/mark_unread/{contact_form_id}', [SubmissionsController::class, 'markContactUnread'])->name('admin_submissions_contact_mark_unread');
    Route::get('/submissions/contact/mark_read/{contact_form_id}', [SubmissionsController::class, 'markContactRead'])->name('admin_submissions_contact_mark_read');
    Route::get('/submissions/contact/mark_deleted/{contact_form_id}', [SubmissionsController::class, 'markContactDeleted'])->name('admin_submissions_contact_mark_deleted');
    /* Documentation Controller */
    Route::get('/documentation/api', [DocumentationController::class, 'apiPage'])->name('admin_api_doc');








    /**
     *
     * Claims
     *
     */
    Route::get('/export-claims', [ClaimsController::class, 'exportClaimsPage'])->name('admin_export_claims');
    Route::post('/export-claims/submit', [ClaimsController::class, 'exportClaimsSubmit'])->name('admin_export_claims_submit');

    Route::get('/my-claims', [ClaimsController::class, 'myClaimsPage'])->name('admin_my_claims');
    Route::get('/my-claims/refine', [ClaimsController::class, 'myClaimsRefine'])->name('admin_my_claims_refine');

    Route::get('/all-claims', [ClaimsController::class, 'allClaimsPage'])->name('admin_all_claims');
    Route::get('/all-claims/refine', [ClaimsController::class, 'allClaimsRefine'])->name('admin_all_claims_refine');

    Route::get('/completed-claims', [ClaimsController::class, 'completedClaimsPage'])->name('admin_completed_claims_list');
    Route::get('/pending-denial-claims', [ClaimsController::class, 'pendingDenialClaimsPage'])->name('admin_pending_denial_claims_list');

    Route::get('/claims/get_store_info/{store_id}', [ClaimsController::class, 'getStoreInfo'])->name('admin_claims_get_store_info');

    /**
     *
     * Claim detail and actions
     *
     */
    Route::get('/claim/{claim_id}', [ClaimsController::class, 'detailPage'])->name('admin_claim_detail');
    Route::get('/claim/{claim_id}/approved', [ClaimsController::class, 'approvedPage'])->name('admin_claim_approved');
    Route::post('/claim/{claim_id}/approved-submit', [ClaimsController::class, 'approvedSubmit'])->name('admin_claim_approved_submit');
    Route::post('/claim/{claim_id}/approved-submit/no-pay-out', [ClaimsController::class, 'approvedSubmitNoPayOut'])->name('admin_claim_approved_submit_no_pay_out');
    Route::get('/claim/{claim_id}/update', [ClaimsController::class, 'update'])->name('admin_claim_detail_update');
    Route::post('/claim/{claim_id}/request_document', [ClaimsController::class, 'requestDocument'])->name('admin_request_document');
    Route::post('/claim/{claim_id}/message-submit', [ClaimsController::class, 'messageSubmit'])->name('admin_message_submit');
    Route::get('/claim/{claim_id}/refresh-messages', [ClaimsController::class, 'messageRefresh'])->name('admin_message_refresh');
    Route::post('/claim/{claim_id}/upload-file/{doc_type}', [ClaimsController::class, 'uploadFile'])->name('admin_upload_file');
    Route::post('/claim/{claim_id}/update_policy_id', [ClaimsController::class, 'updatePolicyID'])->name('admin_claim_update_policy_id');
    Route::get('/claim/{claim_id}/print', [ClaimsController::class, 'printClaim'])->name('admin_claim_print');
    Route::delete('/claim/{claim_id}/message-delete/{claim_message_id}', [ClaimsController::class, 'messageDelete'])->name('admin_message_delete');
    Route::put('/claim/{claim_id}/message-update/{claim_message_id}', [ClaimsController::class, 'messageUpdate'])->name('admin_message_update');

    /**
     *
     * Unmatched Claim detail and actions
     *
     */
    Route::get('/unmatched_claim/{claim_id}', [ClaimsController::class, 'detailPageUnmatched'])->name('admin_unmatched_claim_detail');
    Route::get('/unmatched_claim/{claim_id}/approved', [ClaimsController::class, 'approvedPageUnmatched'])->name('admin_unmatched_claim_approved');
    Route::post('/unmatched_claim/{claim_id}/approved-submit', [ClaimsController::class, 'approvedSubmitUnmatched'])->name('admin_unmatched_claim_approved_submit');
    Route::post('/unmatched_claim/{claim_id}/approved-submit/no-pay-out', [ClaimsController::class, 'approvedSubmitNoPayOutUnmatched'])->name('admin_unmatched_claim_approved_submit_no_pay_out');
    Route::get('/unmatched_claim/{claim_id}/update', [ClaimsController::class, 'updateUnmatched'])->name('admin_unmatched_claim_detail_update');
    Route::post('/unmatched_claim/{claim_id}/request_document', [ClaimsController::class, 'requestDocumentUnmatched'])->name('admin_unmatched_request_document');
    Route::post('/unmatched_claim/{claim_id}/message-submit', [ClaimsController::class, 'messageSubmitUnmatched'])->name('admin_unmatched_message_submit');
    Route::get('/unmatched_claim/{claim_id}/refresh-messages', [ClaimsController::class, 'messageRefreshUnmatched'])->name('admin_unmatched_message_refresh');
    Route::post('/unmatched_claim/{claim_id}/upload-file/{doc_type}', [ClaimsController::class, 'uploadFileUnmatched'])->name('admin_unmatched_upload_file');
    Route::get('/unmatched_claim/{claim_id}/print', [ClaimsController::class, 'printClaimUnmatched'])->name('admin_unmatched_claim_print');
    Route::put('/unmatched_claim/{claim_id}/message-update/{claim_message_id}', [ClaimsController::class, 'messageUpdateUnmatched'])->name('admin_unmatched_message_update');
    Route::delete('/unmatched_claim/{claim_id}/message-delete/{claim_message_id}', [ClaimsController::class, 'messageDeleteUnmatched'])->name('admin_unmatched_message_delete');

    Route::get('/unmatched_claim/offer_search/{policy_id}', [ClaimsController::class, 'offerSearchUnmatched'])->name('admin_unmatched_claim_offer_search');
    Route::post('/unmatched_claim/convert/{claim_id}', [ClaimsController::class, 'unmatchedConvert'])->name('admin_unmatched_claim_convert');


    /*
    *   Superclient Controller
*/

    Route::prefix('superclient')->group(function () {
        Route::get('/', [SuperclientController::class, 'indexPage'])->name('admin_superclient');
        Route::get('/superclients', [SuperclientController::class, 'listPage'])->name('admin_superclients_list');
        Route::get('/new', [SuperclientController::class, 'newPage'])->name('admin_superclient_new');
        Route::post('/new/submit', [SuperclientController::class, 'newSubmit'])->name('admin_superclient_new_submit');
        Route::get('/{superclient_id}', [SuperclientController::class, 'detailPage'])->name('admin_superclient_detail');
        Route::post('/{superclient_id}/update', [SuperclientController::class, 'updateSubmit'])->name('admin_superclient_update');
        Route::get('/{superclient_id}/add_client', [SuperclientController::class, 'addClient'])->name('admin_superclient_detail_add_client');
        Route::get('/{superclient_id}/remove_client/{client_id}', [SuperclientController::class, 'removeClient'])->name('admin_superclient_detail_remove_client');
        Route::get('/{superclient_id}/add_contact', [SuperclientController::class, 'addContact'])->name('admin_superclient_detail_add_contact');
        Route::delete('/{contact_id}/delete_contact', [SuperclientController::class, 'deleteContact'])->name('admin_superclient_detail_delete_contact');
        Route::post('/{superclient_id}/add_note', [SuperclientController::class, 'addNote'])->name('admin_superclient_detail_add_note');
        Route::delete('/{note_id}/delete_note', [SuperclientController::class, 'deleteNote'])->name('admin_superclient_detail_delete_note');
        Route::post('/{superclient_id}/add_file', [SuperclientController::class, 'addFile'])->name('admin_superclient_detail_add_file');
        Route::put('/{superclient_id}/update_file/{file_id}', [SuperclientController::class, 'updateFile'])->name('admin_superclient_detail_update_file');
        Route::delete('/{superclient_id}/delete_file/{file_id}', [SuperclientController::class, 'deleteFile'])->name('admin_superclient_detail_delete_file');
    });


    /*
    *   Prospect Controller
*/

    Route::get('/prospects', [ProspectController::class, 'listPage'])->name('admin_prospect_list');
    Route::get('/add_prospect', [ProspectController::class, 'addProspectPage'])->name('admin_prospect_add');
    Route::post('/add_prospect_submit', [ProspectController::class, 'addProspectSubmit'])->name('admin_prospect_add_submit');
    Route::get('/prospect/{prospect_id}', [ProspectController::class, 'detailPage'])->name('admin_prospect_detail');
    Route::post('/prospect/{prospect_id}/add_action', [ProspectController::class, 'addProspectAction'])->name('admin_prospect_detail_add_action');
    Route::delete('/prospect/{prospect_action_id}/delete_action', [ProspectController::class, 'deleteProspectAction'])->name('admin_prospect_detail_delete_action');
    Route::post('/prospect/{prospect_id}/add_note', [ProspectController::class, 'addNote'])->name('admin_prospect_detail_add_note');
    Route::delete('/prospect/{note_id}/delete_note', [ProspectController::class, 'deleteNote'])->name('admin_prospect_detail_delete_note');
    Route::post('/prospect/{prospect_id}/add_file', [ProspectController::class, 'addFile'])->name('admin_prospect_detail_add_file');
    // Route::put('/prospect/{prospect_id}/update_file/{file_id}', [ProspectController::class, 'updateFile'])->name('admin_prospect_detail_update_file');
    Route::delete('/prospect/{prospect_id}/delete_file/{file_id}', [ProspectController::class, 'deleteFile'])->name('admin_prospect_detail_delete_file');
    Route::post('/prospect/{prospect_id}/add_contact', [ProspectController::class, 'addContact'])->name('admin_prospect_detail_add_contact');
    Route::delete('/prospect/{contact_id}/delete_contact', [ProspectController::class, 'deleteContact'])->name('admin_prospect_detail_delete_contact');


    /* Admin Client controller */
    Route::get('/client', [AdminClientController::class, 'getClients']);
    Route::get('/clients', [AdminClientController::class, 'listPage'])->name('admin_clients_list');
    Route::get('/clients/outstanding', [AdminClientController::class, 'listOutstandingPage'])->name('admin_clients_list_outstanding');
    Route::get('/client/new', [AdminClientController::class, 'newPage'])->name('admin_client_new');
    Route::post('/client/new/submit', [AdminClientController::class, 'newSubmit'])->name('admin_client_new_submit');
    Route::get('/client/new_qbo_customer/{client_id}', [AdminClientController::class, 'newQBOCustomer'])->name('admin_client_new_qbo_customer');
    Route::get('/client/{client_id}', [AdminClientController::class, 'detailPage'])->name('admin_client_detail');
    Route::post('/client/{client_id}/update', [AdminClientController::class, 'updateSubmit'])->name('admin_client_update');
    Route::get('/client/{client_id}/get_offers', [AdminClientController::class, 'getOffers'])->name('admin_client_detail_get_offers');
    Route::get('/client/{client_id}/new_offer', [AdminClientController::class, 'addNewOffer'])->name('admin_client_detail_new_offer');
    Route::get('/client/{client_id}/remove_offer/{client_offer_id}', [AdminClientController::class, 'removeOffer'])->name('admin_client_detail_remove_offer');
    Route::get('/client/{client_id}/add_contact', [AdminClientController::class, 'addContact'])->name('admin_client_detail_add_contact');
    Route::get('/client/{contact_id}/delete_contact', [AdminClientController::class, 'deleteContact'])->name('admin_client_detail_delete_contact');
    Route::get('/client/{client_id}/add_note', [AdminClientController::class, 'addNote'])->name('admin_client_detail_add_note');
    Route::get('/client/{note_id}/delete_note', [AdminClientController::class, 'deleteNote'])->name('admin_client_detail_delete_note');
    Route::get('/client/{client_offer_id}/update_terms', [AdminClientController::class, 'updateTerms'])->name('admin_client_detail_update_terms');
    Route::get('/client/{client_id}/add_file', [AdminClientController::class, 'addFile'])->name('admin_client_detail_add_file');
    Route::post('/client/{client_id}/update_file/{file_id}', [AdminClientController::class, 'updateFile'])->name('admin_client_detail_update_file');
    Route::delete('/client/{client_id}/delete_file/{file_id}', [AdminClientController::class, 'deleteFile'])->name('admin_client_detail_delete_file');
    Route::post('/client/{client_id}/account_management_add', [AdminClientController::class, 'accountManagementAddSubmit'])->name('admin_client_detail_account_management_add');
    Route::delete('/client/{client_id}/account_management_remove/{admin_id}', [AdminClientController::class, 'accountManagementRemoveSubmit'])->name('admin_client_detail_account_management_remove');
    Route::get('/client/{client_id}/add_jose_system_api', [AdminClientController::class, 'addJoseSystemAPI'])->name('admin_client_detail_add_jose_system_api');
    Route::get('/client/{client_id}/add_webhook_api', [AdminClientController::class, 'addWebhookAPI'])->name('admin_client_detail_add_webhook_api');
    Route::get('/client/email_preview/{client_id}/{type}/{status}/{record_id}', [AdminClientController::class, 'emailPreview'])->name('admin_client_email_preview');
    Route::get('/client/policy_file/{client_id}', [AdminClientController::class, 'getPolicyFile'])->name('admin_client_get_policy_file');
    Route::post('/client/policy_file/{client_id}/submit', [AdminClientController::class, 'submitPolicyFile'])->name('admin_client_edit_policy_file');
    Route::post('/client/invoice_rules/{client_id}/update', [AdminClientController::class, 'updateInvoiceRules'])->name('admin_client_update_invoice_rules');
    Route::post('/client/add_referral/{client_id}', [AdminClientController::class, 'addReferral'])->name('admin_client_add_referral');

    // Client Portal Routes -> admin client controller
    Route::get('/client_portal', [AdminClientController::class, 'portalListPage'])->name('admin_client_portal_list');
    Route::get('/client_portal/new', [AdminClientController::class, 'newPortalPage'])->name('admin_client_portal_new');
    Route::post('/client_portal/new/submit', [AdminClientController::class, 'newPortalSubmit'])->name('admin_client_portal_new_submit');
    Route::post('/client_portal/update/{client_login_id}/password', [AdminClientController::class, 'updateClientPortalPasswordSubmit'])->name('admin_client_portal_update_password');
    Route::get('/client_portal/detail/{client_login_id}', [AdminClientController::class, 'clientLoginDetailPage'])->name('admin_view_client_login_detail');
    Route::post('/client_portal/detail/{client_login_id}/update_permissions', [AdminClientController::class, 'clientLoginDetailUpdatePermissions'])->name('admin_client_portal_detail_update_permissions');

    // Client Queue Routes -> admin client controller
    Route::post('/client-queue/submit/{client_id}', [AdminClientController::class, 'queueSubmit'])->name('admin_client_queue_submit');
    Route::delete('/client-queue/delete/{client_id}', [AdminClientController::class, 'queueDelete'])->name('admin_client_queue_delete');



    /*
    *   Admin Order Controller 
*/

    Route::controller(AdminOrderController::class)->group(function () {
        Route::get('/orders', 'getOrders')->name('admin_orders_list');
        Route::get('/orders/refine', 'ordersRefine')->name('admin_orders_list_refine');
        Route::post('/orders/update-status/{order_id}', 'updateOrderStatus')->name('admin_orders_update_status');
        Route::get('/orders/detail/{order_id}', 'orderDetailPage');
        Route::get('/orders/transaction/refund/{transaction_id}', 'transactionRefund')->name('admin_orders_transaction_refund');

        Route::post('/orders/export', 'ordersExportSubmit')->name('orders.export');
        Route::get('/orders/import/get-subclients/{client_id}', 'ordersImportGetSubclients')->name('admin_orders_import_get_subclients');
        Route::post('/orders/import',  'importOrders');

        Route::get('/orders/subclient/{parent_id}', 'ordersPage')->name('admin_orders_subclient_list');
        Route::get('/orders/subclient/{parent_id}/refine', 'ordersRefine')->name('admin_orders_subclient_list_refine');

        Route::get('/orders/client/{parent_id}', 'ordersPage')->name('admin_orders_client_list');
        Route::get('/orders/client/{parent_id}/refine', 'ordersRefine')->name('admin_orders_client_list_refine');

        Route::post('/orders/detail/{order_id}/add_note', 'addNote')->name('admin_order_detail_add_note');
        Route::delete('/orders/detail/{note_id}/delete_note', 'deleteNote')->name('admin_order_detail_delete_note');
        Route::post('/orders/sendEmail/{order_id}', 'sendEmail')->name('admin_order_send_email');

        Route::get('/orders/test_queue', 'testQueuePage')->name('admin_orders_test_queue');
        Route::get('/orders/test_queue/{entity_type}/{entity_id}', 'testQueuePage')->name('admin_orders_test_queue_entity');
    });
});
