<?php

namespace App\Http\Controllers;

use App\Models\Chat;

use App\Events\SendChatMessage;
use App\Models\ChatMessage;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;
use App\Models\chat_user;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        // $userId= Auth::guard('api')->user()?->id;
        $userId = auth()->user()->id;

        $chats  = User::query()->find(auth()->user()->id)->chats()->latest()->get();

        // $latestMessages = $chats->map(fn ($chat) => ChatMessage::where('chat_id', $chat->id)->latest()->get()->first());

        $users  = $chats->map(fn ($chat) => [...collect(Chat::query()->find($chat->id)->users()->where('id', '!=', $userId)->get(['id', 'name', 'email', 'updated_at', 'created_at'])->first()), ...collect(ChatMessage::where('chat_id', $chat->id)->latest()->get(['message'])->first())]);

        return new JsonResponse([
            "active_chats" => $users,
            // "latest_messages" => $latestMessages
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, User $user)
    {
        //
        $pivotIds = collect(User::query()->find(auth()->user()->id)->chats)->map(fn ($item) => $item->pivot->chat_id);
        $isSubbed = $pivotIds->some(
            fn ($id) =>
            User::query()->find($user->id)->chats()->find($id)

        );


        if (!$isSubbed) {
            $chat = Chat::query()->create();
            // dump($chat->id);
            $chat->users()->sync([$user->id, auth()->user()->id]);



            $ChatMessage = ChatMessage::query()->create([
                "chat_id" => $chat->id,
                "user_id" => auth()->user()->id,
                "recipient_id" => $user->id,
                "message" => $request->message
            ]);

            broadcast(new SendChatMessage(
                $user,
                $request->message,
                [...collect($ChatMessage), ...["user" => auth()->user()]]
                // [...collect(auth()->user()), ...["message" => $request->message]]
            ))->toOthers();

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



                        // SendChatMessage::dispatch(
                        //     $user,
                        //     $request->message,
                        //     [...collect(auth()->user()), ...["message" => $request->message]]
                        // );

                        $ChatMessage = ChatMessage::query()->create([
                            "chat_id" => $id,
                            "user_id" => auth()->user()->id,
                            "recipient_id" => $user->id,
                            "message" => $request->message
                        ]);

                        broadcast(new SendChatMessage(
                            $user,
                            $request->message,
                            [...collect($ChatMessage), ...["user" => auth()->user()]]
                            // [...collect(auth()->user()), ...["message" => $request->message]]
                        ))->toOthers();

                        return new JsonResponse([
                            "data" => [
                                "chatMessage" => $ChatMessage
                            ]
                        ]);
                    }
                }

            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Chat $chat)
    {
        //
        $messages = ChatMessage::where('chat_id', $chat->id)->get(['user_id', 'message', 'created_at']);
        return new JsonResponse(["data" => $messages]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chat $chat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chat $chat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chat $chat)
    {
        //
    }
}
