<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\APIController as APIController;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use App\Models\User;
use App\Models\UserSocnetworks;

class APILoginController extends Controller
{

	/**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest');
    }

    /**
     * Auth user to application.
     *
     * @param  array  $data
     * @return api_token string
     * @return User
     */
    public function auth(request $request)
    {
        $rules = array(
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return APIController::json_error_message('Validation Failed',$validator->errors(),422);
        }
        else
        {
            $credentials = $request->only('email', 'password');
            return APIController::AuthResponce($request->only('email', 'password'),false);
        }
    }

    /**
     * Auth user to application by Social.
     *
     * @param  array  $data
     * @return api_token string
     * @return User
     */
    public function auth_social(request $request)
    {
        $rules = array(
            'email' => 'required|email|max:255',
            'social_id' => 'required',
            'social_type' => 'required|numeric',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return APIController::json_error_message('Validation Failed',$validator->errors(),422);
        }
        else
        {
            //check if user already register with this social
            $usersocial=UserSocnetworks::where('social_id',$request->only('social_id'))
                                        ->where('social_type',$request->only('social_type'))
                                        ->first();
            if ($usersocial)
            {
                $user=User::find($usersocial->user_id);
            }
            else
            {
                //check if user already register with this email
                $user=User::where('email',$request->only('email'))->first();
                if ($user)
                {
                    //add social to db
                    $request['user_id']=$user->id;
                    UserSocnetworks::create($request->all());
                    
                }
                else
                {
                    //create user
                    $company=User::create_empty_company('Company for '.$request['email']);
                    $request['agreement_flag']=1;
                    $request['agreement_time']=Carbon::now()->format('Y-m-d H:i:s');
                    $request['password']=bcrypt($request['password']);
                    $request['company_id']=$company->id;
                    $user=User::create($request->all());

                    //add social to db
                    $request['user_id']=$user->id;
                    UserSocnetworks::create($request->all());
                    
                }
    
            }
            
            return APIController::AuthResponce($user);
        }
    }
    /**
     * Logout user to application.
     *
     * @return empty
     */
    public function logout()
    {
        return APIController::LogoutResponce();
    }
}    
?>
