<?php

use App\Events\RealtimeEvent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test', function () {
    event(new \App\Events\RealtimeEvent('test Role',3,"test Realtime events"));
    return 'done';
});
