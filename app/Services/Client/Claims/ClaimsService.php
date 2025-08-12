<?php

namespace App\Services\Client\Claims;

use App\Http\Resources\ClaimDetailResource;
use App\Models\ClientPermission;
use App\Models\Claim;
use App\Models\ClaimLink;
use App\Repositories\ClaimRepository;
use Illuminate\Support\Str;
use App\Models\ClaimUnmatched;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;
use App\Mail\ClaimSubmitted;
use App\Services\MailConfigurationService;

class ClaimsService
{
    public $sg_clients = array(56854, 56863, 56856, 56862, 56855, 56866, 56864, 56858);

    // Could have broken up the fields by the underscore and capitalized, however this gives greater control
    private $claimRepo;
    protected array $columns;

    public function __construct(ClaimRepository $claimRepository)
    {
        $this->claimRepo = $claimRepository;
        $this->columns = [
            'matched'   => Schema::getColumnListing('osis_claim'),
            'unmatched' => Schema::getColumnListing('osis_claim_unmatched'),
            'payment'   => Schema::getColumnListing('osis_claim_payment'),
            'order'     => Schema::getColumnListing('osis_order'),
        ];
    }
    ///////////////////////////////////// Create Claim ////////////////////////////////////////////////////
    public function processClaim($request): JsonResponse
    {
        $user = auth('client')->user();
        $this->validatePermissions($user);
        try {
            return DB::transaction(function () use ($request) {
                $data = $this->processInput($request);

                $claim = $this->createClaim($data);
                $claimLink = $this->createClaimLink($claim);
                $this->addSystemMessage($claim, 'Claim Created', 'Created');
                // $this->sendConfirmation($claim, MailConfigurationService $mailerService, $claimLink->id);
                return response()->json([
                    'master_claim_id' => $claimLink->id,
                    'claim_id' => $claim->id,
                    'message' => "Claim Created Successfully"
                ], 201);
            }, 5);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Claim processing failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function validatePermissions($user): void
    {
        $permissions = ClientPermission::get_modules_by_client_login_id($user->id);

        if (!in_array('client_new_claim', $permissions, true)) {
            abort(response()->json(['message' => 'Unauthorized action'], 403));
        }
    }

    protected function processInput($request): array
    {
        $validated = $request->validate([
            'email' => 'required|email:rfc,strict',
            'claim_amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'extra_info' => 'nullable|string',
            'paid_to' => 'nullable|string',
            'comments' => 'nullable|string',
            'order_id' => 'nullable|integer',
            'claimant_supplied_payment' => 'nullable|integer',
            'order_number' => 'nullable|string|max:45',
            'tracking_number' => 'nullable|string|max:255',
            'carrier' => 'nullable|string|max:45',
            'merchant_name' => 'nullable|string|max:45',
            'merchant_id' => 'nullable|string|max:45',
            'first_name' => 'required_without:customer_name',
            'last_name' => 'required_without:customer_name',
            'phone' => 'nullable|string|max:45',
            'date_of_issue' => 'nullable|date',
            'ship_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_type' => 'nullable|string|in:Check,Credit Card,PayPal,Bank Transfer',
            'currency' => 'nullable|string|max:45',
            'order_address1' => 'nullable|string|max:255',
            'order_address2' => 'nullable|string|max:255',
            'order_city' => 'nullable|string|max:45',
            'order_state' => 'nullable|string|max:45',
            'order_zip' => 'nullable|string|max:45',
            'order_country' => 'nullable|string|max:45',
            'mailing_address1' => 'nullable|string|max:255',
            'mailing_address2' => 'nullable|string|max:255',
            'mailing_city' => 'nullable|string|max:45',
            'mailing_state' => 'nullable|string|max:45',
            'mailing_zip' => 'nullable|string|max:45',
            'mailing_country' => 'nullable|string|max:45',
            'issue_type' => 'nullable|string|max:45',
            'items_purchased' => 'nullable|string',
            'electronic_notice' => 'nullable|boolean'
        ]);

        return array_merge($validated, [
            'client_id' => $request->user()->client_id,
            'subclient_id' => $request->user()->subclient_id ?? null,
            'source' => 'Web-Client',
            'claim_type' => $request->filled('order_id') ? 'shipping_insurance' : null,
            'customer_name' => $this->getCustomerName($validated),
            'status' => 'Claim Received',
            'file_ip_address' => $request->ip(),
            'filed_date' => now(),
            'amount_to_pay_out' => abs($validated['claim_amount']),
            'claim_key' => $this->generateUniqueClaimKey(),
            'electronic_notice' => $validated['electronic_notice'] ?? true,
            'payment_type' => $validated['payment_type'] ?? 'Check',
            'created' => now(),
            'updated' => now()
        ]);
    }

    protected function getCustomerName(array $data): string
    {
        if (isset($data['customer_name'])) {
            return substr($data['customer_name'], 0, 45);
        }

        $fullName = trim($data['first_name'] . ' ' . $data['last_name']);
        return substr($fullName, 0, 45);
    }

    protected function createClaim(array $data): Claim|ClaimUnmatched
    {
        $model = $this->resolveClaimModel($data['order_id'] ?? null);
        return $model::create($data);
    }

    protected function createClaimLink($claim): ClaimLink
    {
        $relation = $claim instanceof Claim ? 'matched_claim_id' : 'unmatched_claim_id';
        return ClaimLink::create([$relation => $claim->id]);
    }

    protected function sendConfirmation($claim, MailConfigurationService $mailerService, int $claimLinkId): void
    {
        $mailer = $mailerService->getFullMailerByDomain();

        Mail::mailer($mailer['mailer'])
            ->to($claim->email)
            ->send(new ClaimSubmitted(
                claim: $claim,
                config: $mailer,
                displayedClaimId: $claimLinkId
            ));
    }

    protected function addSystemMessage($claim, string $message, string $type): void
    {
        $messageData = [
            'claim_id' => $claim->id,
            'message' => $message,
            'type' => $type,
            'created' => now(),
            'updated' => now()
        ];

        $table = $claim instanceof Claim
            ? 'osis_claim_message'
            : 'osis_claim_unmatched_message';

        DB::table($table)->insert($messageData);
    }
    public function resolveClaimModel(?string $orderId): string
    {
        return !empty($orderId) ? Claim::class : ClaimUnmatched::class;
    }

    public function generateUniqueClaimKey(): string
    {
        do {
            $key = Str::random(40);
        } while (
            Claim::where('claim_key', $key)->exists() ||
            ClaimUnmatched::where('claim_key', $key)->exists()
        );

        return $key;
    }

    ///////////////////////////////////// Get Claims ////////////////////////////////////////////////////

    // public function getClaimsList($request)
    // {
    //     $clientId = auth('client')->user()->client_id;
    //     $matchedCount = DB::table('osis_claim_type_link as a')
    //         ->join('osis_claim as b', 'a.matched_claim_id', 'b.id')
    //         ->where('b.client_id', $clientId)
    //         ->count('a.id');

    //     $unmatchedCount = DB::table('osis_claim_type_link as a')
    //         ->join('osis_claim_unmatched as c', 'a.unmatched_claim_id', 'c.id')
    //         ->where('c.client_id', $clientId)
    //         ->count('a.id');

    //     $total = $matchedCount + $unmatchedCount;

    //     $baseQuery = DB::table('osis_claim_type_link as a')
    //         ->select([
    //             'a.id as master_claim_id',
    //             'a.matched_claim_id',
    //             'a.unmatched_claim_id',
    //             DB::raw('COALESCE(b.client_id, c.client_id) as client_id'),
    //             DB::raw('COALESCE(b.subclient_id, c.subclient_id) as subclient_id'),
    //             DB::raw('COALESCE(b.order_number, c.order_number) as order_number'),
    //             DB::raw('COALESCE(b.claim_amount, c.claim_amount, 0) as claim_amount'),
    //             DB::raw('COALESCE(b.customer_name, c.customer_name) as customer_name'),
    //             DB::raw('COALESCE(b.status, c.status) as status'),
    //             DB::raw('COALESCE(b.filed_date, c.filed_date) as filed_date'),
    //         ])
    //         ->leftJoin('osis_claim as b', 'a.matched_claim_id', '=', 'b.id')
    //         ->leftJoin('osis_claim_unmatched as c', 'a.unmatched_claim_id', '=', 'c.id')
    //         ->leftJoin('osis_subclient as sc_b', 'b.subclient_id', '=', 'sc_b.id')
    //         ->leftJoin('osis_subclient as sc', DB::raw('COALESCE(b.subclient_id, c.subclient_id)'), '=', 'sc.id')
    //         ->addSelect('sc.name as subclient_name')->where('b.client_id', $clientId)->orWhere('c.client_id', $clientId);
    //     $this->applyFilters($baseQuery, $request);
    //     $perPage = $request['per_page'] ?? 20;
    //     $claims = $baseQuery->simplePaginate($perPage);
    //     return response()->json([
    //         'data' => $claims->items(),
    //         'total' => $total,
    //         'current_page' => $claims->currentPage(),
    //         'per_page' => $claims->perPage()
    //     ]);
    // }

    protected function applyFilters($query, array $filters): void
    {
        // Date range filters
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

        // Text search filters
        $this->applyTextFilters($query, $filters);

        if (!empty($filters['claim_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('a.id', $filters['claim_id'])
                    ->orWhere('a.matched_claim_id', $filters['claim_id'])
                    ->orWhere('a.unmatched_claim_id', $filters['claim_id']);
            });
        }
        // Sorting
        $this->applySorting($query, $filters);
    }
    protected function applyTextFilters($query, array $filters): void
    {
        $textFilters = [
            'order_number' => '=',
            'tracking_number' => '=',
            'claimant_name' => 'LIKE',
            'email' => 'LIKE',
        ];

        foreach ($textFilters as $field => $operator) {
            if (!empty($filters[$field])) {
                $value = $operator === 'LIKE'
                    ? "%{$filters[$field]}%"
                    : $filters[$field];

                $query->where(function ($q) use ($field, $operator, $value) {
                    $q->where("b.{$field}", $operator, $value)
                        ->orWhere("c.{$field}", $operator, $value);
                });
            }
        }
    }

    public function getClaimsList(array $filters)
    {
        $page      = $filters['page'] ?? 1;
        $perPage   = 30;
        $sortField = $filters['sort_field'] ?? 'a.created';
        $sortDir   = $filters['sort_direction'] ?? 'DESC';

        $results = $this->claimRepo->getClaims($filters, $page, $perPage, $sortField, $sortDir);


        return [
            'data' => $results,
        ];
    }

    protected function applySorting($query, array $filters): void
    {
        $sortField = $filters['sort_field'] ?? 'a.created';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        // Special handling for claim_id sorting
        if ($sortField === 'claim_id') {
            $sortField = 'a.created';
        }

        $query->orderBy($sortField, $sortDirection);
    }

    ///////////////////////////////////////////// Claim Detail ///////////////////////////////////////////////
    public function detail($claim_id)
    {
        $claimLink = ClaimLink::where('id', $claim_id)->firstOrFail();

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
}
