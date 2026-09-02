<?php
namespace App\Http\Middleware;
use Closure;use Illuminate\Http\Request;use Symfony\Component\HttpFoundation\Response;
class RoleMiddleware {public function handle(Request $request,Closure $next,...$roles):Response{if(!$request->user()||!in_array($request->user()->role?->slug,$roles,true)){abort(403,'You are not authorised for this area.');}return $next($request);}}
