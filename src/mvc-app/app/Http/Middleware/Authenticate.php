<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $userNetLogin = explode('\\', $_SERVER['AUTH_USER'])[1];
        $user = User::with(['getAreasRoles', 'userLeaveGroup', 'userRoles'])->where('UD_NetLogin', $userNetLogin)->first();
        if (empty($user)) {
            return redirect('/');
        }
        $user->getUserRoleDetail();

        Auth::login($user);
        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('unauthorized-error');
        }
    }
}
