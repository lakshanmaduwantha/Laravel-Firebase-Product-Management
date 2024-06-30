<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            Log::error('Registration validation failed', ['errors' => $validator->errors()]);
            return response()->json($validator->errors(), 400);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('LaravelAuthApp')->accessToken;

            return response()->json(['token' => $token], 200);
        } catch (\Exception $e) {
            Log::error('Registration failed', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            try {
                $user = Auth::user();
                $token = $user->createToken('LaravelAuthApp')->accessToken;
                return response()->json(['token' => $token], 200);
            } catch (\Exception $e) {
                Log::error('Login token creation failed', ['exception' => $e->getMessage()]);
                return response()->json(['error' => 'Login token creation failed'], 500);
            }
        } else {
            Log::error('Unauthorized login attempt', ['email' => $request->email]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            Log::error('Logout failed', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }
}
