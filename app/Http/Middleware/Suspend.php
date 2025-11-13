<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Suspend
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
        if (auth()->check() && auth()->user()->suspend) {        

            $redirect_to = "";
            if(auth()->user()->user_role == 'admin' || auth()->user()->user_role == 'staff'){
                $redirect_to = "admin.login";
            }else{
                $redirect_to = "login";
            }

            auth()->logout();         

            $message = "Your account has been suspended. Contact admin for re-activation.";      
            // Create custom message later  
            
            return redirect()->route($redirect_to)->withEmodal($message)->withErrors($message);            
            
        }
        return $next($request);
    }
}
