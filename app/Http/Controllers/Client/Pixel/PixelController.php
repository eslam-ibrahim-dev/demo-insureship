<?php

namespace App\Http\Controllers\Client\Pixel;

use App\Models\SubClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ClientLoginPermission;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use app\services\client\pixel\PixelService;

class PixelController extends Controller
{
    protected $pixelService;

    public function __construct(PixelService $pixelService)
    {
        $this->pixelService = $pixelService;
    }

    public function viewTrackingPixelsPage(Request $request)
    {
        $response = $this->pixelService->getTrackingPixelsData($request);
        return response()->json($response['data'], $response['status']);
    }
}
