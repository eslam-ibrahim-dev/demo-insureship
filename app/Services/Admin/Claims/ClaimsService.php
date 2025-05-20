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
use Illuminate\Support\Str;
use App\Models\ClaimUnmatched;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Symfony\Component\Intl\Countries;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;

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
            ->firstOrFail();

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
}
