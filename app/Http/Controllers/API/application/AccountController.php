<?php

namespace App\Http\Controllers\API\application;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\APIController as APIController;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use Carbon\Carbon;

class AccountController extends Controller
{
	/**
     * Create a new CompanyController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }
    /**
     * Display a User by session.
     *
     * @return \Illuminate\Http\Response
     */
    public function session()
    {
        $userdata=Auth::User();
        if ($userdata)
        {
            $userdata->LoadForShow();

            $data = [
                'user'=> $userdata->toarray(),
            ];
            return APIController::json_success($data);
        }
        else
        {
            return APIController::json_error_message('User not found',[],404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return APIController::json_error_message('Route not found',404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return APIController::json_error_message('Route not found',404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return APIController::json_error_message('Route not found',404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return APIController::json_error_message('Route not found',404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return APIController::json_error_message('Route not found',404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $userdata = Auth::User();
        $rules = array(
            'email' => 'required|email|max:255|unique:users,id,'.$userdata->id,
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return APIController::json_error_message('Validation Failed',$validator->errors(),422);
        }
        else
        {
            $request['password']=$userdata->password;
            $request['company_id']=$userdata->company->id;
            
            $userdata->update($request->all());

            $userdata->LoadForShow();
    
            $data = [
                'user'=> $userdata->toarray(),
            ];
            return APIController::json_success($data);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $userdata = Auth::User();
        $rules = array(
            'password' => 'required|min:6|confirmed',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return APIController::json_error_message('Validation Failed',$validator->errors(),422);
        }
        else
        {
            $userdata->password=bcrypt($request['password']);
            
            $userdata->save();

            $userdata->LoadForShow();
    
            $data = [
                'user'=> $userdata->toarray(),
            ];
            return APIController::json_success($data);
        }
    }

    /**
     * Update the agreement_flag resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setagreement_flag(Request $request)
    {
        $userdata = Auth::User();
        if ($userdata)
        {
            $userdata->agreement_flag=1;
            $userdata->agreement_time=Carbon::now()->format('Y-m-d H:i:s');;
            $userdata->save();
            return APIController::json_success([]);
        }
        else
        {
            return APIController::json_error_message('User not found',[],404);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return APIController::json_error_message('Route not found',404);
    }
    
}
