<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;   

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {   
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User successfully registered',
            'data' => $user
        ], 201); 
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Incorrect email or password.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'User successfully logged in',
            'data' => [
                'user' => Auth::user(),
                'token' => $token
            ]
        ], 200);
    }

}