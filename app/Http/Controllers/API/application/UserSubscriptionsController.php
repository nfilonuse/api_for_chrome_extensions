<?php

namespace App\Http\Controllers\API\application;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\APIController as APIController;
use Auth;
use Stripe\Charge;
use App\Models\Subscriptions;
use App\Models\UserSubscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class UserSubscriptionsController extends Controller
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
        $subscriptions=UserSubscriptions::where('user_id',$user->id)->get();
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

    /**
     * @param int $subscription_id
     * @param $payment_data
     * @return mixed
     */
    public function checkout($id) {

        $user = Auth::User();
        $subscription=Subscriptions::where($id)->get();

        if($subscription) {
            $token = Crypt::encryptString($user->id.'||'.$user->email);
            $data = [
                'token' => $token,
                'id' => $id
            ];
            return APIResponse::json_success($data);
        } else {
            $data=[
                'message'   => 'Subscription not found',
            ];
            return APIResponse::json_return($data,404);
        }
    }
}
