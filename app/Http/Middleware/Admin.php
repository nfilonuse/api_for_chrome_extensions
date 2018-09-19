<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Admin
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
        if (Auth::guard("admin_users")->user())
        {
            return $next($request);
        }
        else
        {
            session()->flash('msg-danger', 'You must log-in as Admin');
            return redirect(url('admin/login'));
        }
    }
}
