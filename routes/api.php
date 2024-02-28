<?php

use App\Events\SendChatMessage;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Validation\Rules\Password;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware(['auth:sanctum'])
    ->group(function () {

        Route::post('/chat/{user}', function (Request $request, User $user) {

            $pivotIds = collect(User::query()->find(auth()->user()->id)->chats)->map(fn ($item) => $item->pivot->chat_id);
            $isSubbed = $pivotIds->some(
                fn ($id) =>
                User::query()->find($user->id)->chats()->find($id)

            );


            if (!$isSubbed) {
                $chat = Chat::query()->create();
                // dump($chat->id);
                $chat->users()->sync([$user->id, auth()->user()->id]);

                broadcast(new SendChatMessage(
                    $user,
                    $request->message,
                    [...collect(auth()->user()), ...["message" => $request->message]]
                ))->toOthers();

                $ChatMessage = ChatMessage::query()->create([
                    "chat_id" => $chat->id,
                    "user_id" => auth()->user()->id,
                    "message" => $request->message
                ]);

                return new JsonResponse([
                    "data" => [
                        "chatMessage" => $ChatMessage
                    ]
                ]);
            } else {
                $pivotIds->each(
                    function ($id) use ($user, $request) {

                        if (User::query()->find($user->id)->chats()->where('id', $id)->count()) {
                            dump($id);

                            broadcast(new SendChatMessage(
                                $user,
                                $request->message,
                                [...collect(auth()->user()), ...["message" => $request->message]]
                            ))->toOthers();

                            // SendChatMessage::dispatch(
                            //     $user,
                            //     $request->message,
                            //     [...collect(auth()->user()), ...["message" => $request->message]]
                            // );

                            $ChatMessage = ChatMessage::query()->create([
                                "chat_id" => $id,
                                "user_id" => auth()->user()->id,
                                "message" => $request->message
                            ]);

                            return new JsonResponse([
                                "data" => [
                                    "chatMessage" => $ChatMessage
                                ]
                            ]);
                        }
                    }

                );
            }
        });

        Route::get('/user/{user}', function (Request $request, User $user) {
            return new JsonResponse([
                "data" => $user
            ]);
        });

        Route::get('/chats', function (Request $request) {
            // $userId= Auth::guard('api')->user()?->id;
            $userId = auth()->user()->id;

            $chats  = User::query()->find(auth()->user()->id)->chats()->latest()->get();

            // $latestMessages = $chats->map(fn ($chat) => ChatMessage::where('chat_id', $chat->id)->latest()->get()->first());

            $users  = $chats->map(fn ($chat) => [...collect(Chat::query()->find($chat->id)->users()->where('id', '!=', $userId)->get(['id', 'name', 'email', 'updated_at', 'created_at'])->first()), ...collect(ChatMessage::where('chat_id', $chat->id)->latest()->get(['message'])->first())]);

            return new JsonResponse([
                "active_chats" => $users,
                // "latest_messages" => $latestMessages
            ]);
        });

        Route::get('/chat/{chat}/message', function (Request $request, Chat $chat) {
            $messages = ChatMessage::where('chat_id', $chat->id)->get(['user_id', 'message', 'created_at']);
            return new JsonResponse(["data" => $messages]);
        });

        Route::get('/users', function (Request $request) {
            return  new JsonResponse([
                "data" => (User::all())
            ]);
        });

        Route::get('/testSockets', function (Request $request) {
            event(new SendChatMessage(User::query()->get()->first(), "Hello world", 1));
            return  response("Done", 200);
        });
    });


Route::post('/login', function (Request $request) {
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

    // if (Auth::user()) {
    //     return new JsonResponse([
    //         "data" => [
    //             "token" => User::query()->find(Auth::user()->id)->tokens()->where('personal_access_tokens.name', 'auth_token')->first()

    //         ]
    //     ]);
    // }

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
        ]
    ];

    return response()->json($response, 200);
});


Route::post('/register', function (Request $request) {
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
        ]
    ];

    return response()->json($response, 200);
});


Route::post('/logout', function (Request $request) {
    // Auth::user()->tokens()->delete();
    dump(auth()->user()->id);
    User::query()->find(Auth::id())->tokens()->delete();
    return new JsonResponse([
        "message" => "logged out"
    ]);
})->middleware(['auth:sanctum']);




Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->get('/getStatus', function (Request $request) {
    return new JsonResponse([
        "data" => "its working "
    ]);
});
