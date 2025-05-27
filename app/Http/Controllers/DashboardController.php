<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $METABASE_SITE_URL = "http://localhost:3000"; // o el dominio donde corre Metabase
        $METABASE_SECRET_KEY = "19c1faba8d589bb502fcab95b9f0d83f9bbccf03b708c4090d477e8a45eedf21";

        $payload = [
            "resource" => ["dashboard" => 2], // Cambia el 2 por el ID real del dashboard
            "params" => new \stdClass(),
            "exp" => time() + (10 * 60)
        ];

        $token = JWT::encode($payload, $METABASE_SECRET_KEY, 'HS256');
        $iframeUrl = $METABASE_SITE_URL . "/embed/dashboard/" . $token . "#bordered=true&titled=true";

        return view('dashboard', compact('iframeUrl'));
    }
}
