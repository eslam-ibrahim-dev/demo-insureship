<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ClaimRepository
{

    public function getClaims(array $filters, int $page = 1, int $perPage = 30, string $sortField = 'a.created', string $sortDir = 'DESC')
    {
        $query = DB::table('osis_claim_type_link as a')
            ->leftJoin('osis_claim as b', 'a.matched_claim_id', '=', 'b.id')
            ->leftJoin('osis_claim_unmatched as c', 'a.unmatched_claim_id', '=', 'c.id')
            ->leftJoin('osis_claim_payment as e', 'e.claim_link_id', '=', 'a.id')
            ->selectRaw("
                a.id as master_claim_id,
                a.matched_claim_id,
                a.unmatched_claim_id,
                CASE WHEN a.matched_claim_id IS NOT NULL THEN b.id ELSE c.id END as claim_id,
                CASE WHEN a.matched_claim_id IS NOT NULL THEN b.claim_type ELSE 'Unmatched' END as claim_type,
                COALESCE(b.client_id, c.client_id, 'N/A') as client_id,
                COALESCE(b.subclient_id, c.subclient_id, 'N/A') as subclient_id,
                CASE WHEN a.matched_claim_id IS NOT NULL THEN b.order_id ELSE 0 END as order_id,
                COALESCE(b.customer_name, c.customer_name) as customer_name,
                COALESCE(b.email, c.email) as email,
                COALESCE(b.phone, c.phone) as phone,
                COALESCE(b.status, c.status) as status,
                COALESCE(b.created, c.created) as created,
                CASE WHEN a.matched_claim_id IS NOT NULL THEN COALESCE(b.admin_id, 'N/A') ELSE COALESCE(c.admin_id, 'N/A') END as admin_id
            ");

        // Filters
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $statusMap = [
                'open'   => ['Claim Received', 'Under Review', 'Waiting On Documents', 'Completed', 'Approved'],
                'paid'   => ['Paid', 'Closed - Paid'],
                'denied' => ['Pending Denial', 'Denied', 'Closed - Denied']
            ];
            $statusValues = $statusMap[$filters['status']] ?? [$filters['status']];

            $query->where(function ($q) use ($statusValues) {
                $q->whereIn('b.status', $statusValues)
                    ->orWhereIn('c.status', $statusValues);
            });
        }

        if (!empty($filters['assigned_type'])) {
            if ($filters['assigned_type'] == "assigned") {
                $query->where(function ($q) {
                    $q->where('b.admin_id', '>', 0)->orWhere('c.admin_id', '>', 0);
                });
            } elseif ($filters['assigned_type'] == "unassigned") {
                $query->where(function ($q) {
                    $q->where('b.admin_id', '<=', 0)->orWhere('c.admin_id', '<=', 0);
                });
            } elseif (is_numeric($filters['assigned_type'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('b.admin_id', $filters['assigned_type'])
                        ->orWhere('c.admin_id', $filters['assigned_type']);
                });
            }
        }

        if (!empty($filters['start_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('b.created', '>=', $filters['start_date'])
                    ->orWhere('c.created', '>=', $filters['start_date']);
            });
        }

        if (!empty($filters['end_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('b.created', '<=', $filters['end_date'])
                    ->orWhere('c.created', '<=', $filters['end_date']);
            });
        }

        if (!empty($filters['tracking_number'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('b.tracking_number', $filters['tracking_number'])
                    ->orWhere('c.tracking_number', $filters['tracking_number']);
            });
        }

        if (!empty($filters['order_number'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('b.order_number', $filters['order_number'])
                    ->orWhere('c.order_number', $filters['order_number']);
            });
        }

        if (!empty($filters['claim_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('a.id', $filters['claim_id'])
                    ->orWhere('a.matched_claim_id', $filters['claim_id'])
                    ->orWhere('a.unmatched_claim_id', $filters['claim_id']);
            });
        }

        if (!empty($filters['claimant_name'])) {
            $name = "%" . $filters['claimant_name'] . "%";
            $query->where(function ($q) use ($name) {
                $q->where('b.customer_name', 'like', $name)
                    ->orWhere('c.customer_name', 'like', $name);
            });
        }

        if (!empty($filters['filed_type'])) {
            if ($filters['filed_type'] == "matched") {
                $query->whereNotNull('a.matched_claim_id');
            } else {
                $query->whereNull('a.matched_claim_id');
            }
        }

        if (!empty($filters['admin_id'])) {
            if (is_numeric($filters['admin_id']) && $filters['admin_id'] > 0) {
                $query->where(function ($q) use ($filters) {
                    $q->where('b.admin_id', $filters['admin_id'])
                        ->orWhere('c.admin_id', $filters['admin_id']);
                });
            } elseif ($filters['admin_id'] < 0) {
                $query->where(function ($q) {
                    $q->whereNull('b.admin_id')->orWhere('b.admin_id', '<=', 0);
                })->where(function ($q) {
                    $q->whereNull('c.admin_id')->orWhere('c.admin_id', '<=', 0);
                });
            }
        }

        $total = (clone $query)->count();
        // Sort & Pagination
        $query->orderBy($sortField, $sortDir)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage);

        return [
            'data' => $query->get(),
            'total' => $total
        ];
    }
}
