<?php

namespace App\Http\Controllers;

class GateController extends Controller
{
    public function __invoke()
    {
        return view('gate.scanner');
    }
}
