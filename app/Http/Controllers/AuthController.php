<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        $payload = $request->only(['email', 'password']);

        $validator = Validator::make($payload, [
            "email" => ["required", "string"],
            "password" => ["required", "string"]
        ]);

        $validator->validate();

        if (!Auth::attempt($payload)) {
            return new JsonResponse([
                "message" => "Invalid credentials"
            ]);
        }

        $user = auth()->user();

        $token = User::query()->find($user->id)->createToken('auth_token')->plainTextToken;


        $response = [
            'status' => 'success',
            'msg' => 'Login successfully',
            'data' => [
                'status_code' => 200,
                'token' => $token,
                'token_type' => 'Bearer',
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_id' => $user->id,
                'user_image' => $user->image,
            ]
        ];

        return response()->json($response, 200);
    }

    public function register(Request $request)
    {
        $payload = [
            "name" => $request->name,
            "email" => $request->email,
            "password" => $request->password,
            "password_confirmation" => $request->password_confirmation
        ];

        Validator::make($payload, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'string', Password::default(), 'confirmed']
        ])->validate();


        $user  =  User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;


        $response = [
            'status' => 'success',
            'msg' => 'Registration successfull',
            'data' => [
                'status_code' => 200,
                'token' => $token,
                'token_type' => 'Bearer',
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_id' => $user->id,
                'user_image' => $user->image,
            ]
        ];

        return response()->json($response, 200);
    }


    public function logout()
    {
        User::query()->find(Auth::id())->tokens()->delete();
        return new JsonResponse([
            "message" => "logged out"
        ]);
    }
}
