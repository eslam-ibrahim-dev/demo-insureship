<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientLoginPermission extends Model
{
    protected $table = "osis_client_login_permission";  
    protected $fillable = [
        'id',
        'client_login_id',
        'module',
        'created',
    ];

    public $modules = array(

        // Orders
        "orders" => array(
            "client_view_orders" => "View Orders",
            "client_search_orders" => "Search Orders", // found on View Orders
            "client_view_orders_detail" => "View Orders Detail", // found on View Orders
            "client_export_orders" => "Export Orders",
            "client_import_orders" => "Import Orders",
            "client_new_order" => "New Order Submission"
        ),

        // Claims
        "claims" => array(
            "client_view_claims" => "View Claims",
            "client_search_claims" => "Search Claims", // found on View Claims
            "client_view_claims_detail" => "View Claims Detail", // found on View Claims
            "client_edit_claims_detail" => "Edit Claims Detail", // found on View Claims
            "client_export_claims" => "Export Claims",
            "client_new_claim" => "New Claim Submission",
        ),

        // Reports
        "reports" => array(
            "client_view_trends_report" => "View Trends Report",
        ),

        // API, tracking pixels, referrals, invoices (and payments)

        "api" => array(
            "client_view_api_account" => "View API Accounts",
            "client_view_api_documentation" => "View API Documentation"
        ),

        "tracking_pixels" => array(
            "client_view_tracking_pixels" => "View Tracking Pixels"
        ),

        "referrals" => array(
            "client_view_referral_link" => "View Referral Link",
            "client_view_referrals" => "View Referrals"
        ),

        "billing" => array(
            "client_view_invoices" => "View Invoices",
            "client_view_payments" => "View Payments",
            "client_make_payment" => "Make Payment"
        ),
    );

    
}
