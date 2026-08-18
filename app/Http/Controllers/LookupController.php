<?php

namespace App\Http\Controllers;

use App\Models\Lookup;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function index(Request $request)
    {
        $query = Lookup::where('is_active', true);
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->get());
    }
}
