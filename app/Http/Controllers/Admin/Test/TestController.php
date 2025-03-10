<?php

namespace App\Http\Controllers\Admin\Test;

use App\Services\Admin\Test\TestService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    protected $testService;
    public function __construct(TestService $testService){
        $this->testService = $testService;
    }
    //
    public function indexPage(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->testService->indexPage($data);
        return $returnedData;
    }

    public function testOrderGetSubclients(Request $request, $client_id)
    {
        $data = $request->all();
        $returnedData = $this->testService->testOrderGetSubclients($data , $client_id);
        return $returnedData;
    }

    public function testOrderGetOffers(Request $request, $subclient_id)
    {
        $data = $request->all();
        $returnedData = $this->testService->testOrderGetOffers($data , $subclient_id);
        return $returnedData;
    }

    public function testSubmit(Request $request)
    {
        $data = $request->all();
        $returnedData = $this->testService->testSubmit($data);
        return $returnedData;
    }

}
