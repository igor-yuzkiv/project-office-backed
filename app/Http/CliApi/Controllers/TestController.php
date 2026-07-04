<?php

namespace App\Http\CliApi\Controllers;

class TestController
{
    public function index()
    {
        return response()->json([
            'test' => 1,
        ]);
    }
}
