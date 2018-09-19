<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;

use App\Http\Controllers\API\APIController as APIController;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use App\Models\User;
use Carbon\Carbon;

class APIRegisterController extends Controller
{

	/**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Register user to application.
     *
     * @param  array  $data
     * @return api_token string
     * @return User
     */
    public function register(request $request)
    {
        $rules = array(
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'agreement_flag' => 'required|boolean|accepted',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return APIController::json_error_message('Validation Failed',$validator->errors(),422);
        }
        else
        {
            $company=User::create_empty_company('Company for '.$request['email']);
            $request['agreement_time']=Carbon::now()->format('Y-m-d H:i:s');
            $request['password']=bcrypt($request['password']);
            $request['company_id']=$company->id;
            $request['role_id']=100;
            $request['trial_ends_at']=Carbon::now()->addDays(6)->format('Y-m-d H:i:s');;
            
            $user=User::create($request->all());

            return APIController::AuthResponce($user);
        }
    }
}    
?>
