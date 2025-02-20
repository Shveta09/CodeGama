<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 404);
        }

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,  // Initial balance
        ]);
        

        return response()->json(['message' => 'User registered and wallet', 'user' => $user, 'wallet' => $wallet], 200);
    }

    public function login(Request $request)
    {
       
        if ($token = Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['token' => $token], 200);
        }

        return response()->json(['message' => 'Invalid user name or password'], 401);
    }

    public function profile()
    {
        $user = Auth::user();
        return response()->json($user);
    }

}