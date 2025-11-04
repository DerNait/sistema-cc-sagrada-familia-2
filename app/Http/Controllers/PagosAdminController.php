<?php

namespace App\Http\Controllers;

class PagosAdminController extends Controller
{
    public function index()
    {
        return view('component', [
            'component' => 'admin-pagos',
        ]);
    }
}
