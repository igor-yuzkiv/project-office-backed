<?php

namespace App\Http\CliApi\Controllers;

class TestController
{
    public function index()
    {
        $user = auth()->user();
        return response()->json([
            'test' => $user->toArray(),
        ]);
    }
}
