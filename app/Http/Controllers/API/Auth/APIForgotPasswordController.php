<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\APIController as APIController;

use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

class APIForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
     */
    use SendsPasswordResetEmails;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getResetToken(Request $request)
    {
        $rules = array(
            'email' => 'required|email|max:255',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return APIController::json_error_message('Validation Failed',$validator->errors(),422);
        }
        else
        {
            $user = User::where('email', $request->input('email'))->first();
            if (!$user) {
                return APIController::json_error_message(trans('passwords.user'),[],404);
            }
            $token = $this->broker()->createToken($user);
            return APIController::json_success(['token' => $token]);
        }
    }
}