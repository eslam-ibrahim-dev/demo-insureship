<?php

namespace App\Http\Controllers\Admin\Offer;

use App\Services\Admin\Offer\OfferService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class OfferController extends Controller
{
    protected $offerService;
    public function __construct(OfferService $offerService){
        $this->offerService = $offerService;
    }
    public function indexPage(){
        $returnedData = $this->offerService->indexPage();
        return $returnedData;
    }

    public function newPage(Request $request){
        $data = $request->all();
        $returnedData = $this->offerService->newPage($data);
        return $returnedData;
    }

    public function newSubmit(Request $request){
        $data = $request->all();
        $returnedData = $this->offerService->newSubmit($data , $request);
        return $returnedData;
    }

    public function detailPage(Request $request , $offer_id){
        $data = $request->all();
        $returnedData = $this->offerService->detailPage($data , $offer_id);
        return $returnedData;
    }


    public function updateSubmit(Request $request , $offer_id){
        $data = $request->all();
        $returnedData = $this->offerService->updateSubmit($data , $request , $offer_id);
        return $returnedData;
    }

}
