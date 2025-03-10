<?php

namespace App\Services\Admin\Bugs;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class BugsService {
    private $bugzillaUrl = 'https://bugs.insureship.com';
    private $apiKey = 'z6wMKDjvg5bDnPZ1CsguRu27AyaMUQeejc46Rn0d';
     /**
     * Bug severity levels with descriptions
     */
    private $severity = [
        'enhancement' => 'Could make the site better',
        'blocker' => 'The system is broken',
        'critical' => 'Critical',
        'major' => 'Major',
        'normal' => 'Normal',
        'minor' => 'Minor',
        'trivial' => 'Trivial',
    ];
    private $product = 'ShopGuarantee';
    private $version = '1.0';

    public function listPage(){
        $user = auth('admin')->user();

        $data = [
            'alevel' => $user->level,
            'admin_id' => $user->id,
        ];

        return response()->json($data, 200);
    }





    public function newPage(){
        $user = auth('admin')->user();

        $data = [
            'alevel' => $user->level,
            'admin_id' => $user->id,
            'severity' => $this->severity,
        ];

        return response()->json($data, 200);
    }






    public function newSubmit($request){
        $user = auth('admin')->user();

        $data = [
            'alevel' => $user->level,
            'admin_id' => $user->id,
        ];
        $validated = $request->validate([
            'component' => 'required|string|max:255',
            'summary' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|string|max:50',
            'severity' => 'required|string|max:50',
        ]);

        $params = [
            'api_key' => $this->apiKey,
            'product' => $this->product,
            'component' => $validated['component'],
            'summary' => $validated['summary'],
            'version' => $this->version,
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'severity' => $validated['severity'],
            'op_sys' => 'All',
            'platform' => 'All',
        ];

        // Make HTTP POST request
        $response = Http::withHeaders(['Accept' => 'application/json'])
            ->post("{$this->bugzillaUrl}/rest/bug", $params);

        if ($response->successful()) {
            return response()->json(['status' => 'Submitted successfully'], 200);
        }

        return response()->json(['status' => 'Submission failed', 'error' => $response->json()], 400);
    }

}