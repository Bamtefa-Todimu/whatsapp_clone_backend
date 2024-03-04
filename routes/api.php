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
use App\Actions\Fortify\PasswordValidationRules;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\UserController;
use App\Models\chat_user;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

use function PHPSTORM_META\map;

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

        Route::post('/chat/{user}', [ChatController::class, 'create']);

        Route::get('/user/{user}', [UserController::class, 'show']);

        Route::get('/chats', [ChatController::class, 'index']);

        Route::get('/chat/{chat}/message', [ChatController::class, 'show']);

        Route::get('/users/messages', [ChatMessageController::class, 'index']);

        Route::get('/users', [UserController::class, 'index']);

        Route::put('/users/update', [UserController::class, 'update']);

        Route::get('/testSockets', function (Request $request) {
            event(new SendChatMessage(User::query()->get()->first(), "Hello world", 1));
            return  response("Done", 200);
        });
    });


Route::post('/login', [AuthController::class, 'login']);


Route::post('/register', [AuthController::class, 'register']);


Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);




Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->get('/getStatus', function (Request $request) {
    return new JsonResponse([
        "data" => "its working "
    ]);
});
