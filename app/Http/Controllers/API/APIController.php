<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;

use Tymon\JWTAuth\Facades\JWTAuth;
use Auth;

class APIController extends Controller
{

    public static function json_error($code, $message = '')
    {
        $msg = '';
        $http_status = 200;
        switch($code) {
            case 1: $msg = "Wrong credentials"; $http_status = 404; break;
            case 2: $msg = "Username or E-mail already exist"; $http_status = 400; break;
            case 3: $msg = "DB error"; $http_status = 500; break;
            case 4: $msg = "Activation code is wrong"; $http_status = 400; break;
            case 5: $msg = "SMS sending fail"; $http_status = 500; break;
            case 6: $msg = "Phone is already in use"; $http_status = 400; break;
            case 7: $msg = "Social username already exist"; $http_status = 500; break;
            case 8: $msg = "Wrong  input"; $http_status = 422; break;
            case 9: $msg = "Order already accepted"; $http_status = 404; break;
            case 10: $msg = "User not found"; $http_status = 404; break;
            case 11: $msg = "User is not active"; $http_status = 423; break;
            case 12: $msg = "Wrong user Role"; $http_status = 403; break;
            case 13: $msg = "Phone not found"; $http_status = 404; break;
            case 14: $msg = "You already have active order"; $http_status = 400; break;
            case 15: $msg = "This action is Forbidden"; $http_status = 403; break;
            case 16: $msg = "Information not found"; $http_status = 404; break;
            case 17: $msg = "Information already exist"; $http_status = 404; break;
            case 18: $msg = "Wrong Token"; $http_status = 401; break;
            case 19: $msg = "Submit your phone"; $http_status = 202; break;
            case 20: $msg = "Wrong password"; $http_status = 400; break;
            default: $msg = "Unknown error"; $http_status = 500; break;
        }

        if($message != '')
        {
            $msg =  $message;
        }

        $json = ['code'=>$code, 'msg' => $msg];
        return self::json_return($json, $http_status);
            
    }

    public static function json_error_message($msg, $errors, $http_status)
    {
        $data=[
            'message'   => 'Invalid credentials',
            'errors'    => $errors
        ];

        return self::json_return($data, $http_status);
    }

    public static function json_empty_success()
    {
        return self::json_return('');
    }

    public static function json_success($data)
    {
        return self::json_return($data);
    }
    public static function json_return($data,$http_status=200)
    {
        $json = json_encode($data);
        return response($json, $http_status)->header('Content-Type', 'application/json');
    }

    public static function AuthResponce($userdata,$isuser=true)
    {
        try {
            if ($isuser)
            {
                if (!$access_token = JWTAuth::fromUser($userdata)) {
                    return APIController::json_error_message('Invalid credentials',[],401);
                }
            }
            else
            {
                if (!$access_token = JWTAuth::attempt($userdata)) {
                    return APIController::json_error_message('Invalid credentials',[],401);
                }
                $userdata=Auth::user();
            }
        } catch (JWTException $e) {
            return APIController::json_error_message('Could not create token',[],500);
        }
        $userdata->LoadForShow();

        $data = [
            'access_token' => $access_token,
            'user'=> $userdata->toarray(),
        ];
        return APIController::json_success($data);
    }
    public static function LogoutResponce()
    {
        $token = JWTAuth::getToken();
        JWTAuth::invalidate($token);
        return APIController::json_empty_success();
    }
}
