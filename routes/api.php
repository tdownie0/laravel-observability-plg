<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/log-client-error', function (Request $request) {
    Log::error('Frontend Error', [
        'message' => $request->input('message'),
        'stack' => $request->input('stack'),
        'component_name' => $request->input('componentName'),
        'lifecycle_hook' => $request->input('lifecycleHook'),
        'context' => $request->input('context'),
        'url' => url()->previous(),
        'user_agent' => $request->header('User-Agent'),
        'ip_address' => $request->ip(),
        'user_id' => $request->input('userId'),
    ]);

    return response()->json(['status' => 'success'], 200);
});

Route::post('/track-page-visit', function (Request $request) {
    Log::channel('loki')->info('Page Visit', array_merge($request->all(), [
        'event' => 'page_visit', 
        'ip_address' => $request->ip(),
    ]));

    return response()->json(['status' => 'success']);
});
