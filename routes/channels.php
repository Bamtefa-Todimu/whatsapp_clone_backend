<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('testing.{id}', function ($user, $id) {
    // dump($user);
    return true;
});


Broadcast::channel('Send-Message.{id}', function ($user, $id) {
    $ids = collect($user->chats)->map(fn ($item) => $item->id);
    return $ids->contains($id);
    // return (int) $user->chats()->find($id) === (int) $id;
});
