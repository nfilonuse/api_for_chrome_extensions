<?php

namespace App\Http\Controllers\API\mobile;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Roles;
use App\Models\Orders;
use App\Models\Chat;
use Illuminate\Mail\Message;
use Response;
use DB;
use Hash;

class APIChatController extends Controller
{
    public function send(request $request)
    {
        $msg = $request->get('message');
        $order_id = $request->get('order_id');
        $user_id = APIController::get_APIUserID($request);

        $user = User::where('id', $user_id)->first();
        $role = Roles::where('id', $user->role_id)->first()->role;
        $order = Orders::where('id', $order_id)->first();

        if(count($order) == 0)
        {
            return APIController::json_error(16, 'Order is not found');
        }

        if($role=='user')
        {
            $to = $order->driver_id;
        }

        if($role=='driver')
        {
            $to = $order->user_id;
        }

        $message = new Chat();
        $message->sender_id = $user_id;
        $message->reciver_id = $to;
        $message->order_id = $order_id;
        $message->message = $msg;
        $message->msg_date = date("Y-m-d H:i:s");
        $message->save();

        PushController::new_message($to, $msg, time());

        return APIController::json_empty_success();
    }

    public function get(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $order_id = $request->get('order_id');
        $order = Orders::where('id', $order_id)->first();
        if(count($order)==0)
        {
            return APIController::json_error(16, 'Order is not found');
        }

        $user = User::where('id', $user_id)->first();
        $role = Roles::where('id', $user->role_id)->first()->role;
        if($role=='user')
        {
            $search_type = 'driver_id';
        }
        else
        {
            $search_type = 'user_id';
        }

        $to_id = $order->$search_type;
        //$query = "SELECT * FROM public.chats WHERE (sender_id = ".$user_id." OR reciver_id = ".$user_id.") AND () order_id = ".$order_id;
        $query = "SELECT * FROM public.chats WHERE (sender_id = ".$user_id." OR reciver_id = ".$user_id.") AND (sender_id = ".$to_id." OR reciver_id = ".$to_id.")";
        $chats = DB::select( DB::raw($query) );
        $data = [];
        foreach($chats as $key => $chat)
        {
            $user = User::where('id', $chat->sender_id)->first();
            if(count($user) == 0)
            {
                continue;
            }

            if($chat->sender_id == $user_id)
            {
                $data[$key]['type'] = 1;
            }
            else
            {
                $data[$key]['type'] = 0;
            }
            $data[$key]['from'] = $user->full_name;
            $data[$key]['date'] = APIController::StrTounixTime($chat->msg_date);
            $data[$key]['message'] = $chat->message;
        }

        return APIController::json_success($data);
    }

}
