<?php

namespace App\Http\Controllers\Admin\Claims;

use App\Services\Admin\Claims\ClaimsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ExportClaimsRequest;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;


class ClaimsController extends Controller
{
    protected $claimsService;
    public function __construct(ClaimsService $claimsService)
    {
        $this->claimsService = $claimsService;
    }
    public function getClaimsData(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->claimsService->getClaimsData($data);
        return $returnedData;
    }

    public function getClaimExportFull(ExportClaimsRequest $request): Response
    {
        $filters    = $request->validated();
        $fileFields = $filters['file_fields'];

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('claims_exports');
        // Unique filename
        $filename = 'claims_export_' . now()->format('Ymd_His') . '_' . uniqid() . '.csv';
        $path     = "claims_exports/{$filename}";

        // Open stream
        $fullPath = Storage::disk('public')->path($path);
        $fp = fopen($fullPath, 'w');

        // Header row
        $headers = $this->claimsService->mapHeaders($fileFields);
        fputcsv($fp, $headers);

        // Chunked data write
        $this->claimsService
            ->buildQuery($filters)
            ->orderBy('a.id')
            ->chunk(500, function ($claims) use ($fp, $fileFields) {
                foreach ($claims as $claim) {
                    $row = $this->claimsService->mapRow($claim, $fileFields);
                    fputcsv($fp, $row);
                }
            });

        fclose($fp);

        $url = Storage::disk('public')->url($path);
        return response()->json([
            'download_url' => $url,
        ], 200);
    }

    public function detailPage($claim_id)
    {
        $returnedData = $this->claimsService->detail($claim_id);
        return $returnedData;
    }

    public function approvedSubmit(Request $request, $claim_id)
    {
        $data = $request->all();
        if ($data['fill-type'] == 'matched') {
            return $this->claimsService->approveClaim($data, $claim_id);
        } else {
            return $this->claimsService->approveClaim($data, $claim_id, true);
        }
    }
    public function updateClaim(Request $request, $claim_id)
    {
        $data = $request->all();
        if ($data['fill-type'] == 'matched') {
            return $this->claimsService->updateClaim($data, $claim_id);
        } else {
            return $this->claimsService->updateClaim($data, $claim_id, true);
        }
    }
    public function uploadFile(Request $request, $claim_id, $docType)
    {
        if ($request->input('fill-type') == 'matched') {
            return $this->claimsService->uploadFile($request, $claim_id, $docType);
        } else {
            return $this->claimsService->uploadFile($request, $claim_id, $docType, true);
        }
    }
    public function deleteMessage(Request $request, $claim_id, $messageId)
    {
        $data = $request->all();
        if ($data['fill-type'] == 'matched') {
            return $this->claimsService->deleteMessage($claim_id, $messageId);
        } else {
            return $this->claimsService->deleteMessage($claim_id, $messageId, true);
        }
    }
    public function updateMessage(Request $request, $claim_id, $messageId)
    {
        $data = $request->all();
        if ($data['fill-type'] == 'matched') {
            return $this->claimsService->updateMessage($data, $claim_id, $messageId);
        } else {
            return $this->claimsService->updateMessage($data, $claim_id, $messageId, true);
        }
    }
    public function messageSubmit(Request $request, $claim_id)
    {
        $data = $request->all();
        if ($data['fill-type'] == 'matched') {
            return $this->claimsService->submitMessage($data, $claim_id);
        } else {
            return $this->claimsService->submitMessage($data, $claim_id, true);
        }
    }
    public function requestDocument(Request $request, $claim_id)
    {
        $data = $request->all();
        if ($data['fill-type'] == 'matched') {
            return $this->claimsService->requestDocument($data, $claim_id);
        } else {
            return $this->claimsService->requestDocument($data, $claim_id, true);
        }
    }
    public function updatePolicyID(Request $request, $claim_id)
    {
        $data = $request->all();
        return $this->claimsService->updatePolicyID($data, $claim_id);
    }
}
