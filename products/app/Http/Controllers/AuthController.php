<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('LaravelAuthApp')->accessToken;

            return response()->json(['token' => $token], 200);
        } catch (\Exception $e) {
            Log::error('Error during registration: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during registration.'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('LaravelAuthApp')->accessToken;

                return response()->json(['token' => $token], 200);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (\Exception $e) {
            Log::error('Error during login: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during login.'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();
            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (\Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during logout.'], 500);
        }
    }
}
