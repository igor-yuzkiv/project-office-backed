<?php

namespace App\Http\Controllers;

use App\Http\Resources\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController
{
    public function user()
    {
        return UserResource::make(Auth::user());
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return UserResource::make(Auth::user());
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }
}
