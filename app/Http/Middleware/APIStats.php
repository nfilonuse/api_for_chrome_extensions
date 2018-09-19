<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\API\mobile\ViewController as ViewController;
use App\Http\Controllers\API\mobile\APIController as APIController;
use App\Models\User;
use Auth;

class APIStats
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
        $user = Auth::guard('api')->user();

        if(!$user)
        {
            return APIController::json_error(18);
        }

        if($user) {
            if ($user->is_active == 0) {
                return APIController::json_error(11);
            }

            if ($user) {
                ViewController::add($user->id);
            }
            return $next($request);
        } else {
            return APIController::json_error(18);
        }
    }
}
