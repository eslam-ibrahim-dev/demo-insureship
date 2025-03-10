<?php

namespace App\Http\Controllers\Admin\Bugs;
use App\Services\Admin\Bugs\BugsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class BugsController extends Controller
{
    protected $bugsService;
    public function __construct(BugsService $bugsService){
        $this->bugsService = $bugsService;
    }
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

    public function listPage()
    {
        $returnedData = $this->bugsService->listPage();
        return $returnedData;
    }

    public function newPage()
    {
        $returnedData = $this->bugsService->newPage();
        return $returnedData;
    }

    public function newSubmit(Request $request)
    {
        $data = $request->all();
        

        $returnedData = $this->bugsService->newSubmit($request);
        return $returnedData;
    }


}
