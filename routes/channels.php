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

// Chat room channels - users can only join rooms they are part of
Broadcast::channel('chat.room.{roomId}', function ($user, $roomId) {
    // Check if user is part of this chat room
    // You would implement your own logic here based on your chat room structure
    return true; // For now, allow all authenticated users
});

// Private channels for chat rooms
Broadcast::channel('private-chat.room.{roomId}', function ($user, $roomId) {
    // Check if user is part of this chat room
    // You would implement your own logic here based on your chat room structure
    return true; // For now, allow all authenticated users
});

// Presence channels for showing who is online in a chat room
Broadcast::channel('presence-chat.room.{roomId}', function ($user, $roomId) {
    // Check if user is part of this chat room
    // You would implement your own logic here based on your chat room structure
    return [
        'id' => $user->id,
        'name' => $user->nombre . ' ' . $user->apellido,
        'email' => $user->email,
    ];
}); 