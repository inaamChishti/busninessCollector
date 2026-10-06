<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request$request,Closure$next):Response{$configured=(string)Setting::valueOf('api_token','');$provided=(string)$request->bearerToken();abort_if($configured===''||$provided===''||!hash_equals($configured,$provided),401,'Invalid API token.');return$next($request);}
}
