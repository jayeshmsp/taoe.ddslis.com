<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class securityTokenCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return $next($request);
        }
        $securityToken = isset($_COOKIE['securityToken'])?$_COOKIE['securityToken']:'';
        if ($request->has('tag') && $request->get('tag') == $securityToken ) {
            $url = $request->url().'?tage='.$securityToken;
            return $next($request);
        }

        $request->session()->flush();
        $request->session()->regenerate();

        return redirect('login')
                ->with('error','Your token might be expire or not found please login again.');
    }
}
