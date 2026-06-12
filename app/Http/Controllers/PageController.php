<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
  public function home()
{
    $nom = "Serigne";

    return view('home', compact('nom'));
}
}
