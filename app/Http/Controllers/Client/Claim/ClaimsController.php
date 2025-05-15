<?php

namespace App\Http\Controllers\Client\Claims;

use App\Services\Client\Claims\ClaimsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function completedClaimsPage(Request $request)
    {
        $vars = $request->all();
        $returnedData = $this->claimsService->completedClaimsPage($vars);
        return $returnedData;
    }

    public function pendingDenialClaimsPage(Request $request)
    {
        $vars = $request->all();
        $returnedData = $this->claimsService->pendingDenialClaimsPage($vars);
        return $returnedData;
    }

    public function getStoreInfo(Request $request, $store_id)
    {
        $data = $request->all();
        $returnedData = $this->claimsService->getStoreInfo($data, $store_id);
        return $returnedData;
    }



    public function exportClaimsSubmit(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->claimsService->exportClaimsSubmit($data);
        return $returnedData;
    }


    public function detailPage($claim_id)
    {
        $returnedData = $this->claimsService->detail($claim_id);
        return $returnedData;
    }


    public function update(Request $request, $claim_id)
    {
        $data = $request->all();
        $returnedData = $this->claimsService->update($data, $claim_id);
        return $returnedData;
    }

    public function updatePolicyID(Request $request, $claim_id)
    {
        $data = $request->all();
        $returnedData = $this->claimsService->updatePolicyID($data, $claim_id);
        return $returnedData;
    }
}
