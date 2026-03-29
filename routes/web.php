<?php

use Illuminate\Support\Facades\Route;

Route::get('/api', function () {
    return view('welcome');
});

Route::get('/api/healthz', function() {
    return response()->json(['statusss' => 'oksss']);
});
