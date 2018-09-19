<?php

namespace App\Http\Controllers\API\application;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\APIController as APIController;
use Auth;
use App\Models\Subscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SubscriptionController extends Controller
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
        $subscriptions=Subscriptions::get();
		$data = [
			'subscriptions'=> $subscriptions->toarray(),
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

    public function payment(Request $request)
    {
        $user=Auth::User();
        $token = $request['token'];
        $plane = $request['subscription_id'];

        $subscription=Subscriptions::find($request['subscription_id']);
        if ($subscription)
        {
            try {
                $user->newSubscription('main', $subscription->stripe_plan)->create($token,[
                        'email' => $user->email
                ]);
                $user=User::find($user->id);
                $user->LoadForShow();
                return APIController::json_error_message('Success',$user->toarray(),200);
            } catch (Exception $e) {
                return APIController::json_error_message('Unsuccess',$e->getMessage(),404);
            }    
        }
        else
            return APIController::json_error_message('Subscription not found',[],404);
            
    }
}
