<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\ViewController as ViewController;
use Auth;

class SiteStats
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
        $user_id = Auth::user()->id;
        if($user_id)
        {
            ViewController::add($user_id);
        }
        return $next($request);
    }
}
