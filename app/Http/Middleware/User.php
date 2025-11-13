<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class User
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
        if (auth()->check() && auth()->user()->blocked) {        

            $redirect_to = "";
            if(auth()->user()->user_role == 'admin' || auth()->user()->user_role == 'staff'){
                $redirect_to = "admin.login";
            }else{
                $redirect_to = "login";
            }

            auth()->logout();         
   
            $message = "Your account has been deleted or doesnt exist.";      
            // Create custom message later  
            
            return redirect()->route($redirect_to)->withEmodal($message)->withErrors($message);            
            
        }
        
        if (Auth::check()){ 
            if(auth()->user()->user_role == 'admin' || Auth::user()->user_role == 'user') {
                return $next($request);

            }elseif( auth()->user()->user_role == 'staff'){
                return redirect()->route('admin.index');
            }else{
                return redirect()->route('index');
            }
        }
        else{
            session(['link' => url()->current()]);
            return redirect()->route('login');
        }
    }
}
