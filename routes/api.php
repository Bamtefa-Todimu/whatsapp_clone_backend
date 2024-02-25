<?php

use App\Events\SendChatMessage;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::middleware([])
    ->group(function () {

        Route::post('/chat/{user}', function (Request $request, User $user) {

            $pivotIds = collect(User::query()->find(1)->chats)->map(fn ($item) => $item->pivot->chat_id);
            $isSubbed = $pivotIds->some(
                fn ($id) =>
                User::query()->find($user->id)->chats()->find($id)

            );


            if (!$isSubbed) {
                $chat = Chat::query()->create();
                // dump($chat->id);
                $chat->users()->sync([$user->id, 1]);
            }
            SendChatMessage::dispatch(
                $user,
                $request->message
            );

            $ChatMessage = ChatMessage::query()->create([
                "chat_id" => $chat->id,
                "user_id" => 1,
                "message" => $request->message
            ]);

            return new JsonResponse([
                "data" => [
                    "chatMessage" => $ChatMessage
                ]
            ]);
        });

        Route::get('/user/{user}', function (Request $request, User $user) {
            return new JsonResponse([
                "data" => $user
            ]);
        });

        Route::get('/chats', function (Request $request) {
            // $userId= Auth::guard('api')->user()?->id;
            $userId = 1;

            $chats  = User::query()->find(1)->chats;

            $users  = $chats->map(fn ($chat) => Chat::query()->find($chat->id)->users()->where('id', '!=', $userId)->get());

            return new JsonResponse([
                "active chats" => $users
            ]);
        });

        Route::get('/users', function (Request $request) {
            return  new JsonResponse([
                "data" => (User::all())
            ]);
        });
    });






Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
