<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            // Each module with its associated roles
            "prospects" => [
                "view_prospects" => "View Prospects",
                "view_prospects_detail" => "View Prospects Detail",
                "edit_prospects" => "Edit Prospects",
                "add_prospects" => "Add Prospects"
            ],
            "referrals" => [
                "view_referrals" => "View Referrals",
                "view_referrals_detail" => "View Referrals Detail",
                "edit_referrals" => "Edit Referrals",
                "add_referrals" => "Add Referrals"
            ],
            "reports" => [
                "view_trends_report" => "View Trends Report",
                "view_up_down_report" => "View Up Down Report",
                "view_date_range_summary_report" => "View Date Range Summary Report",
                "view_claims_report" => "View Claims Report",
                "view_client_threshold_report" => "View Client Threshold Report",
                "view_subclient_threshold_report" => "View Subclient Threshold Report"
            ],
            "accounting" => [
                "view_accounting_invoices" => "View Accounting Invoices",
                "view_accounting_claims" => "View Accounting Claims",
                "view_accounting_quickbooks" => "View Accounting Quickbooks",
                "view_accounting_fantasy_football" => "View Accounting Fantasy Football"
            ],
            "claims" => [
                "view_claims" => "View Claims",
                "search_claims" => "Search Claims",
                "view_claims_detail" => "View Claims Detail",
                "edit_claims" => "Edit Claims",
                "export_claims" => "Export Claims",
                "view_completed_claims" => "View Completed Claims",
                "view_pending_denial_claims" => "View Pending Denial Claims"
            ],
            "orders" => [
                "view_orders" => "View Orders",
                "search_orders" => "Search Orders",
                "view_orders_detail" => "View Orders Detail",
                "edit_orders" => "Edit Orders",
                "export_orders" => "Export Orders",
                "import_orders" => "Import Orders",
                "view_orders_test" => "View Test Orders"
            ],

            // Superclients
            "superclients" => [
                "view_superclients" => "View Superclients",
                "view_superclients_detail" => "View Superclients Detail",
                "edit_superclients" => "Edit Superclients",
                "add_superclients" => "Add Superclients"
            ],

            // Clients
            "clients" => [
                "view_clients" => "View Clients",
                "view_clients_detail" => "View Clients Detail",
                "edit_clients" => "Edit Clients",
                "add_clients" => "Add Clients"
            ],

            // Subclients
            "subclients" => [
                "view_subclients" => "View Subclients",
                "view_subclients_queue" => "View Subclients Queue",
                "view_subclients_detail" => "View Subclients Detail",
                "edit_subclients" => "Edit Subclients",
                "add_subclients" => "Add Subclients"
            ],

            // Offers
            "offers" => [
                "view_offers" => "View Offers",
                "view_offers_detail" => "View Offers Detail",
                "add_offers" => "Add Offers",
                "edit_offers" => "Edit Offers"
            ],

            // Submissions
            "submissions" => [
                "view_contact_form" => "View Contact Form",
                "edit_contact_form" => "Edit Contact Form"
            ],

            // Administration
            "administration" => [
                "view_admin_accounts" => "View Admin Accounts",
                "edit_admin_accounts" => "Edit Admin Accounts",
                "add_admin_accounts" => "Add Admin Accounts",
                "add_admin_test_order" => "Add Admin Test Order"
            ]
        ];

        // Loop through each module and insert into the database
        foreach ($modules as $moduleName => $moduleRoles) {
            // Insert the module
            $module = Module::create([
                'name' => $moduleName,
            ]);

            // Insert the module roles
            foreach ($moduleRoles as $roleName => $roleDescription) {
                ModuleRole::create([
                    'name' => $roleName,
                    'module_id' => $module->id,
                ]);
            }
        }
    }
}
