<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagosEmpleadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
}
