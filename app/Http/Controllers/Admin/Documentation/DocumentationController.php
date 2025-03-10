<?php

namespace App\Http\Controllers\Admin\Documentation;

use App\Services\Admin\Documentation\DocumentationService;
use App\Http\Controllers\Controller;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    protected $documentationService;
    public function __construct(DocumentationService $documentationService){
        $this->documentationService = $documentationService;
    }
    public function apiPage(Request $request){
        $data = $request->all();
        $returnedData = $this->documentationService->apiPage($data);
        return $returnedData;
    }
}
