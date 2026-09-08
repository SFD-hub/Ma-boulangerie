<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AProposController extends Controller
{
    public function index(): View
    {
        return view('a-propos.index');
    }
}
