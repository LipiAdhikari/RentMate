<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DocsController extends Controller
{
    /**
     * Show the documentation page.
     */
    public function index()
    {
        return view('docs.index');
    }
}
