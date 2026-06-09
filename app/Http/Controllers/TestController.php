<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    public function test()
    {
        return 'Test OK - ' . date('H:i:s');
    }
}