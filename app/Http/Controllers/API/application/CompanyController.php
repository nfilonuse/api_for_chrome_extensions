<?php

namespace App\Http\Controllers\API\application;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\APIController as APIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Facades\JWTAuth;
use Auth;
use App\Models\Company;

class CompanyController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies=Company::all();
		$data = [
			'companys'=> $companies->makeVisible('id')->toarray(),
        ];
		return APIController::json_success($data);
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
        $rules = array(
            'name'       => 'required',
        );
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            $data=[
                'message'   => 'Validation Failed',
                'errors'        => $validator->errors()
            ];
            return APIController::json_error_message('',422);
        }
        else
        {
            $company=Company::create($request->all());
            $data = $company->makeVisible('id')->toarray();
            return APIController::json_success($data);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company=Company::find($id);
		$data = $company->makeVisible('id')->toarray();
		return APIController::json_success($data);
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
    public function update(Request $request, $id)
    {
        $company=Company::find($id);
        if (empty($company)) 
            return APIController::json_error_message('Role not found',404);

        $rules = array(
            'name'       => 'required',
        );
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            $data=[
                'message'   => 'Validation Failed',
                'errors'        => $validator->errors()
            ];
            return APIController::json_error_message('',422);
        }
        else
        {
            $company->update($request->all());
            $data = $company->makeVisible('id')->toarray();
            return APIController::json_success($data);
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
        $company=Company::find($id);
        if ($company)
        {
            $company->delete();
            return APIController::json_empty_success();
        }
        else
        {
            return APIController::json_error_message('Role not found',404);
        }
    }
}
