<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    //

    public function index()
    {
        return  new JsonResponse([
            "data" => (User::all())
        ]);
    }
    public function show(User $user)
    {
        return new JsonResponse([
            "data" => $user
        ]);
    }

    public function update(User $user, Request $request)
    {
        $input  = $request->only(['name', 'email', 'image']);
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // Rule::unique('users')->ignore($user->id),
            ],
            'image' => [
                'string',
            ],
        ])->validate();


        User::where('id', Auth::id())->update([
            'name' => $input['name'],
            // 'email' => $input['email'],
            'image' => $input['image'],
        ]);

        // dump($input['image']);

        return new JsonResponse([
            "data" => [
                "user_email" => $input['email'],
                "user_name" => $input['name'],
                "user_image" => $input['image'],
            ]
        ]);
    }
}
