<?php

namespace App\Http\Middleware;

use App\Option;
use Closure;
use Route;

class siteMaintenance
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
        $site_enabled = Option::where('name', 'site_enabled')->first()->value;
        $route_name = Route::currentRouteName();

        if($site_enabled==0 && $route_name != 'maintenance')
        {
            return  redirect(route('maintenance'));
        }

        return $next($request);

    }
}
