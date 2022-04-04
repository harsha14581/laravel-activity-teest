<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    function login(Request $request){
       $credentials =  $request->validate([
            'email_id' => 'required|string|email',
            'password' => 'required|string' 
        ]);

        if (Auth::attempt($credentials)) {
            return response()->json([
                'code' => 200,
                'error' => false,
                'message' => 'Successfully logged in',
                'data' => [[
                    'access_token' => Auth::user()->createToken(Str::random(50))->accessToken,
                    'user_details' => Auth::user()
                ]]
            ]);
        }else{
            return response()->json([
                'code' => 422,
                'error' => true,
                'message' => 'Invalid credentials',
                'data' => []
            ]);
        }
    }

    function register(Request $request){
        $data = $request->validate([
            'email_id' => 'required|string|email|unique:users,email_id',
            'name' => 'required|string',
            'password' => 'required|string'
        ]);
        
        $data['password'] = bcrypt($data['password']);
        $data['role'] = 'ADMIN';
        User::create($data);
        return response()->json([
            'code' => 200,
            'error' => false,
            'message' => 'successfully create user',
            'data' => []
        ]);
    }
}
