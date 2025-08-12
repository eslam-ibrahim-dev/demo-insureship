<?php

namespace App\Http\Controllers;

use App\Services\QuickbooksService;
use Illuminate\Http\Request;
use QuickBooksOnline\API\DataService\DataService;

class QuickbooksController extends Controller
{
    protected $quickbooksService;

    public function __construct(QuickbooksService $quickbooksService)
    {
        $this->quickbooksService = $quickbooksService;
    }
    public function authPage()
    {
        return response()->json([
            'authUrl' => $this->quickbooksService->getAuthUrl(),
        ]);
    }

    public function authConnect(Request $request)
    {
        $code = $request->query('code');
        $realmId = $request->query('realmId');

        if (!$code || !$realmId) {
            return response()->json(['error' => 'Missing required query params'], 400);
        }

        try {
            $this->quickbooksService->handleAuthCallback($code, $realmId);

            return response()->json([
                'message' => 'QuickBooks authorization successful',
                'status' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to exchange token: ' . $e->getMessage()
            ], 500);
        }
    }
}
