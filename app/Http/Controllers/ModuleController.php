<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::with('moduleRoles')->get();

        return response()->json([
            'status' => 'success',
            'data' => $modules
        ]);
    }
}
