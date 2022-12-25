<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required','string','max:255'],
                'email' => ['email','required','unique:users'],
                'username' => ['required','string','max:255','unique:users'],
                'password' => ['required','string',new Password],
                'phone' => ['nullable','string','max:16'],
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type'    => 'Bearer',
                'user' => $user
            ], 'User Registered');

        } catch(Exception $error){
            return ResponseFormatter::success([
               'message' => 'Something went wrong',
               'error' => $error
            ], 'Authentication error', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credentials = request(['email','password']);

            if(!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'unauthorized'
                ], 'Authentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();

            if(!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatted::success([
                'access_token' => $tokenResult,
                'token_type' =>'Bearer',
                'user' => $user
            ], 'Authenticated'); 

        } catch (Exception $error ) {
            return ResponseFormatter::error([
                'message' => 'something went wrong',
                'error' => $error
            ], 'Login Failed', 500);
        }
    }
}
