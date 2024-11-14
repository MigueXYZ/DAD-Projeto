<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CheckRememberToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = User::where('remember_token', hash('sha256', $request->bearerToken()))->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Authenticate the user for this request
        Auth::login($user);

        return $next($request);
    }

}
