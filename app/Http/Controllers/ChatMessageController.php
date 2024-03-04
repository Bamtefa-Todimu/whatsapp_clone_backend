<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Requests\UpdateChatMessageRequest;
use App\Models\chat_user;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $pivotIds = collect(User::query()->find(auth()->user()->id)->chats)->map(fn ($item) => $item->pivot->chat_id);

        $Senders = chat_user::whereIn('chat_id', $pivotIds)->WhereNotIn('user_id', [Auth::id()])->distinct()->orderBy('chat_id', 'DESC')->get();
        // $pivotUsers = collect(User::query()->find(auth()->user()->id)->chats)->map(fn ($item) => $item->pivot->user_id);
        $messages = ChatMessage::whereIn('chat_id', $pivotIds)->latest()->get(['user_id', 'chat_id', 'message', 'created_at'])->groupBy('chat_id');
        $results = [];
        $finalresults = [];
        foreach ($messages as $group) {
            $results[] = head($group);
        }
        foreach ($results as $msg) {
            $finalresults[] = head($msg);
        }

        $newMsg = collect($finalresults)->map(fn ($mg, $id) => [...collect($mg), ...["user" => User::query()->find($Senders->where('chat_id', $mg->chat_id)->first()?->user_id)]]);
        // return $finalresults;
        return new JsonResponse([
            // "pivotIds" => $pivotIds,
            // "newMg" => $newMsg,
            "senders" => $Senders,
            "senders" => collect($Senders)->where('chat_id', 11)->first(),
            "data" => $newMsg
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatMessageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatMessageRequest $request, ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatMessage $chatMessage)
    {
        //
    }
}
