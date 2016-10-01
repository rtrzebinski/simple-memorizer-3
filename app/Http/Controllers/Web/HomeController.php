<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Response;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        return view('home');
    }
}
