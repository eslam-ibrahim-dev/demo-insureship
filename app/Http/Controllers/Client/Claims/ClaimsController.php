<?php

namespace App\Http\Controllers\Client\Claims;

use App\Http\Requests\ListClaimsRequest;
use App\Models\ClaimLink;
use App\Models\ClaimUnmatched;
use App\Models\ClientPermission;
use App\Services\Client\Claims\ClaimsService;
use App\Http\Controllers\Controller;
use App\Mail\ClaimSubmitted;
use App\Models\Claim;
use App\Services\MailConfigurationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClaimsController extends Controller
{
    protected $claimsService;
    public function __construct(ClaimsService $claimsService)
    {
        $this->claimsService = $claimsService;
    }
    public function getClaimsData(Request $request)
    {
        $claims = $this->claimsService->getClientClaimsList($request->all());

        return response()->json([
            'data' => $claims,
            'meta' => [
                'page' => (int)($request->input('page', 1)),
                'per_page' => 30,
                'total' => $claims->count()
            ]
        ]);
    }

    public function getClientClaimsList(ListClaimsRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $query = $this->claimsService->getClaimsList($filters);
        return $query;
    }

    public function newClaimSubmit(Request $request): JsonResponse
    {
        $result = $this->claimsService->processClaim($request);
        return $result;
    }

    public function detailPage($claim_id)
    {
        $returnedData = $this->claimsService->detail($claim_id);
        return $returnedData;
    }
}
