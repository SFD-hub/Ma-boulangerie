<?php

namespace App\Http\Controllers;

use App\Support\CaisseDuJour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaisseController extends Controller
{
    public function index(Request $request): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $date = $request->query('date');
        $date = ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) ? $date : Carbon::today()->toDateString();

        $resume = CaisseDuJour::resume($boulangerie_id, $date);

        return view('caisse.index', array_merge(['date' => $date], $resume));
    }
}
