<?php

namespace App\Http\Controllers\API\application;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\APIController as APIController;
use Auth;
use App\Models\Citation;
use App\Models\PatentWork;
use Illuminate\Http\Request;

class PatentController extends Controller
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
        $user=Auth::User();
        $citations=Citation::where('user_id',$user->id)->get();
		$data = [
			'citations'=> $citations->toarray(),
        ];
		return APIController::json_success($data);
    }

    /**
     * Scane patent a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function scane(Request $request)
    {
        $rules = array(
            'PatentNumber'       => 'required',
            'DownloadUrl'       => 'required',
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
            $data=[
                'PattentNumber' => $request->PattentNumber,
                'DownloadUrl'   => $request->DownloadUrl,
            ]
            $res=PatentWork::getStatus();
            if ($res=PatentWork::getStatus())
            {
                if ($res==PatentWork::psNone)
                {
                    PatentWork::ParsePattent($data);
                    return APIController::json_empty_success();
                }
            }

        }
    }

    /**
     * Scane patent a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $rules = array(
            'PatentNumber'       => 'required',
            'SearchString'       => 'required',
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
            $data=[
                'PattentNumber' => $request->PattentNumber,
                'SearchString'   => $request->SearchString,
            ]
            $res=PatentWork::getStatus();
            if ($res=PatentWork::getStatus())
            {
                if ($res==PatentWork::psDone)
                {
                    $res_data=PatentWork::search($data);
                    return APIController::json_success($res_data);
                }
                else
                    return APIController::json_error_message('Patent stil in the process of recognition',404);
                
            }
            return APIController::json_error_message('Patent not found',404);
        }
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return APIController::json_error_message('Route not found',[],404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        return APIController::json_error_message('Route not found',[],404);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function show(Module $module)
    {
        return APIController::json_error_message('Route not found',[],404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function edit(Module $module)
    {
        return APIController::json_error_message('Route not found',[],404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Module $module)
    {
        return APIController::json_error_message('Route not found',[],404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function destroy(Module $module)
    {
        return APIController::json_error_message('Route not found',[],404);
    }
}
