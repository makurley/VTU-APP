<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ApiAuth
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

        $request->headers->set('Accept', 'application/json');
        try {
            $headers = $request->headers;
            $apikey = $headers->get('Authorization');
            $apikey = str_replace('Token ', '', $apikey);
            if (!$apikey) {
                return response()->json(['status' => 'error', 'message' => 'Your Authorization Header must Be Your API KEY.'], 401);
            }

            $user = User::where('api_key', $apikey)->whereSuspend(0)->first();

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Invalid Access Token or Account Disabled'], 401);
            }
            if ($user->blocked == 1) {
                return response()->json(['status' => 'error', 'message' => 'Invalid Access Token or Account has been blocked. Please Contact admin'], 401);
            }
            // if ($user->developer != 2) {
            //     return response()->json(['status' => 'error', 'message' => 'You Need to upgrade your account to a developer Account to access our API. Please Upgrade'], 401);
            // }

            // You can pass the authenticated user to the next middleware or controller
            // Modify this line to suit your needs
            return $next($request, $user);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Your Authorization Code is not Correct'], 401);
        }

    }
}
