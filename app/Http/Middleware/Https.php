<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Https
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(sys_setting('is_https') === 1) {
            if (!$request->secure()){
                // return redirect()->secure($request->getRequestUri());
                return redirect()->secure($request->path());
            }
        }
        return $next($request);
    }
}
