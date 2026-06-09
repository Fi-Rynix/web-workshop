<?php

namespace App\Http\Controllers;

class SimpleController extends Controller
{
    public function plain()
    {
        return response('Plain text - ' . date('H:i:s'));
    }

    public function blade()
    {
        return view('test-simple');
    }

    public function layout()
    {
        return view('test-layout');
    }
}