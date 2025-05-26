<?php

namespace App\Services\Admin\Claims;

use App\Http\Resources\ClaimDetailResource;
use App\Models\Admin;
use App\Models\Claim;
use App\Models\ClaimLink;
use App\Models\Client;
use App\Models\Subclient;
use App\Models\SuperClient;
use App\Models\Offer;
use App\Models\MyMailer;
use App\Models\Webhook;
use App\Models\ClaimPayment;
use App\Services\MailerService;
use Illuminate\Support\Str;
use App\Models\ClaimUnmatched;
use App\Models\Order;
use App\Models\Order_Offer;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Symfony\Component\Intl\Countries;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClaimsService
{
    protected array $columns;
    public function __construct()
    {
        $this->columns = [
            'matched'   => Schema::getColumnListing('osis_claim'),
            'unmatched' => Schema::getColumnListing('osis_claim_unmatched'),
            'order'     => Schema::getColumnListing('osis_order'),
            'payment'   => Schema::getColumnListing('osis_claim_payment'),
        ];
    }
    public $sg_clients = array(56854, 56863, 56856, 56862, 56855, 56866, 56864, 56858);

    public function getClaimsData($data)
    {
        $claims = new Claim();
        $claimsUnmatched = new ClaimUnmatched();
        $type = $data['type'] ?? 'matched';

        switch ($type) {
            case 'matched':
                return [
                    'type' => 'matched',
                    'data' => $claims->getAdminClaimsList($data)
                ];

            case 'unmatched':
                return [
                    'type' => 'unmatched',
                    'data' => $claimsUnmatched->getAdminUnmatchedClaimsList($data)
                ];

            case 'all':
                return [
                    'type' => 'all',
                    'matched' => $claims->getAdminClaimsList($data),
                    'unmatched' => $claimsUnmatched->getAdminUnmatchedClaimsList($data)
                ];

            default:
                return [
                    'error' => 'Invalid type specified. Use "matched", "unmatched", or "all".'
                ];
        }
    }

    public function detail($claim_id)
    {
        $claimLink = ClaimLink::where('matched_claim_id', $claim_id)
            ->orWhere('unmatched_claim_id', $claim_id)
            ->first();

        if ($claimLink->matched_claim_id == $claim_id) {
            $claimLink->load([
                'matchedClaim.subclient.contacts',
                'matchedClaim.subclient.notes.admin',
                'matchedClaim.client.contacts',
                'matchedClaim.client.notes.admin',
                'matchedClaim.order.extraInfo',
                'matchedClaim.assignedAdmin',
                'matchedClaim.messages.admin',
                'matchedClaim.offers',
                'payments'
            ]);
            $claim = $claimLink->matchedClaim;
        } else {
            $claimLink->load([
                'unmatchedClaim.subclient.contacts',
                'unmatchedClaim.subclient.notes.admin',
                'unmatchedClaim.client.contacts',
                'unmatchedClaim.client.notes.admin',
                'unmatchedClaim.order.extraInfo',
                'unmatchedClaim.assignedAdmin',
                'unmatchedClaim.messages.admin',
                'payments'
            ]);
            $claim = $claimLink->unmatchedClaim;
        }

        return new ClaimDetailResource($claim);
    }


    public function buildQuery(array $filters): Builder
    {

        $q = DB::table('osis_claim_type_link as a')
            ->leftJoin('osis_claim as b', 'a.matched_claim_id', 'b.id')
            ->leftJoin('osis_claim_unmatched as c', 'a.unmatched_claim_id', 'c.id')
            ->leftJoin('osis_claim_payment as e', 'e.claim_link_id', 'a.id')
            ->leftJoin('osis_order as d', 'b.order_id', 'd.id')
            ->leftJoin('osis_client as cl', function ($join) {
                $join->on('cl.id', '=', DB::raw('COALESCE(b.client_id, c.client_id)'));
            })
            ->leftJoin('osis_subclient as sc', function ($join) {
                $join->on('sc.id', '=', DB::raw('COALESCE(b.subclient_id, c.subclient_id)'));
            })
            ->leftJoin('osis_admin as adm', function ($join) {
                $join->on('adm.id', '=', DB::raw('COALESCE(b.admin_id, c.admin_id)'));
            })
            ->where(function ($q) {
                $q->where('cl.is_test_account', 0)
                    ->orWhereNull('cl.id');
            })
            ->where(function ($q) {
                $q->where('sc.is_test_account', 0)
                    ->orWhereNull('sc.id');
            })->whereExists(function ($query) use ($filters) {
                $query->select(DB::raw(1))
                    ->from('osis_client')
                    ->whereColumn('cl.id', 'osis_client.id')
                    ->when(!empty($filters['superclient_id']), function ($q) use ($filters) {
                        $q->where('osis_client.superclient_id', $filters['superclient_id']);
                    });
            });

        $q->when(!empty($filters['admin_id']) && $filters['admin_id'] < 0, function ($q) {
            $q->where(function ($q) {
                $q->whereNull('b.admin_id')
                    ->orWhere('b.admin_id', '<=', 0)
                    ->whereNull('c.admin_id')
                    ->orWhere('c.admin_id', '<=', 0);
            });
        });

        // status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $map = match ($filters['status']) {
                'open'   => ['Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved'],
                'paid'   => ['Paid', 'Closed - Paid'],
                'denied' => ['Pending Denial', 'Denied', 'Closed - Denied'],
                default  => [$filters['status']],
            };
            $q->where(fn($qb) => $qb->whereIn('b.status', $map)->orWhereIn('c.status', $map));
        }
        // include claimant payment
        if (!empty($filters['include_claimant_payment_supplied'])) {
            $q->where('b.claimant_supplied_payment', 1);
        }

        if (!empty($filters['superclient_id'])) {
            $q->where('cl.superclient_id', $filters['superclient_id']);
        }

        // assigned_type
        if (!empty($filters['assigned_type'])) {
            match ($filters['assigned_type']) {
                'assigned'   => fn() => $q->where(fn($qb) => $qb->where('b.admin_id', '>', 0)->orWhere('c.admin_id', '>', 0)),
                'unassigned' => fn() => $q->where(fn($qb) => $qb->whereNull('b.admin_id')->orWhere('b.admin_id', '<=', 0)
                    ->whereNull('c.admin_id')->orWhere('c.admin_id', '<=', 0)),
                default      => fn() => is_numeric($filters['assigned_type'])
                    ? $q->where(fn($qb) => $qb->where('b.admin_id', $filters['assigned_type'])->orWhere('c.admin_id', $filters['assigned_type']))
                    : null,
            };
        }

        // generic filters
        foreach (['start_date', 'end_date', 'tracking_number', 'order_number', 'claim_id', 'claimant_name'] as $f) {
            if (isset($filters[$f]) && $filters[$f] !== '') {
                $v = $filters[$f];
                $q->where(fn($qb) => $this->applyFilter($qb, $f, $v));
            }
        }
        // filed_type
        if (!empty($filters['filed_type'])) {
            $cond = $filters['filed_type'] === 'matched' ? 'IS NOT NULL' : 'IS NULL';
            $q->whereRaw("a.matched_claim_id {$cond}");
        }

        // only requested columns
        $q->selectRaw($this->buildSelectRaw($filters['file_fields']));
        // sorting
        $sf = $filters['sort_field'] ?? 'a.created';
        $sf = $sf === 'claim_id' ? 'a.created' : $sf;
        $sd = $filters['sort_direction'] ?? 'desc';
        $q->orderByRaw("{$sf} {$sd}");

        return $q;
    }

    protected function applyFilter(Builder $qb, string $f, mixed $v): void
    {
        if ($f === 'claimant_name') {
            $qb->where('b.customer_name', 'like', "%{$v}%")
                ->orWhere('c.customer_name', 'like', "%{$v}%");
        } elseif ($f === 'claim_id') {
            $qb->where('a.id', $v)
                ->orWhere('a.matched_claim_id', $v)
                ->orWhere('a.unmatched_claim_id', $v);
        } else {
            [$col, $op] = match ($f) {
                'start_date' => ['created', '>='],
                'end_date'  => ['created', '<='],
                default     => [$f, '='],
            };
            $qb->where("b.{$col}", $op, $v)
                ->orWhere("c.{$col}", $op, $v);
        }
    }

    /** Build the raw SELECT clause */
    protected function buildSelectRaw(array $fields): string
    {
        $allowed = config('claims.exportable_fields');
        $fields  = array_intersect($allowed, $fields);

        $parts = [
            'a.id AS master_claim_id',
            'a.matched_claim_id',
            'a.unmatched_claim_id',
        ];

        foreach ($fields as $f) {
            switch ($f) {
                case 'claim_id':
                    $parts[] = "CASE WHEN a.matched_claim_id IS NOT NULL THEN b.id ELSE c.id END AS claim_id";
                    break;
                case 'claim_type':
                    $parts[] = "CASE WHEN a.matched_claim_id IS NOT NULL THEN b.claim_type ELSE 'Unmatched' END AS claim_type";
                    break;
                case 'client_id':
                case 'subclient_id':
                    $parts[] = "CASE WHEN b.{$f} IS NOT NULL THEN b.{$f} ELSE COALESCE(c.{$f}, 'N/A') END AS {$f}";
                    break;
                case 'agent':
                    $parts[] = "COALESCE(adm.name, 'N/A') AS agent";
                    break;
                case 'agent_id':
                    $parts[] = "COALESCE(b.admin_id, c.admin_id) AS agent_id";
                    break;
                case 'purchase_amount':
                    $parts[] = "CASE 
                    WHEN a.matched_claim_id IS NOT NULL THEN d.subtotal
                    ELSE c.purchase_amount 
                    END AS purchase_amount";
                    break;

                case 'order_date':
                    $parts[] = "CASE 
                    WHEN a.matched_claim_id IS NOT NULL THEN COALESCE(d.order_date, d.created)
                    ELSE c.date_of_purchase 
                    END AS order_date";
                    break;
                case 'superclient_name':
                    $parts[] = "COALESCE(cl.superclient_id, 'N/A') AS superclient_id";
                    $parts[] = "(SELECT name FROM osis_superclient WHERE id = cl.superclient_id) AS superclient_name";
                    break;

                case 'client_name':
                    $parts[] = "COALESCE(cl.name, 'N/A') AS client_name";
                    break;

                case 'subclient_name':
                    $parts[] = "COALESCE(sc.name, 'N/A') AS subclient_name";
                    break;
                case 'shipping_address1':
                case 'shipping_address2':
                case 'shipping_city':
                case 'shipping_state':
                case 'shipping_zip':
                case 'shipping_country':
                case 'billing_address1':
                case 'billing_address2':
                case 'billing_city':
                case 'billing_state':
                case 'billing_zip':
                case 'billing_country':
                    $parts[] = "CASE WHEN a.matched_claim_id IS NOT NULL THEN d.{$f} ELSE '' END AS {$f}";
                    break;
                case 'merchant_id':
                    $parts[] = "CASE 
                    WHEN a.matched_claim_id IS NOT NULL THEN d.merchant_id
                    ELSE c.merchant_id 
                    END AS merchant_id";
                    break;

                case 'merchant_name':
                    $parts[] = "CASE 
                    WHEN a.matched_claim_id IS NOT NULL THEN d.merchant_name
                    ELSE c.merchant_name 
                    END AS merchant_name";
                    break;

                default:
                    if (
                        in_array($f, $this->columns['matched'], true) ||
                        in_array($f, $this->columns['unmatched'], true)
                    ) {
                        $parts[] = "CASE WHEN a.matched_claim_id IS NOT NULL THEN b.{$f} ELSE c.{$f} END AS {$f}";
                    }
                    // payment table
                    elseif (in_array($f, $this->columns['payment'], true)) {
                        $alias = $f === 'payment_name' ? 'paid_to' : $f;
                        $parts[] = "COALESCE(e.{$f}, 'N/A') AS {$alias}";
                    }
                    // order table
                    elseif (in_array($f, $this->columns['order'], true)) {
                        $parts[] = "CASE WHEN a.matched_claim_id IS NOT NULL THEN d.{$f} ELSE c.{$f} END AS {$f}";
                    }
                    // matched/unmatched fallback

                    break;
            }
        }

        return implode(', ', $parts);
    }

    /** Map field keys to CSV headers */
    public function mapHeaders(array $fields): array
    {
        return array_map(fn($f) => config("claims.export_headers.{$f}", $f), $fields);
    }

    /**
     * Map a single claim record to a CSV row.
     */
    public function mapRow(object $claim, array $fields): array
    {
        $mapped = [];
        foreach ($fields as $field) {
            switch ($field) {
                case 'superclient':
                    $mapped[] = $claim->superclient_id . ' - ' . $claim->superclient_name;
                    break;
                case 'client':
                    $mapped[] = $claim->client_id . ' - ' . $claim->client_name;
                    break;
                case 'subclient':
                    $mapped[] = $claim->subclient_id . ' - ' . $claim->subclient_name;
                    break;
                case 'order_address':
                    $mapped = array_merge($mapped, [
                        $claim->order_address1 ?? '',
                        $claim->order_address2 ?? '',
                        $claim->order_city ?? '',
                        $claim->order_state ?? '',
                        $claim->order_zip ?? '',
                        $claim->order_country ?? ''
                    ]);
                    break;
                case 'mailing_address':
                    $mapped = array_merge($mapped, [
                        $claim->paid_to ?? '',
                        $claim->mailing_address1 ?? '',
                        $claim->mailing_address2 ?? '',
                        $claim->mailing_city ?? '',
                        $claim->mailing_state ?? '',
                        $claim->mailing_zip ?? '',
                        $claim->mailing_country ?? ''
                    ]);
                    break;
                case 'status_dates':
                    $mapped = array_merge($mapped, [
                        $claim->filed_date ?? '',
                        $claim->under_review_date ?? '',
                        $claim->wod_date ?? '',
                        $claim->completed_date ?? '',
                        $claim->approved_date ?? '',
                        $claim->paid_date ?? '',
                        $claim->pending_denial_date ?? '',
                        $claim->denied_date ?? '',
                        $claim->closed_date ?? ''
                    ]);
                    break;
                default:
                    $mapped[] = data_get($claim, $field, '');
            }
        }
        return $mapped;
    }

    /////////////////////////////////////////// approve claim /////////////////////////////////////
    public function approveClaim($data, $claimId, $isUnmatched = false): JsonResponse
    {
        $user = auth('admin')->user();
        $admin = Admin::findOrFail($user->id);

        // Get claim with appropriate relationships
        $claim = $isUnmatched
            ? ClaimUnmatched::findOrFail($claimId)
            : Claim::with('order')->findOrFail($claimId);

        $order = $isUnmatched ? null : $claim->order;

        // Get claim link based on claim type
        $claimLink = $isUnmatched
            ? ClaimLink::where('unmatched_claim_id', $claimId)->firstOrFail()
            : ClaimLink::where('matched_claim_id', $claimId)->firstOrFail();

        // Update claim status
        $claim->update([
            'unread' => 0,
            'status' => 'Approved',
            'approved_date' => now(),
        ]);

        // Process payment
        $paymentParams = $this->processPayment(
            $data,
            $claimLink->id,
            $claim->client_id,
            $order,
            $claim,
            $isUnmatched
        );

        // Save payment
        ClaimPayment::updateOrCreate(
            ['claim_link_id' => $claimLink->id],
            $paymentParams
        );

        // Send email notification
        // $this->sendApprovalEmail($claim, $order, $claimLink, $isUnmatched);

        // Trigger webhook
        $this->triggerWebhook($claim, $order, $claimLink, $data, $isUnmatched);

        // Add admin note
        $claim->messages()->create([
            'message' => "Claim Approved by: {$admin->name}",
            'type' => 'Internal Note',
            'admin_id' => $admin->id,
        ]);

        return response()->json(['status' => 'updated']);
    }

    protected function processPayment(
        array $data,
        int $claimLinkId,
        int $clientId,
        $order,
        $claim,
        bool $isUnmatched = false
    ): array {
        $paymentParams = [
            'claim_link_id' => $claimLinkId,
            'client_id' => $clientId,
            'amount' => $data['amount_to_pay'],
            'currency' => $data['currency'],
            'payment_name' => $data['paid_to'] ?? $data['payment_name'] ?? null,
            'status' => $isUnmatched ? 'Pending' : null,
        ];

        switch ($data['type']) {
            case 'paypal':
                $paymentParams['payment_type'] = 'Paypal';
                break;

            case 'ach':
            case 'wire':
                $paymentParams['payment_type'] = strtoupper($data['type']);
                $paymentParams += [
                    'bank_name' => $data['bank_name'],
                    'bank_country' => $data['bank_country'],
                    'bank_account_number' => $data['bank_account_number'],
                    'bank_routing_number' => $data['bank_routing_number'],
                    'bank_swift_code' => $data['bank_swift_code'],
                ];
                break;

            case 'check':
                $paymentParams['payment_type'] = 'Check';
                $paymentParams += $this->getCheckAddress($data, $order, $claim, $isUnmatched);
                break;

            case 'other':
                if ($isUnmatched) {
                    $paymentParams['payment_type'] = 'Other';
                }
                break;

            default:
                abort(400, 'Invalid payment type');
        }

        return $paymentParams;
    }

    protected function getCheckAddress(
        array $data,
        $order,
        $claim,
        bool $isUnmatched = false
    ): array {
        $addressFields = ['address1', 'address2', 'city', 'state', 'zip', 'country'];
        $address = [];

        switch ($data['address_type']) {
            case 'shipping_address':
                if ($isUnmatched) {
                    foreach ($addressFields as $field) {
                        $address[$field] = $order->{'shipping_' . $field};
                    }
                }
                break;

            case 'billing_address':
                if ($isUnmatched) {
                    foreach ($addressFields as $field) {
                        $address[$field] = $order->{'billing_' . $field};
                    }
                }
                break;

            case 'claim_address':
                foreach ($addressFields as $field) {
                    $address[$field] = $claim->{'order_' . $field};
                }
                break;

            case 'claimant_address':
                foreach ($addressFields as $field) {
                    $address[$field] = $claim->{'mailing_' . $field};
                }
                break;

            case 'other_mailing_address':
            case 'store_address':
            case 'new_store_address':
                foreach ($addressFields as $field) {
                    $prefix = $data['address_type'] === 'other_mailing_address'
                        ? 'other_'
                        : ($data['address_type'] === 'new_store_address' ? 'new_' : 'store_');
                    $address[$field] = $data[$prefix . $field];
                }

                if ($data['address_type'] === 'store_address' && ($data['store_update'] ?? false)) {
                    $this->updateStore($data);
                }

                if ($data['address_type'] === 'new_store_address') {
                    $this->createNewStore($data);
                }
                break;

            default:
                abort(400, 'Invalid address type');
        }

        return $address;
    }

    protected function updateStore(array $data): void
    {
        Store::where('id', $data['store_id'])->update([
            'store_name' => $data['store_store_name'],
            'name' => $data['store_name'],
            'address1' => $data['store_address1'],
            'address2' => $data['store_address2'],
            'city' => $data['store_city'],
            'state' => $data['store_state'],
            'zip' => $data['store_zip'],
            'country' => $data['store_country'],
        ]);
    }

    protected function createNewStore(array $data): void
    {
        Store::create([
            'store_name' => $data['new_store_name'],
            'name' => $data['new_name'],
            'address1' => $data['new_address1'],
            'address2' => $data['new_address2'],
            'city' => $data['new_city'],
            'state' => $data['new_state'],
            'zip' => $data['new_zip'],
            'country' => $data['new_country'],
        ]);
    }

    // protected function sendApprovalEmail($claim, $order, $claimLink, bool $isUnmatched = false
    // ): void {
    //     $client = Client::find($claim->client_id);
    //     $superclientId = $client->superclient_id;

    //     $mailerConfig = MailerService::bySuperclientClientSubclientId(
    //         $claim->client_id,
    //         $isUnmatched ? 0 : $claim->subclient_id,
    //         $superclientId
    //     );

    //     if (!$isUnmatched) {
    //         $offer = Offer::where('claim_id', $claim->id)->first();
    //         $claimType = $offer ? $offer->name : $mailerConfig['company_name'];
    //     } else {
    //         $claimType = $mailerConfig['company_name'];
    //     }

    //     $displayedClaimId = $this->getDisplayedClaimId($claim, $order, $claimLink, $isUnmatched);

    //     $emailVars = [
    //         'from_email' => $mailerConfig['email'],
    //         'to_email' => $claim->email,
    //         'file_date' => $claim->created_at,
    //         'domain' => config('app.domain'),
    //         'subject' => 'The status on your ' . $mailerConfig['company_name'] . ' claim has changed!',
    //         'type' => 'status_change',
    //         'claim_type' => $claimType,
    //         'status' => 'Approved',
    //         'claim_id' => $claim->id,
    //         'old_claim_id' => $claim->old_claim_id,
    //         'client_id' => $isUnmatched ? $claim->client_id : $order->client_id,
    //         'displayed_claim_id' => $displayedClaimId,
    //         'claim_link_id' => $claimLink->id,
    //     ];

    //     if ($isUnmatched) {
    //         $emailVars['unmatched'] = 1;
    //         $emailVars['claim_key'] = $claim->claim_key;
    //     } else {
    //         $emailVars['order_key'] = $order->order_key;
    //     }

    //     $emailVars = array_merge($mailerConfig, $emailVars);

    //     Mail::to($emailVars['to_email'])
    //         ->send(new ClaimStatusChanged($emailVars));
    // }

    // protected function getDisplayedClaimId($claim, $order, $claimLink, bool $isUnmatched = false): string
    // {
    //     if ($isUnmatched) {
    //         if (in_array($claim->client_id, Claim::USE_CLAIM_LINK_ID_CLIENT_IDS)) {
    //             return $claimLink->id;
    //         }
    //     } else {
    //         if (in_array($order->client_id, Claim::USE_CLAIM_LINK_ID_CLIENT_IDS)) {
    //             return $claimLink->id;
    //         }
    //     }

    //     return $claim->old_claim_id ?: $claim->id;
    // }

    protected function triggerWebhook($claim, $order, $claimLink, array $data, bool $isUnmatched = false): void
    {
        $payload = [
            'client_id' => $claim->client_id,
            'subclient_id' => $isUnmatched ? 0 : $claim->subclient_id,
            'claim_id' => $claimLink->id,
            'policy_id' => $isUnmatched ? 0 : $claim->order_id,
            'customer_name' => $claim->customer_name,
            'email' => $claim->email,
            'payment_amount' => $data['amount_to_pay'] ?? 0,
            'filed' => $claim->filed_date->format('Y-m-d'),
            'validated' => now()->format('Y-m-d'),
        ];

        if ($claim->client_id == 56858) { // TicketGuardian
            if (!$isUnmatched && $order && $order->extra && $order->extra->tg_policy_id) {
                $payload['tg_policy_id'] = $order->extra->tg_policy_id;
            }
        } else {
            $payload['order_number'] = $claim->order_number;
        }

        Webhook::dispatch([
            'action' => 'claim_validated',
            'client_id' => $claim->client_id,
            'subclient_id' => $isUnmatched ? 0 : $claim->subclient_id,
        ], json_encode($payload));
    }

    /////////////////////////////////// detail update ////////////////////////////////////////////////////
    protected array $statusDateFields = [
        'Claim Received' => 'filed_date',
        'Under Review' => 'under_review_date',
        'Waiting On Documents' => 'wod_date',
        'Completed' => 'completed_date',
        'Approved' => 'approved_date',
        'Pending Denial' => 'pending_denial_date',
        'Denied' => 'denied_date',
        'Paid' => 'paid_date',
        'Closed' => 'closed_date',
        'Closed - Paid' => 'closed_date',
        'Closed - Denied' => 'closed_date',
    ];

    protected array $skipWebhookStatuses = [
        'Pending Denial'
    ];

    protected array $disableEmailsForClients = [
        95280, // AfterShip
        95281, // AfterShip Test
    ];

    public function updateClaim($request, $claimId, $isUnmatched = false): JsonResponse
    {
        $data = $request;
        $user = auth('admin')->user();
        $admin = Admin::findOrFail($user->id);


        // Get claim with appropriate relationships
        $claim = $isUnmatched
            ? ClaimUnmatched::findOrFail($claimId)
            : Claim::with('order')->findOrFail($claimId);

        $order = $isUnmatched ? null : $claim->order;

        // Get claim link based on claim type
        $claimLink = $isUnmatched
            ? ClaimLink::where('unmatched_claim_id', $claimId)->firstOrFail()
            : ClaimLink::where('matched_claim_id', $claimId)->firstOrFail();

        if ($data['admin_id']) {
            $data['admin_id'] = $admin->id;
        } else {
            $data['admin_id'] = 0;
        }

        // Handle status changes
        if (!empty($data['status'])) {
            $this->handleStatusChange(
                $data,
                $claim,
                $claimLink,
                $order,
                $isUnmatched
            );
        }
        
        // Final claim update
        $claim->update(array_merge(['unread' => 0], $data));

        return response()->json(['status' => 'updated']);
    }

    protected function handleStatusChange(array &$data, $claim, $claimLink, $order, bool $isUnmatched): void
    {
        if ($this->shouldProcessStatusChange($data, $claim)) {
            // Update status date field
            $this->updateStatusDateField($data);

            // Handle email notification
            // if ($this->shouldSendEmail($data, $claim)) {
            //     $this->sendStatusChangeEmail(
            //         $data,
            //         $claim,
            //         $claimLink,
            //         $order,
            //         $isUnmatched
            //     );
            // }

            // Handle webhook
            // if (!in_array($data['status'], $this->skipWebhookStatuses)) {
            //     $this->triggerStatusChangeWebhook(
            //         $data,
            //         $claim,
            //         $claimLink,
            //         $order,
            //         $isUnmatched
            //     );
            // }

            // Handle payment status changes
            $this->handlePaymentStatusChanges($data, $claimLink);

            // Handle removal from claim payment
            $this->handleRemovalFromPayment($data, $claimLink);
        }
    }

    protected function shouldProcessStatusChange(array $data, $claim): bool
    {
        return $data['status'] != $data['previous_status']
            && !in_array($data['status'], [
                'Pending Denial',
                'Denied',
                'Closed',
                'Closed - Paid',
                'Closed - Denied'
            ]);
    }

    protected function shouldSendEmail(array $data, $claim): bool
    {
        return !empty($claim->email)
            && !in_array($claim->client_id, $this->disableEmailsForClients)
            && !empty(request('send_email'));
    }

    protected function updateStatusDateField(array &$data): void
    {
        if (isset($this->statusDateFields[$data['status']])) {
            $data[$this->statusDateFields[$data['status']]] = now();
        }
    }

    // protected function sendStatusChangeEmail(array $data,$claim,$claimLink,$order,bool $isUnmatched): void {
    //     $client = Client::find($claim->client_id);
    //     $superclientId = $client->superclient_id;

    //     $mailerConfig = MailerService::bySuperclientClientSubclientId(
    //         $claim->client_id,
    //         $isUnmatched ? 0 : $claim->subclient_id,
    //         $superclientId
    //     );

    //     $emailVars = $this->prepareEmailVars(
    //         $data,
    //         $claim,
    //         $claimLink,
    //         $order,
    //         $mailerConfig,
    //         $isUnmatched
    //     );

    //     Mail::to($emailVars['to_email'])
    //         ->send(new ClaimStatusChanged($emailVars));
    // }

    protected function prepareEmailVars(
        array $data,
        $claim,
        $claimLink,
        $order,
        array $mailerConfig,
        bool $isUnmatched
    ): array {
        $emailVars = [
            'from_email' => $mailerConfig['email'],
            'to_email' => $claim->email,
            'file_date' => $claim->created_at,
            'domain' => config('app.domain'),
            'subject' => 'The status on your ' . $mailerConfig['company_name'] . ' claim has changed!',
            'type' => 'status_change',
            'status' => $data['status'],
            'claim_id' => $claim->id,
            'old_claim_id' => $claim->old_claim_id,
            'displayed_claim_id' => $this->getDisplayedClaimId($claim, $order, $claimLink, $isUnmatched),
            'claim_link_id' => $claimLink->id,
        ];

        if ($isUnmatched) {
            $emailVars['unmatched'] = 1;
            $emailVars['company_name'] = $mailerConfig['company_name'];
            $emailVars['claim_key'] = $claim->claim_key;
            $emailVars['client_id'] = $claim->client_id;
        } else {
            $offer = Offer::where('claim_id', $claim->id)->first();
            $emailVars['claim_type'] = $offer ? $offer->name : $mailerConfig['company_name'];
            $emailVars['order_key'] = $order->order_key;
            $emailVars['client_id'] = $order->client_id;
        }

        return array_merge($mailerConfig, $emailVars);
    }

    protected function triggerStatusChangeWebhook(array $data, $claim, $claimLink, $order, bool $isUnmatched): void
    {
        $payload = [
            'subclient_id' => $isUnmatched ? 0 : $claim->subclient_id,
            'claim_id' => $claimLink->id,
            'policy_id' => $isUnmatched ? 0 : $claim->order_id,
            'status' => $data['status'],
            'filed' => $claim->filed_date->format('Y-m-d'),
        ];

        if ($claim->client_id == 56858) { // TicketGuardian
            if (!$isUnmatched && $order && $order->extra && $order->extra->tg_policy_id) {
                $payload['tg_policy_id'] = $order->extra->tg_policy_id;
            }
        } else {
            $payload['order_number'] = $claim->order_number;
        }

        if ($data['status'] == 'Denied') {
            $action = 'claim_denied';
        } else {
            $action = 'claim_status_change';
        }

        Webhook::dispatch([
            'action' => $action,
            'client_id' => $claim->client_id,
            'subclient_id' => $isUnmatched ? 0 : $claim->subclient_id,
        ], json_encode($payload));
    }

    protected function handlePaymentStatusChanges(array $data, $claimLink): void
    {
        if ($data['previous_status'] == "Approved" && $data['status'] == "Paid") {
            ClaimPayment::where('claim_link_id', $claimLink->id)
                ->update(['status' => 'Paid']);
        }
    }

    protected function handleRemovalFromPayment(array $data, $claimLink): void
    {
        if (
            $data['status'] != "Closed" &&
            in_array($data['previous_status'], ['Approved', 'Paid'])
        ) {
            ClaimPayment::where('claim_link_id', $claimLink->id)->delete();
        }
    }

    protected function getDisplayedClaimId($claim, $order, $claimLink, bool $isUnmatched): string
    {
        $clientId = $isUnmatched ? $claim->client_id : $order->client_id;

        if (in_array($clientId, Claim::USE_CLAIM_LINK_ID_CLIENT_IDS)) {
            return $claimLink->id;
        }

        return $claim->old_claim_id ?: $claim->id;
    }


    /////////////////////////////// Request Document : 
    public function requestDocument($request, $claimId, $isUnmatched = false): JsonResponse
    {
        $data = $request->all();
        $admin = auth('admin')->user();

        $claim = $isUnmatched
            ? ClaimUnmatched::findOrFail($claimId)
            : Claim::with('order')->findOrFail($claimId);

        $order = $isUnmatched ? null : $claim->order;

        $claimLink = $isUnmatched
            ? ClaimLink::where('unmatched_claim_id', $claimId)->firstOrFail()
            : ClaimLink::where('matched_claim_id', $claimId)->firstOrFail();

        // Update claim as unread
        $claim->unread = 0;

        // Handle "Other" document type
        if ($data['document_type'] == "Other") {
            $data['document_type'] = $data['other'];
        }

        // Update status if needed
        if ($claim->status == "Pending Denial") {
            $claim->status = "Under Review";
        }
        $claim->save();
        // Send email notification
        // $this->sendDocumentRequestEmail(
        //     $data,
        //     $claim,
        //     $claimLink,
        //     $order,
        //     $isUnmatched
        // );

        // Add admin message
        $this->addAdminMessage(
            $claim,
            $data,
            $admin->id
        );

        // Trigger webhook if not TicketGuardian
        // if ($claim->client_id != 56858) {
        //     $this->triggerDocumentRequestWebhook(
        //         $data,
        //         $claim,
        //         $claimLink,
        //         $isUnmatched
        //     );
        // }

        return response()->json(['message' => 'Document has been requested']);
    }

    protected function sendDocumentRequestEmail(array $data, $claim, $claimLink, $order, bool $isUnmatched): void
    {
        $client = Client::find($claim->client_id);
        $superclientId = $client->superclient_id;

        $mailerConfig = MailerService::bySuperclientClientSubclientId(
            $claim->client_id,
            $isUnmatched ? 0 : $claim->subclient_id,
            $superclientId
        );

        $emailVars = $this->prepareEmailVars(
            $data,
            $claim,
            $claimLink,
            $order,
            $mailerConfig,
            $isUnmatched
        );

        // Mail::to($emailVars['to_email'])
        //     ->send(new DocumentRequested($emailVars));
    }

    // protected function prepareEmailVars(
    //     array $data,
    //     $claim,
    //     $claimLink,
    //     $order,
    //     array $mailerConfig,
    //     bool $isUnmatched
    // ): array {
    //     $emailVars = [
    //         'from_email' => $mailerConfig['email'],
    //         'to_email' => $claim->email,
    //         'file_date' => $claim->created_at,
    //         'domain' => config('app.domain'),
    //         'subject' => 'A document has been requested for your ' . $mailerConfig['company_name'] . ' claim!',
    //         'message' => $data['message'],
    //         'doc_request_type' => $data['document_type'],
    //         'type' => 'document_request',
    //         'status' => $claim->status,
    //         'claim_id' => $claim->id,
    //         'old_claim_id' => $claim->old_claim_id,
    //         'displayed_claim_id' => $this->getDisplayedClaimId($claim, $order, $claimLink, $isUnmatched),
    //         'claim_link_id' => $claimLink->id,
    //     ];

    //     if ($isUnmatched) {
    //         $emailVars['unmatched'] = 1;
    //         $emailVars['company_name'] = $mailerConfig['company_name'];
    //         $emailVars['claim_key'] = $claim->claim_key;
    //         $emailVars['client_id'] = $claim->client_id;
    //     } else {
    //         $offer = Offer::where('claim_id', $claim->id)->first();
    //         $emailVars['claim_type'] = $offer ? $offer->name : $mailerConfig['company_name'];
    //         $emailVars['order_key'] = $order->order_key;
    //         $emailVars['client_id'] = $order->client_id;
    //     }

    //     return array_merge($mailerConfig, $emailVars);
    // }

    protected function addAdminMessage($claim, array $data, int $adminId): void
    {
        $claim->messages()->create([
            'message' => $data['message'],
            'type' => 'Document Request',
            'admin_id' => $adminId,
            'document_type' => $data['document_type'],
        ]);
    }

    protected function triggerDocumentRequestWebhook(array $data, $claim, $claimLink, bool $isUnmatched): void
    {
        $payload = [
            'subclient_id' => $isUnmatched ? 0 : $claim->subclient_id,
            'claim_id' => $claimLink->id,
            'policy_id' => $isUnmatched ? 0 : $claim->order_id,
            'order_number' => $claim->order_number,
            'message' => $data['message'],
            'document_type' => $data['document_type'],
            'filed' => $claim->filed_date->format('Y-m-d'),
        ];

        Webhook::dispatch([
            'action' => 'claim_document_requested',
            'client_id' => $claim->client_id,
            'subclient_id' => $isUnmatched ? 0 : $claim->subclient_id,
        ], json_encode($payload));
    }

    ///////////////////////////////////  Message Submit :


    public function submitMessage($request, $claimId, $isUnmatched = false): JsonResponse
    {
        $data = $request->all();
        $admin = auth('admin')->user();

        // Get claim with appropriate relationships
        $claim = $isUnmatched
            ? ClaimUnmatched::findOrFail($claimId)
            : Claim::with('order')->findOrFail($claimId);

        $order = $isUnmatched ? null : $claim->order;

        // Get claim link based on claim type
        $claimLink = $isUnmatched
            ? ClaimLink::where('unmatched_claim_id', $claimId)->firstOrFail()
            : ClaimLink::where('matched_claim_id', $claimId)->firstOrFail();

        // Update claim as unread
        $claim->unread = 0;

        // Handle Agent Message
        if ($data['message_type'] == "Agent Message" && !empty($claim->email)) {
            $this->handleAgentMessage(
                $data,
                $claim,
                $claimLink,
                $order,
                $isUnmatched
            );
        }

        $claim->save();

        // Add message to claim
        $this->addMessageToClaim(
            $claim,
            $data,
            $admin->id
        );

        return response()->json(['message' => 'Your message has been submitted']);
    }

    protected function handleAgentMessage(
        array $data,
        $claim,
        $claimLink,
        $order,
        bool $isUnmatched
    ): void {
        // Update status if pending denial
        if ($claim->status == "Pending Denial") {
            $claim->status = "Under Review";
        }

        // Send email notification
        // $this->sendAgentMessageEmail(
        //     $data,
        //     $claim,
        //     $claimLink,
        //     $order,
        //     $isUnmatched
        // );

        // // Trigger webhook if not TicketGuardian
        // if ($claim->client_id != 56858) {
        //     $this->triggerMessageSentWebhook(
        //         $data,
        //         $claim,
        //         $claimLink,
        //         $isUnmatched
        //     );
        // }
    }

    // protected function sendAgentMessageEmail(
    //     array $data,
    //     $claim,
    //     $claimLink,
    //     $order,
    //     bool $isUnmatched
    // ): void {
    //     $client = Client::find($claim->client_id);
    //     $superclientId = $client->superclient_id;

    //     $mailerConfig = MailerService::bySuperclientClientSubclientId(
    //         $claim->client_id,
    //         $isUnmatched ? 0 : $claim->subclient_id,
    //         $superclientId
    //     );

    //     $emailVars = $this->prepareEmailVars(
    //         $data,
    //         $claim,
    //         $claimLink,
    //         $order,
    //         $mailerConfig,
    //         $isUnmatched
    //     );

    //     Mail::to($emailVars['to_email'])
    //         ->send(new ClaimMessageSent($emailVars));
    // }

    // protected function prepareEmailVars(array $data,$claim,$claimLink,$order,array $mailerConfig,bool $isUnmatched
    // ): array {
    //     $emailVars = [
    //         'from_email' => $mailerConfig['email'],
    //         'to_email' => $claim->email,
    //         'file_date' => $claim->created_at,
    //         'domain' => config('app.domain'),
    //         'subject' => 'New message for your ' . $mailerConfig['company_name'] . ' claim!',
    //         'message' => $data['message'],
    //         'type' => 'new_message',
    //         'status' => $claim->status,
    //         'claim_id' => $claim->id,
    //         'old_claim_id' => $claim->old_claim_id,
    //         'displayed_claim_id' => $this->getDisplayedClaimId($claim, $order, $claimLink, $isUnmatched),
    //         'claim_link_id' => $claimLink->id,
    //     ];

    //     if ($isUnmatched) {
    //         $emailVars['unmatched'] = 1;
    //         $emailVars['company_name'] = $mailerConfig['company_name'];
    //         $emailVars['claim_key'] = $claim->claim_key;
    //         $emailVars['client_id'] = $claim->client_id;
    //     } else {
    //         $offer = Offer::where('claim_id', $claim->id)->first();
    //         $emailVars['claim_type'] = $offer ? $offer->name : $mailerConfig['company_name'];
    //         $emailVars['order_key'] = $order->order_key;
    //         $emailVars['client_id'] = $order->client_id;
    //     }

    //     return array_merge($mailerConfig, $emailVars);
    // }

    // protected function triggerMessageSentWebhook(array $data,$claim,$claimLink,bool $isUnmatched
    // ): void {
    //     $payload = [
    //         'subclient_id' => $isUnmatched ? 0 : $claim->subclient_id,
    //         'claim_id' => $claimLink->id,
    //         'policy_id' => $isUnmatched ? 0 : $claim->order_id,
    //         'order_number' => $claim->order_number,
    //         'message' => $data['message'],
    //         'filed' => $claim->filed_date->format('Y-m-d'),
    //     ];

    //     Webhook::dispatch([
    //         'action' => 'claim_message_sent',
    //         'client_id' => $claim->client_id,
    //         'subclient_id' => $isUnmatched ? 0 : $claim->subclient_id,
    //     ], json_encode($payload));
    // }

    protected function addMessageToClaim($claim, array $data, int $adminId): void
    {
        $claim->messages()->create([
            'message' => $data['message'],
            'type' => $data['message_type'],
            'admin_id' => $adminId,
        ]);
    }

    ////////////////////////////////////// Update Message 

    public function updateMessage($request, $claimId, $messageId, $isUnmatched = false): JsonResponse
    {
        $data = $request->all();

        // Get appropriate claim model based on type
        $claim = $isUnmatched
            ? ClaimUnmatched::find($claimId)
            : Claim::find($claimId);

        // Update the message
        $this->updateClaimMessage(
            $claim,
            $messageId,
            ['message' => $data['claim_message_textarea']]
        );

        return response()->json(['message' => 'Message has been updated']);
    }

    protected function updateClaimMessage($claim, $messageId, array $params): void
    {
        $claim->messages()
            ->where('id', $messageId)
            ->update($params);
    }

    ////////////////////////////////////// Delete Message 

    public function deleteMessage($claimId, $messageId, $isUnmatched = false): JsonResponse
    {

        // Get appropriate claim model based on type
        $claim = $isUnmatched
            ? ClaimUnmatched::find($claimId)
            : Claim::find($claimId);

        $this->deleteClaimMessage($claim, $messageId);

        return response()->json(['message' => 'Message has been deleted']);
    }

    protected function deleteClaimMessage($claim, string $messageId): void
    {
        $claim->messages()
            ->where('id', $messageId)
            ->delete();
    }

    //////////////////////////////////////////  update policy id 

    public function updatePolicyID($request, string $claimId): JsonResponse
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'policy_id' => 'required|integer|exists:orders,id',
            'offer_type' => 'required|string|exists:offers,link_name'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $adminId = auth('admin')->id();

        try {
            $this->updateClaimPolicy(
                claimId: $claimId,
                newPolicyId: $validated['policy_id'],
                offerType: $validated['offer_type'],
                adminId: $adminId
            );

            return response()->json([
                'message' => 'Policy ID updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update policy ID',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function updateClaimPolicy(
        string $claimId,
        int $newPolicyId,
        string $offerType,
        int $adminId
    ): void {
        if ($this->claimAlreadyFiled($newPolicyId, $offerType)) {
            throw ValidationException::withMessages([
                'policy_id' => 'A claim of this type has already been filed.'
            ]);
        }

        $claim = Claim::findOrFail($claimId);
        $order = Order::findOrFail($newPolicyId);

        DB::transaction(function () use ($claim, $order, $offerType, $claimId, $newPolicyId) {

            $this->clearClaimFromOldOrderOffer($claimId);

            $orderOffer = $this->getOrderOfferByOrderAndClaimType($newPolicyId, $offerType);
            $this->updateOrderOfferWithClaimId($orderOffer, $claimId);

            $claim->update([
                'order_id' => $newPolicyId,
                'client_id' => $order->client_id,
                'subclient_id' => $order->subclient_id,
            ]);
        });
    }

    protected function claimAlreadyFiled(int $orderId, string $claimType): bool
    {
        return Claim::where('order_id', $orderId)
            ->where('claim_type', $claimType)
            ->exists();
    }

    protected function getOrderOfferByOrderAndClaimType(int $orderId, string $claimType)
    {
        return Order_Offer::whereHas('offer', function ($query) use ($claimType) {
            $query->where('link_name', $claimType);
        })
            ->where('order_id', $orderId)
            ->firstOrFail();
    }

    protected function clearClaimFromOldOrderOffer(string $claimId): void
    {
        Order_Offer::where('claim_id', $claimId)
            ->update(['claim_id' => null]);
    }

    protected function updateOrderOfferWithClaimId(Order_Offer $orderOffer, string $claimId): void
    {
        $orderOffer->update(['claim_id' => $claimId]);
    }
    //////////////////////////////////////////// upload file 
    public function uploadFile($data, string $claimId, string $docType, bool $isUnmatched = false): JsonResponse
    {
        $admin = auth('admin')->user();

        $claim = $isUnmatched
            ? ClaimUnmatched::findOrFail($claimId)
            : Claim::findOrFail($claimId);

        $claim->update(['unread' => 0]);

        $filePath = $this->storeUploadedFile($data, $claimId, $isUnmatched);

        // Add message to claim
        $this->addDocumentMessageToClaim(
            claim: $claim,
            claimId: $claimId,
            docType: $docType,
            fileName: $filePath,
            adminId: $admin->id
        );

        return response()->json(['message' => 'File has been uploaded']);
    }

    protected function storeUploadedFile(Request $request, string $claimId, bool $isUnmatched): string
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $folder = $isUnmatched ? 'unmatched_claims' : 'matched_claims';

        // Generate a unique filename with original extension
        $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $uniqueName = "claim-{$claimId}-{$fileName}-" . uniqid() . ".{$extension}";

        return $file->storeAs(
            "claims/{$folder}/{$claimId}",
            $uniqueName
        );
    }

    protected function addDocumentMessageToClaim(
        $claim,
        string $claimId,
        string $docType,
        string $fileName,
        int $adminId
    ): void {
        $claim->messages()->create([
            'claim_id' => $claimId,
            'message' => 'File Upload',
            'type' => 'Agent Upload',
            'admin_id' => $adminId,
            'document_type' => $docType,
            'document_file' => $fileName,
        ]);
    }
}
