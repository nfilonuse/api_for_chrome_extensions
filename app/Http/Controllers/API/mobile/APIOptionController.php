<?php

namespace App\Http\Controllers\API\mobile;

use App\Models\Favourites;
use App\Models\Images;
use Illuminate\Http\Request;
use App\Models\Option;
use App\Http\Controllers\API\mobile\APIController as APIController;
use App\Models\Blacklist;
use App\Models\User;
use App\Models\Subscriptions;
use App\Models\DriverSubscription;
use App\Models\PaymentLog;
use App\Models\Rates;

class APIOptionController extends Controller
{
    public function get(request $request)
    {
        $option = $request->get('option');
        $data = Option::where('name', $option)->first();
        if( count($data) == 0)
        {
            return APIController::json_error(8, 'This option is not exist');
        }
        $output = ['value'=>$data->value];
        return APIController::json_success($output);
    }

    public function all()
    {
        $data = Option::all();
        $input = [];
        foreach($data as $key => $dt)
        {
            $input[$dt->name] = $dt->value;
        }
        return APIController::json_success($input);
    }

    /* BlackList options GET */
    public function get_blacklist(request $request, $type)
    {
        $user_id = APIController::get_APIUserID($request);
        $blacklist = Blacklist::where('user_id', $user_id)->get();
        $data = [];
        foreach($blacklist as $key => $item)
        {
            $user_blocked = $user = User::where('id', $item->user_id_blocked)->first();
            if( count($user_blocked) == 0)
            {
                continue;
            }
            $data[$key]['id'] = $user_blocked->id;
            $data[$key]['full_name'] = $user_blocked->full_name;
            $data[$key]['email'] = $user_blocked->email;
            $data[$key]['phone'] = $user_blocked->phone;
        }

        if(count($data) == 0)
        {
            return APIController::json_error(16, 'Your blacklist is empty');
        }

        return APIController::json_success($data);
    }

    public function get_blacklist_driver(request $request)
    {
        return $this->get_blacklist($request, 'driver');
    }

    public function get_blacklist_client(request $request)
    {
        return $this->get_blacklist($request, 'user');
    }

    /* BlackList options ADD */
    public function add_blacklist(request $request, $type)
    {
        $user_id = APIController::get_APIUserID($request);
        $user_id_blocked = $request->get('user_id');
        if(empty($user_id_blocked))
        {
            return APIController::json_error(8, 'Blocked user id empty');
        }

        $blacklist = Blacklist::where('user_id', $user_id)
            ->where('user_id_blocked', $user_id_blocked)
            ->first();

        if(count($blacklist)==0)
        {
            $bl = new Blacklist();
            $user_blocked = User::where('id', $user_id_blocked)->first();
            if(count($user_blocked)!=0) {
                $bl->user_id = $user_id;
                $bl->user_id_blocked = $user_id_blocked;
                $bl->save();
                return APIController::json_empty_success();
            }
        }
        else
        {
            return APIController::json_error(17, 'You already add this user to blacklist');
        }
    }

    public function add_blacklist_driver(request $request)
    {
        return $this->add_blacklist($request, 'driver');
    }

    public function add_blacklist_client(request $request)
    {
        return $this->add_blacklist($request, 'user');
    }

    /* BlackList options Remove */
    public function remove_blacklist(request $request, $type, $user_id_blocked)
    {
        $user_id = APIController::get_APIUserID($request);
        $search_bl = Blacklist::where('user_id', $user_id)
            ->where('user_id_blocked', $user_id_blocked)->first();
        if(count($search_bl) == 0)
        {
            return APIController::json_error(16, 'This user is not in your blacklist');
        }

        $search_bl->delete();
        return APIController::json_empty_success();
    }

    public function remove_blacklist_driver($user_id, request $request)
    {
        return $this->remove_blacklist($request, 'driver', $user_id);
    }

    public function remove_blacklist_client($user_id, request $request)
    {
        return $this->remove_blacklist($request, 'user', $user_id);
    }


    /* Favourites GET */
    public function get_favourites(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $favourites = Favourites::where('user_id', $user_id)->get();
        $data = [];
        foreach($favourites as $key => $favourite)
        {
            $data[$key]['id'] = $favourite->id;
            $data[$key]['name'] = $favourite->name;
            $data[$key]['lat'] = $favourite->lat;
            $data[$key]['lng'] = $favourite->lng;
            $data[$key]['adr'] = $favourite->adr;
        }

        if(count($data) == 0)
        {
            return APIController::json_error(16, 'Your favourites are empty');
        }

        return APIController::json_success($data);
    }

    public function add_favourites(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $name = $request->get('name');
        $lat = $request->get('lat');
        $lng = $request->get('lng');
        $adr = $request->get('adr');
        if(empty($name) || empty($lat) || empty($lng) || strlen($name) > 200)
        {
            return APIController::json_error(8, 'Input all required fields');
        }

        $favourites = Favourites::where('user_id', $user_id)
            ->where('name', $name)->first();
        if(count($favourites) > 0)
        {
            return APIController::json_error(17, "It's already in your's favorites");
        }

        $favourite = new Favourites();
        $favourite->user_id = $user_id;
        $favourite->name = $name;
        $favourite->lat = $lat;
        $favourite->lng = $lng;
        $favourite->adr = $adr;
        $favourite->save();
        return APIController::json_empty_success();
    }

    public function remove_favourites(request $request)
    {
        $favourite_id = $request->get('favourite_id');
        $user_id = APIController::get_APIUserID($request);
        $favourite = Favourites::where('user_id', $user_id)->where('id', $favourite_id)->first();
        if(count($favourite)>0)
        {
            $favourite->delete();
            return APIController::json_empty_success();
        }
        else
        {
            return APIController::json_error(16);
        }
    }

    public function subscription_free(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $count = DriverSubscription::where('driver_id', $user_id)->where('active', 1)->count();
        $free_subscription_id = Option::where('name', 'free_subscription_id')->first()->value;

        if($count > 0)
        {
            $cs = DriverSubscription::where('driver_id', $user_id)->where('active', 1)->first();
            $time = time();
            $subscription = Subscriptions::where('id', $cs->subscriptions_id)->first();
            $expire = APIOptionController::calculate_expire(APIController::StrTounixTime($cs->added_on), $subscription->expiration_time, $subscription->expiration_period);
            if($expire > $time)
            {
                return APIController::json_error(15, 'You Already have subscription');
            }
            else
            {
                $cs->active = 0;
                $cs->save();
            }
        }

        if( SELF::is_can_take_free_subscription($user_id) )
        {
            $cs = new DriverSubscription();
            $cs->driver_id = $user_id;
            $cs->subscriptions_id = $free_subscription_id;
            $cs->added_on = date('Y-m-d H:i:s', time());
            $cs->active = 1;
            $cs->save();

            return APIController::json_empty_success();
        }
        else
        {
            return APIController::json_error(15, "You can't take free subscription right now.");
        }

    }


    public static function is_can_take_free_subscription($user_id)
    {
        $free_subscription_id = Option::where('name', 'free_subscription_id')->first()->value;
        $free_subscription_time = Option::where('name', 'free_subscription_time')->first()->value;
        $free_subscription_period = Option::where('name', 'free_subscription_period')->first()->value;

        $time = time();
        $min_time = $time - ($free_subscription_period * $free_subscription_time);

        $dt_min = date('Y-m-d H:i:s', $min_time);

        $c = DriverSubscription::where('driver_id', $user_id)
            ->where('subscriptions_id', $free_subscription_id)
            ->where('added_on', '>', $dt_min)
            ->where('added_on', '<', date('Y-m-d H:i:s', $time) )
            ->count();

        if($c == 0)
            return (bool) true;
        else
            return (bool) false;


    }

    public function subscription_check(request $request)
    {
        $user_id = APIController::get_APIUserID($request);

        $ds = DriverSubscription::where('driver_id', $user_id)
            ->where('active', 1)
            ->orderBy('added_on', 'desc')
            ->first();

        $image_search = Images::where('user_id', $user_id)
            ->where('accepted', -1)
            ->first();

        $err_img_flag = false;
        if(count($image_search) > 0)
        {
            $err_img_flag = true;
        }

        if( count($ds) == 0 )
        {
            $data = [
                'payed'=>false,
                'expire'=>0,
                'name'=>'Free'
            ];

            if($err_img_flag)
            {
                $data['msg_block'] = $image_search->comment;
                return APIController::json_error_message($data, 403);
            }
            return APIController::json_success($data);
        }
        else
        {
            $subscription = Subscriptions::where('id', $ds->subscriptions_id)->first();
            if(count($subscription)>0) {
                $is_payed = (bool)$subscription->is_payed;
                $added = APIController::StrTounixTime($ds->added_on);
                $expire = APIOptionController::calculate_expire($added, $subscription->expiration_time, $subscription->expiration_period);
                $data = [
                    'payed' => $is_payed,
                    'expire' => $expire,
                    'name' => $subscription->name
                ];

                if ($err_img_flag) {
                    $data['msg_block'] = $image_search->comment;
                    return APIController::json_error_message($data, 403);
                }
                return APIController::json_success($data);
            }
            else
            {
                $data = [
                    'payed' => false,
                    'expire' => 0,
                    'name' => 'This subscription is removed'
                ];
                if ($err_img_flag) {
                    $data['msg_block'] = $image_search->comment;
                    return APIController::json_error_message($data, 403);
                }
                return APIController::json_success($data);
            }
        }
    }

    public function subscription_list(request $request)
    {
        $subscriptions = Subscriptions::all();
        $data = [];
        $user_id = APIController::get_APIUserID($request);

        $free_subscription_id = Option::where('name', 'free_subscription_id')->first()->value;


        foreach($subscriptions as $subscription)
        {

            if($subscription->id == $free_subscription_id)
            {
                $flag = SELF::is_can_take_free_subscription($user_id);
                if($flag === false)
                {
                    continue;
                }
            }

            $arr['id'] = $subscription->id;
            $arr['name'] = $subscription->name;
            $data[] = $arr;
        }
        return APIController::json_success($data);
    }

    public function subscription_history_payment(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $payments = PaymentLog::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();
        $data = [];
        foreach($payments as $key => $payment)
        {
            $subscription = Subscriptions::where('id', $payment->subscriptions_id)->first();
            $data[$key]['name'] = $subscription->name;
            $data[$key]['amount'] = $payment->price;
            $data[$key]['datetime'] = APIController::StrTounixTime($payment->updated_at);
        }

        if( count($data) == 0)
        {
            return APIController::json_error(16, "Your's payment history is empty");
        }

        return APIController::json_success($data);
    }

    public static function calculate_expire($added, $time, $period)
    {
        $period_multiplier = 1;
        switch($period) {
            case 1: $period_multiplier = 60; break;
            case 2: $period_multiplier = 3600; break;
            case 3: $period_multiplier = 86400; break;
            case 4: $period_multiplier = 604800; break;
            case 5: $period_multiplier = 2419200; break;
            case 6: $period_multiplier = 31536000; break;
            case 7: $period_multiplier = 315360000; break;
            case 8: $period_multiplier = 3153600000; break;
            default: $period_multiplier = 60; break;
        }
        $expire = $added + ($time * $period_multiplier);
        return $expire;
    }


    public static function subscription_check_stat($user_id)
    {

        $ds = DriverSubscription::where('driver_id', $user_id)
            ->where('active', 1)
            ->orderBy('added_on', 'desc')
            ->first();

        $image_search = Images::where('user_id', $user_id)
            ->where('accepted', -1)
            ->first();

        $err_img_flag = false;
        if(count($image_search) > 0)
        {
            $err_img_flag = true;
        }

        if( count($ds) == 0 )
        {
            $data = [
                'payed'=>false,
                'expire'=>0,
            ];

            if($err_img_flag)
            {
                $data['msg_block'] = $image_search->comment;
                return false;
            }
            return $data;
        }
        else
        {
            $subscription = Subscriptions::where('id', $ds->id)->first();

            if(count($subscription)==0)
            {
                $is_payed = false;
                $expire = $ds->added_on;
            }
            else
            {
                $is_payed = (bool)$subscription->is_payed;
                $added = APIController::StrTounixTime($ds->added_on);

                $expire = APIOptionController::calculate_expire($added, $subscription->expiration_time, $subscription->expiration_period);
            }
            $data = [
                'payed'=>$is_payed,
                'expire'=>$expire,
            ];

            if($err_img_flag)
            {
                $data['msg_block'] = $image_search->comment;
                return false;
            }
            return $data;
        }
    }

    public static function price_format($price)
    {
        return 'G$'.$price;
    }

    public static function status_text($status)
    {
        if($status==0)
        {
            return 'Published';
        }

        if($status==1)
        {
            return 'On the way to the client';
        }

        if($status==2)
        {
            return 'Canceled by driver';
        }

        if($status==3)
        {
            return 'Canceled by client';
        }

        if($status==4)
        {
            return 'Code Confirmed';
        }

        if($status==5)
        {
            return 'Complate';
        }
    }

    public function get_user_rate($user_id)
    {
        $rates = Rates::where('to', $user_id)->get();
        $rate_data['total'] = 0;
        $rate_data['cnt'] = 0;
        foreach ($rates as $rate) {
            $rate_data['total'] += $rate->rate;
            $rate_data['cnt']++;
        }
        if ($rate_data['cnt'] == 0) {
            $rate_data['avg'] = 0;
        } else {
            $rate_data['avg'] = round($rate_data['total'] / $rate_data['cnt'], 2);
        }
        $data = [
            'rate'=>  $rate_data['avg']
        ];
        return APIController::json_success($data);
    }

    public function get_users_rate(request $request)
    {
        $users = $request->get('users');
        $data = [];
        foreach($users as $user_id) {
            $rates = Rates::where('to', $user_id)->get();
            $rate_data['total'] = 0;
            $rate_data['cnt'] = 0;
            foreach ($rates as $rate) {
                $rate_data['total'] += $rate->rate;
                $rate_data['cnt']++;
            }
            if ($rate_data['cnt'] == 0) {
                $rate_data['avg'] = 0;
            } else {
                $rate_data['avg'] = round($rate_data['total'] / $rate_data['cnt'], 2);
            }
            $data[] = ['user_id'=>$user_id,'rate' => $rate_data['avg']];
        }
        return APIController::json_success($data);
    }



    public static function get_periods_array()
    {
        $dt[1] = 'Minutes';
        $dt[2] = 'Hours';
        $dt[3] = 'Days';
        $dt[4] = 'Weeks';
        $dt[5] = 'Months (28 days)';
        $dt[6] = 'Years';
        $dt[7] = 'Decades';
        $dt[8] = 'Unlimited';
        return $dt;
    }

    public static function get_periods_times()
    {
            $period_multiplier[1] = 60;
            $period_multiplier[2] = 3600;
            $period_multiplier[3] = 86400;
            $period_multiplier[4] = 604800;
            $period_multiplier[5] = 2419200;
            $period_multiplier[6] = 31536000;
            $period_multiplier[7] = 315360000;
            $period_multiplier[8] = 3153600000;
            return $period_multiplier;
    }

}
