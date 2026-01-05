<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Champions League API', 'docs' => '/api/v1']);
});

Route::get('/health', function () {
    return 'healthy';
});
