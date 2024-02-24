<?php

use App\Events\SendChatMessage;
use App\Models\Chat;
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




Route::middleware([])->post('/chat/{user}', function (Request $request, User $user) {

    // event(new SendChatMessage(
    //     $user,
    //     $request->message
    // ));

    // $chats = $user->chats;
    $pivotIds = collect(User::query()->find(1)->chats)->map(fn ($item) => $item->pivot->chat_id);
    $isSubbed = $pivotIds->some(
        fn ($id) =>
        User::query()->find(1)->chats()->find($id)

    );

    if (!$isSubbed) {
        Chat::query()->create()->users()->sync([$user->id, auth()->user()->id]);
    }
    SendChatMessage::dispatch(
        $user,
        $request->message
    );
    return new JsonResponse([
        "data" => [
            "message" => $request->message
        ]
    ]);
});

Route::middleware(['auth:sanctum'])->get('/user/{user}', function (Request $request, User $user) {
    return new JsonResponse([
        "data" => $user
    ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
