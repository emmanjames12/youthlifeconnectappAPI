<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        $responseData = [
            'status' => 'error',
            'message' => 'Registration Failed',
            'data' => null
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:10|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|max:50'
        ]);

        if ($validator->fails()) {
            $responseData['message'] = $validator->errors()->first();
            return response()->json($responseData, 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email, 
            'password' => Hash::make($request->password)
        ]);

        if ($user) {
            $responseData['status'] = 'success';
            $responseData['message'] = 'Registration Successful';
            $responseData['data'] = $user;
            return response()->json($responseData, 201);
        }

        return response()->json($responseData, 500);
    }

    public function login(Request $request) {
        $responseData = [
            'status' => 'error',
            'message' => 'Registration Failed',
            'data' => null
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|max:50'
        ]);

        if ($validator->fails()) {
            $responseData['message'] = $validator->errors()->first();
            return response()->json($responseData, 400);
        }

        $credentials = request(['email', 'password']);

        if(Auth::attempt($credentials)) {
            $user = $request->user();
            $user->tokens()->delete();

            $responseData = [
                'status' => 'success',
                'message' => 'Successful Login',
                'data' => [
                    'token' => $user->createToken(Auth::user())->plainTextToken,
                    'user' => $user
                ]
            ];
            return response()->json($responseData, 200);
        }

        return response($responseData, 400);
    }

    public function logout(Request $request)
    {
        $responseData = [
            'status' => 'fail',
            'message' => 'Unsuccessful Logout',
            'data' => null
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        
        if ($validator->fails()) {
            $responseData['message'] = $validator->errors()->first();
            return response($responseData, 400);
        }

        $credentials = request(['email']);

        $user = User::where('email', $credentials['email'])->first();

        if(!$user) return response()->json($responseData, 400);

        $user->tokens()->delete();

        $responseData = [
                    'status' => 'success',
                    'message' => 'Successful Logout',
                    'data' => null
                ];

        return response($responseData, 200);

    }
  
}
