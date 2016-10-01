<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Response;

class MainController extends Controller
{
    /**
     * Show the application main page.
     *
     * @return Response
     */
    public function index()
    {
        return view('welcome');
    }
}
