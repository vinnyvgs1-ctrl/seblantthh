<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.heatmap', function ($user) {
    return true; // Solo usuarios autenticados llegarán aquí
});

Broadcast::channel('admin.fleet', function ($user) {
    return true;
});
