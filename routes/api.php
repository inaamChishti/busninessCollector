<?php

use App\Http\Controllers\Api\BusinessApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['collector.api','throttle:api'])->prefix('v1')->group(function(){Route::get('/businesses',[BusinessApiController::class,'index']);Route::get('/businesses/{business}',[BusinessApiController::class,'show']);Route::get('/businesses/{business}/emails',[BusinessApiController::class,'emails']);});
