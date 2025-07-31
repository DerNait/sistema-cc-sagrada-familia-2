<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $params = [

        ];

        return view('component',[
            'component' => 'Home',
            'params' => $params,
        ]);
    }
}
