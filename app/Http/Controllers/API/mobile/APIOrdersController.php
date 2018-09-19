<?php

namespace App\Http\Controllers\API\mobile;

use App\Models\Blacklist;
use Dingo\Api\Facade\API;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Roles;
use Response;
use DB;
use smsController;
use App\Models\Orders;
use App\Models\Rates as Rates;
use App\Models\Avaible;
use App\Models\Option;
use App\Models\OrderStatus;
use App\Models\Reasons;
use App\Models\Images as Images;
use App\Http\Controllers\API\mobile\PushController as PushController;
use App\Http\Controllers\API\mobile\OrdersController as OrdersController;
use App\Http\Controllers\API\mobile\APIController as APIController;
use App\Models\DriverTariff;
use App\Models\Tariff;
use App\Http\Controllers\API\mobile\RatesController as RatesController;
use App\Http\Controllers\API\mobile\APIGPSController as APIGPSController;
use App\Http\Controllers\API\mobile\ImagesController as ImagesController;
use App\Models\Invoices;
use App\Models\DriverSubscription;
use App\Models\Subscriptions;
use Log;



class APIOrdersController extends Controller
{
    public function add(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $points = $request->get('points');
        $expected_price = $request->get('expected_price');
        $client_coment = $request->get('client_coment');
        $order_activation_code = rand(10,99);

        $role_id = (int) Roles::where('role', 'user')->first()->id;
        $user = User::where('id', $user_id)->first();
        if($user->role_id != $role_id)
        {
            return APIController::json_error(12, "Drivers can not add orders");
        }

        $json_str = json_encode($points);
        $new_order = new Orders();
        $new_order->user_id = $user_id;
        $new_order->driver_id = 0;
        $new_order->status = 0;
        $new_order->active_client = 1;
        $new_order->expected_price = floatval($expected_price);
        $new_order->points = $json_str;
        $new_order->client_coment = $client_coment;
        $new_order->order_activation_code = $order_activation_code;

        $order = Orders::where('active_client', 1)
            ->where('user_id', $user_id)
            ->first();

        if( count($order) > 0)
        {
            return APIController::json_error(14, "You already have active order. Complete or cancel the current order before creating a new one");
        }
        else
        {
            $new_order->save();
        }
        $result = $this->drivers_set_order($points[0], $new_order->id, $user_id);

        if(!$result)
        {
            APIController::json_error(8, "We could not find suitable drivers for your order");
        }

        $data['id'] = $new_order->id;
        $data['points'] = $points;
        $data['datetime'] = APIController::StrTounixTime($new_order->created_at);
        $data['price'] = $new_order->price;
        $data['expected_price'] = $new_order->expected_price;
        $data['status'] = 0;
        $data['driver_id'] = $new_order->driver_id;
        $data['user_id'] = $new_order->user_id;
        $data['activation_code'] = $new_order->order_activation_code;

        return APIController::json_success($data);
    }

    public function drivers_set_order($geo, $order_id, $client_id)
    {
        $radius = Option::where('name', 'search_radius')->first()->value;
        $radius_m = $radius / 1000;
        $data = APIGPSController::get_drivers_in_radius($geo, $radius_m, true);
        $uniq_arr = array();
        foreach($data as $driver)
        {
            $avaible = Avaible::where('user_id', $driver['user_id'])->first();
            $r_client = APIController::is_in_blacklist($client_id, $driver['user_id']);
            $r_driver = APIController::is_in_blacklist($driver['user_id'], $client_id);
            $d_active_order = Orders::where('driver_id', $driver['user_id'])->where('active_driver', 1)->count();


            $cs = DriverSubscription::where('driver_id', $driver['user_id'])->where('active', 1)->first();
            $time = time();
            if(count($cs)==0)
            {
                $expire = 0;
            }
            else
            {
                $subscription = Subscriptions::where('id', $cs->subscriptions_id)->first();
                $expire = APIOptionController::calculate_expire($time, $subscription->expiration_time, $subscription->expiration_period);
            }

            /*
            Skip driver if
            Subscription is expired
            */
            if($time > $expire)
            {
                continue;
            }

            /*
            Skip driver if
            driver is not avaible or have active order or in blacklist
            */
            if(count($avaible)==0 || $avaible->val==0 || $r_client || $r_driver || $d_active_order>0)
            {
                continue;
            }

            /*
             Skip driver if
             already send request
             */
            $res = array_search($driver['user_id'], $uniq_arr);
            if($res!==false)
            {
                continue;
            }

            $is_have_trial = APIController::is_driver_have_trial($driver['user_id']);
            if($is_have_trial)
            {
                $is_active_trial = APIController::is_driver_trial($driver['user_id']);
                if(!$is_active_trial)
                {
                    continue;
                }
            }

            $order_status = new OrderStatus();
            $order_status->driver_id = $driver['user_id'];
            $order_status->status = 0;
            $order_status->order_id = $order_id;
            $order_status->save();
            $uniq_arr[] = $driver['user_id'];
            PushController::order_add($client_id, $driver['user_id'], $order_id);
        }
        return true;
    }

    public function set_rate(request $request, $type)
    {
        $user_id = APIController::get_APIUserID($request);
        $order_id = $request->get('order_id');
        $mark = $request->get('rate');
        $text = $request->get('text');

        if(empty($mark) || empty($order_id))
        {
            return APIController::json_error(8);
        }

        $user = User::where('id', $user_id)->first();
        $role_id = (int) Roles::where('role', $type)->first()->id;
        if($user->role_id != $role_id)
        {
            return APIController::json_error(12);
        }

        $rates = Rates::where('from', $user_id)->where('order_id', $order_id)->count();
        if($rates>0)
        {
            return APIController::json_error(15, "You already rate this order");
        }

        $order = Orders::where('id', $order_id)->first();

        if( count($order) == 0)
        {
            return APIController::json_error(16, 'Order is not found');
        }

        if($type=="user")
        {
            $push_to = $order->driver_id;
        }
        else
        {
            $push_to = $order->Client->id;
        }

        $rates = Rates::where('to', $push_to)->get();
        $rate_data['total'] = 0;
        $rate_data['cnt'] = 0;
        foreach ($rates as $rate) {
            $rate_data['total'] += $rate->rate;
            $rate_data['cnt']++;
        }
        $rate_data['cnt']++;
        $rate_data['total'] += $mark;

        if ($rate_data['cnt'] == 0) {
            $rate_data['avg'] = 0;
        } else {
            $rate_data['avg'] = $rate_data['total'] / $rate_data['cnt'];
        }

        if($type=="user")
        {
                $rate = new Rates();
                $rate->type = 1;
                $rate->from = $user_id;
                $rate->to = $order->driver_id;
                $rate->rate = $mark;
                $rate->order_id = $order->id;
                $rate->text = $text;
                $rate->save();
        }

        if($type=="driver")
        {
                $rate = new Rates();
                $rate->type = 2;
                $rate->from = $user_id;
                $rate->to = $order->Client->id;
                $rate->rate = $mark;
                $rate->text = $text;
                $rate->order_id = $order->id;
                $rate->save();
        }

        PushController::rate_update($push_to, $rate_data['avg']);
        return APIController::json_empty_success();

    }

    public function set_rate_client(request $request)
    {
        return $this->set_rate($request, 'user');
    }

    public function set_rate_driver(request $request)
    {
        return $this->set_rate($request, 'driver');
    }

    public function set_avaible(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $avaible = $request->get('avaible');

        /*
        * if one of image is reject by admin, then driver can't
        * be avaible for recive order
        */
        $images = Images::where('user_id', $user_id)->get();
        foreach ($images as $image){
            if($image->accepted == -1)
            {
                return APIController::json_error(11, "You can't become available, because some of your's images are rejected by admin");
            }
        }

        if(!isset($avaible))
        {
            return APIController::json_error(8);
        }

        $is_trial = APIController::is_driver_have_trial($user_id);
        $a = Avaible::where('user_id', $user_id)->first();

        $cs = DriverSubscription::where('driver_id', $user_id)->where('active', 1)->first();

        if(count($cs)==0 && ($avaible==1 || $avaible == true)) {
            return APIController::json_error(1, 'You have currently no active subscription');
        }

        if(count($a)>0)
        {
            $a->val = $avaible;
        }
        else
        {
            $a = new Avaible();
            $a->user_id = $user_id;
            $a->val = $avaible;
        }

        $a->save();
        return APIController::json_empty_success();

        /*
        if(count($search)>0)
        {
            $search->val = $avaible;
            if($is_trial && ($avaible==1 || $avaible == true))
            {
                $is_active_trial = APIController::is_driver_trial($user_id);
                if(!$is_active_trial)
                {
                    $trial_from = date("Y-m-d H:i:s");
                    $trial_to = date("Y-m-d H:i:s", mktime(date("H")+1, date("i"), date("s"), date("m")  , date("d"), date("Y")));
                    $search->trial_from = $trial_from;
                    $search->trial_to = $trial_to;
                }
            }
            $search->save();
            return APIController::json_empty_success();
        }
        else
        {
            $a = new Avaible();
            $a->user_id = $user_id;
            $a->val = $avaible;

            if($is_trial && ($avaible==1 || $avaible == true))
            {

                $is_active_trial = APIController::is_driver_trial($user_id);
                if (!$is_active_trial) {
                    $trial_from = date("Y-m-d H:i:s");
                    $trial_to = date("Y-m-d H:i:s", mktime(date("H")+1, date("i"), date("s"), date("m"), date("d"), date("Y")));
                    $a->trial_from = $trial_from;
                    $a->trial_to = $trial_to;
                }

            }

            $a->save();
            return APIController::json_empty_success();
        }
        */
    }

    public function get_order_status(request $request, $type)
    {
        /* OLD VERSION
        $user_id = APIController::get_APIUserID($request);
        $order_id = $request->get('order_id');

        $order = Orders::where('id', $order_id)->first();
        if( count($order) == 0 )
        {
            return APIController::json_error(1);
        }

        if($user_id != $order->user_id && $user_id != $order->driver_id)
        {
            return APIController::json_error(15);
        }
        $data =
            [
                'status' => $order->status,
            ];
        if($type=='user')
            $data['driver_id'] = $order->driver_id;

        if($type=='driver')
            $data['user_id'] = $order->user_id;

        return APIController::json_success($data);
        */

        $user_id = APIController::get_APIUserID($request);
        $order_id = $request->get('order_id');
        $order = Orders::where('id', $order_id)->first();
        if( count($order) == 0 )
        {
            return APIController::json_error(16, 'Order not found');
        }

        if($user_id != $order->user_id && $user_id != $order->driver_id)
        {
            return APIController::json_error(15, "It's not your's order ");
        }

        $data =
            [
                'status_order' => $order->status,
                'status_driver' => $order->status_2
            ];
        if($type=='user')
		{
            $data['driver_id'] = $order->driver_id;
			$driver = User::where('id', $order->driver_id)->first();
			if ($driver)
			{
				$data['driver'] =collect($driver->toarray())->only(['phone', 'full_name', 'car_number', 'car_marka', 'car_color']);
				$image = Images::where('user_id', $order->driver_id)->where('type', 0)->first();
				if (!empty($image))
				{
					$accepted = false;
					if($image->accepted == 1)
					{
						$accepted = true;
					}
					$type_data = ImagesController::get_type_str($image->type);
					$fc = ImagesController::get_base64_encoded_image($image->file_name);
					$data['driver'][$type_data] = [
						"file_name" => $image->file_name,
						"file_content" => $fc,
						"accepted" => $accepted
					];
				}
				$rate_data=Rates::get_totalbyuser($order->driver_id);
                $data['driver']['rate'] = number_format($rate_data['avg'], 2);
			}
			else
			{
				$data['driver'] =null;
			}
		}

        if($type=='driver')
		{
            $data['user_id'] = $order->user_id;
			$client = User::where('id', $order->user_id)->first();
			if ($client)
			{
				$data['client'] =collect($client->toarray())->only(['phone', 'full_name']);
			}
			else
			{
				$data['client'] =null;
			}
		}
        return APIController::json_success($data);

    }

    public function get_order_status_client(request $request)
    {
        return $this->get_order_status($request, 'user');
    }

    public function get_order_status_driver(request $request)
    {
        return $this->get_order_status($request, 'driver');
    }


    public function set_order_status(request $request, $type)
    {
        /* OLD VERSION
        $user_id = APIController::get_APIUserID($request);
        $order_id = $request->get('order_id');
        $state = $request->get('state');
        $order = Orders::where('id', $order_id)->first();
        if( count($order) == 0 )
        {
            return APIController::json_error(1);
        }

        if($user_id != $order->user_id && $user_id != $order->driver_id)
        {
            return APIController::json_error(15);
        }


        // update Order

        $order->status = $state;
        if($state == 2 || $state == 3 || $state == 5)
        {
            $order->active_client = 0;
            $order->active_driver = 0;
        }
        $order->save();
        PushController::order_status_update($order->user_id, $state, $order_id);
        return APIController::json_empty_success();
        */
        $user_id = APIController::get_APIUserID($request);
        $order_id = $request->get('order_id');
        $state = $request->get('state');
        $order = Orders::where('id', $order_id)->first();
        if( count($order) == 0 )
        {
            return APIController::json_error(16, "Order not found");
        }

        if($user_id != $order->driver_id)
        {
            return APIController::json_error(15, "Only driver can set order state");
        }

        $order->status_2 = $state;
        $order->save();

        PushController::order_status_update($order->user_id, $state, $order_id);
        return APIController::json_empty_success();
    }

    public function set_order_status_client(request $request)
    {
        return $this->set_order_status($request, 'user');
    }

    public function set_order_status_driver(request $request)
    {
        return $this->set_order_status($request, 'driver');
    }

    public function check_order_code(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $order_id = $request->get('order_id');
        $order_activation_code = $request->get('order_activation_code');
        $order = Orders::where('id', $order_id)->first();
        if( count($order) == 0 )
        {
            return APIController::json_error(16, "Order not found");
        }

        if($user_id != $order->user_id && $user_id != $order->driver_id)
        {
            return APIController::json_error(15, "It's not your's order ");
        }

        if($order->order_activation_code == $order_activation_code)
        {
            $order_status = OrderStatus::where('order_id', $order_id)->where('driver_id', $user_id)->first();
            if(count($order_status) == 0)
            {
                return APIController::json_error(8);
            }
            $order_status->status = 3;
            $order_status->save();

            $order->status = 4;
            $order->save();

            PushController::order_status_update($order->user_id, 1, $order_id);
            return APIController::json_empty_success();
        }
        else
        {
            return APIController::json_error(4);
        }
    }


    public function order_accept(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $order_id = $request->get('order_id');
        if(empty($order_id))
        {
            return APIController::json_error(8, "Order not found");
        }

        $order = Orders::where('id', $order_id)->first();
        $user = User::where('id', $user_id)->first();
        $role_id = (int) Roles::where('role', 'driver')->first()->id;
        if($role_id != $user->role_id)
        {
            return APIController::json_error(12, "Client can't accept order");
        }

        if( count($order) == 0 )
        {
            return APIController::json_error(16, "Order not found");
        }

        if($order->active_client==0)
        {
            return APIController::json_error(16, "Order canceled");
        }

        if($order->driver_id != 0)
        {
            return APIController::json_error(9);
        }

        $active_currnet = Orders::where('driver_id', $user_id)->where('active_driver', 1)->count();
        if($active_currnet > 0)
        {
            return APIController::json_error(15, "You already have active order");
        }

        $order_status = OrderStatus::where('order_id', $order_id)->where('driver_id', $user_id)->first();
        if(count($order_status) == 0)
        {
            return APIController::json_error(15, "You can't accept this order");
        }
        $order_status->status = 2;
        $order_status->save();

        $order->driver_id = $user_id;
        $order->status = 1;
        $order->history_flag = 1;
        $order->active_driver = 1;
        $order->save();

        $other_drivers = OrderStatus::where('order_id', $order_id)->get();
        foreach($other_drivers as $other_driver)
        {
            if($other_driver->driver_id == $user_id)
            {
                continue;
            }
            PushController::delete_order($other_driver->driver_id, $order_id);
        }

        PushController::order_accepted($order->user_id, $user_id, $order_id);
        return APIController::json_empty_success();
    }

    public function order_reject(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $order_id = $request->get('order_id');

        $order_status = OrderStatus::where('order_id', $order_id)->where('driver_id', $user_id)->first();
        if(count($order_status) == 0)
        {
            return APIController::json_error(16);
        }
        $order_status->status = 1;
        $order_status->save();
        return APIController::json_empty_success();
    }

    public function get_history_orders(request $request, $type)
    {
        $user_id = APIController::get_APIUserID($request);
        $search = $type.'_id';

        $orders = Orders::where($search, $user_id)
            ->where('history_flag', 1)->orderBy('created_at', 'desc')->get();

        if( count($orders) == 0)
        {
            return APIController::json_error(16, "Orders history is empty");
        }
        $data = [];
        foreach($orders as $key => $order)
        {
            $data[$key]['id'] = $order->id;
            $data[$key]['points'] = json_decode($order->points);;
            $data[$key]['datetime'] = APIController::StrTounixTime($order->created_at);
            $data[$key]['price'] = floatval (number_format($order->price, 2));
        }
        return APIController::json_success($data);
    }

    public function get_history_client(request $request)
    {
        return $this->get_history_orders($request, 'user');
    }

    public function get_history_driver(request $request)
    {
        return $this->get_history_orders($request, 'driver');
    }


    public static function get_current_order_id($user_id)
    {

        $user = User::where('id', $user_id)->first();
        $type = Roles::where('id', $user->role_id)->first()->role;

        $search = $type.'_id';
        if($type=='user')
            $search_active = 'active_client';
        if($type=='driver')
            $search_active = 'active_driver';

        $order = Orders::where($search, $user_id)->where($search_active,'1')->first();
        if(count($order)==0)
        {
            return 0;
        }

        return $order->id;
    }

    public static function get_current_order(request $request, $type)
    {

        /*
        Log::info("-------HEADER START-------");
        $headers = array ();
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        foreach ($headers as $name => $value) {
            Log::info($name.':'.$value);
        }
        Log::info("-------END-------");
        */
        $user_id = APIController::get_APIUserID($request);
        $search = $type.'_id';
        if($type=='user')
            $search_active = 'active_client';
        if($type=='driver')
            $search_active = 'active_driver';

        $order = Orders::where($search, $user_id)->where($search_active,'1')->first();
        if(count($order)==0)
        {
            return APIController::json_error(16, "You don't have active order right now");
        }

        $driver_tarif = DriverTariff::where('user_id', $order->driver_id)->first();
        if(count($driver_tarif)==0)
        {
            $tariff_title = 'Default Tarif';
            $tariff_id = 0;
            $base_price = Option::where('name', 'base_price')->first()->value;
            $base_distance = Option::where('name', 'base_distance_m')->first()->value;
            $extend_price = Option::where('name', 'price_per_km')->first()->value;
        }
        else
        {
            $driver_tarif_id = $driver_tarif->tariff_id;
            $tariff = Tariff::where('id', $driver_tarif_id)->first();
            $tariff_id = $tariff->id;
            $tariff_title = $tariff->title;
            $base_price = $tariff->base_price;
            $base_distance = $tariff->base_distance;
            $extend_price = $tariff->extend_price;
        }




        $data['id'] = $order->id;
        $points = json_decode($order->points);
        $data['points'] = $points;
        $data['datetime'] = APIController::StrTounixTime($order->created_at);
        $data['price'] = floatval($order->price);
        $data['status'] = $order->status;
        $data['driver_id'] = $order->driver_id;
        $data['expected_price'] = $order->expected_price;
        $data['user_id'] = $order->user_id;
        $data['client_coment'] = $order->client_coment;
        $data['type'] = $order->status_2;
        $data["tarif"] = [
                'id' => $tariff_id,
                'title'=> $tariff_title,
                'base_price'=> $base_price,
                'base_distance' => $base_distance,
                'extend_price'=> $extend_price
            ];

        if($type=='user')
		{
			$data['activation_code'] = $order->order_activation_code;
		
			$driver = User::where('id', $order->driver_id)->first();
			if ($driver)
			{
				$data['driver'] =collect($driver->toarray())->only(['phone', 'full_name', 'car_number', 'car_marka', 'car_color']);
				$image = Images::where('user_id', $order->driver_id)->where('type', 0)->first();
				if (!empty($image))
				{
					$accepted = false;
					if($image->accepted == 1)
					{
						$accepted = true;
					}
					$type_data = ImagesController::get_type_str($image->type);
					$fc = ImagesController::get_base64_encoded_image($image->file_name);
					$data['driver'][$type_data] = [
						"file_name" => $image->file_name,
						"file_content" => $fc,
						"accepted" => $accepted
					];
				}
				$rate_data=Rates::get_totalbyuser($order->driver_id);
                $data['driver']['rate']= number_format($rate_data['avg'], 2);
			}
			else
			{
				$data['driver'] =null;
			}
		}

        if($type=='driver')
		{
		
			$client = User::where('id', $order->user_id)->first();
			if ($client)
			{
				$data['client'] =collect($client->toarray())->only(['phone', 'full_name']);
			}
			else
			{
				$data['client'] =null;
			}
		}

        return APIController::json_success($data);
    }


    public function get_current_order_client(request $request)
    {
        return $this->get_current_order($request, 'user');
    }

    public function get_current_order_driver(request $request)
    {
        return $this->get_current_order($request, 'driver');
    }

    public function orders_filter(request $request)
    {
        $input = $request->get('data');
        if(empty($input))
        {
            return APIController::json_error(8);
        }
        $data = [];
        foreach($input as $order_id) {
            $order = Orders::where('id', $order_id)->first();

            if(count($order) == 0)
            {
                continue;
            }

            if ($order->status == 0)
            {
                $data[] = $order_id;
            }
        }

        if(count($data) == 0)
        {
            return APIController::json_error(16, "No orders left");
        }

        return APIController::json_success($data);
    }


    public function order_set_price(request $request)
    {
        $price = $request->get('price');
        $dist = $request->get('distance');
        $user_id = APIController::get_APIUserID($request);
        $order = Orders::where('driver_id', $user_id)->where('active_driver', '1')->first();
        if(count($order)==0)
        {
            return APIController::json_error(16);
        }

        $order->price = floatval($price);
        $order->distance = $dist;
        $order->save();
        return APIController::json_empty_success();
    }

    public function order_canceled(request $request, $type)
    {
        $order_id = $request->get('order_id');
        $comment = $request->get('comment');
        $cancel_reason_id = $request->get('cancel_reason');

        if(empty($cancel_reason_id) || empty($order_id))
        {
            return APIController::json_error(8, "Cancel reason and order id are required");
        }

        $r = Reasons::where('id', $cancel_reason_id)->first();
        if(count($r)==0)
        {
            return APIController::json_error(8, "Cancel reason not found");
        }

        if(empty($comment))
        {
            $comment = $r->title;
        }
        $user_id = APIController::get_APIUserID($request);

        $search_field = $type.'_id';
        $order = Orders::where($search_field, $user_id)->where('id', $order_id)->first();
        if( count($order) == 0)
        {
            return APIController::json_error(16);
        }

        $order_status = OrderStatus::where('order_id', $order_id)->where('driver_id', $order->driver_id)->first();

        if($type=='user')
        {
            $order->status = 2;
            $send_push_to = $order->driver_id;
        }
        if($type=='driver')
        {
            $order->status = 3;
            $send_push_to = $order->user_id;
        }

        if( count($order_status) != 0)
        {
            $order_status->coment = $comment;
            $order_status->cancel_reason_id = $r->id;
            $order_status->save();
        }

        $order->active_client = 0;
        $order->active_driver = 0;
        $order->save();
        if($send_push_to!=0) {
            PushController::order_canceled($send_push_to, $order->id, $comment);
        }

        return APIController::json_empty_success();
    }

    public function order_canceled_driver(request $request)
    {
        return $this->order_canceled($request, 'driver');
    }

    public function order_canceled_client(request $request)
    {
        return $this->order_canceled($request, 'user');
    }

    public function order_finished(request $request)
    {
        $order_id = $request->get('order_id');
        $price = floatval (number_format($request->get('price'), 2));

        if(empty($order_id) || empty($price))
        {
            if($price!=0)
            {
                return APIController::json_error(8, "Price and Order ID are required");
            }
        }

        $user_id = APIController::get_APIUserID($request);
        $order = Orders::where('driver_id', $user_id)->where('id', $order_id)->first();


        if(count($order)==0)
        {
            return APIController::json_error(16);
        }

        if($order->status == 4)
        {
            $order->active_client = 0;
            $order->active_driver = 0;
            $order->price = $price;
            $order->status = 5;
            $order->save();
            PushController::order_finished($order->user_id, $order_id);
            return APIController::json_empty_success();
        }
        else
        {
            return APIController::json_error(16, "Order status is wrong");
        }
    }

    public function order_canceled_reasons(request $request, $user_type)
    {
        $reasons = Reasons::where('type', 1)
            ->where('user_type', $user_type)
            ->get();
        $data = [];

        foreach($reasons as $key => $reason)
        {
            $data[$key]['id'] = $reason->id;
            $data[$key]['title'] = $reason->title;
        }

        if(count($data) == 0)
        {
            return APIController::json_error(16, "No cancel reasons found");
        }

        return APIController::json_success($data);
    }

    public function order_canceled_reasons_client(request $request)
    {
        return $this->order_canceled_reasons($request, 1);
    }

    public function order_canceled_reasons_driver(request $request)
    {
        return $this->order_canceled_reasons($request, 2);
    }

    public function get_order_by_id($order_id, request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $order = Orders::where('id', $order_id)->first();
        if(count($order)==0)
        {
            return APIController::json_error(16);
        }

        if($user_id != $order->user_id && $user_id != $order->driver_id)
        {
            return APIController::json_error(15, "It's not your's order");
        }
		
		$driver = collect(User::where('id', $order->driver_id)->first()->toarray())->only(['id', 'phone', 'full_name', 'car_number', 'car_marka', 'car_color']);
		$client = collect(User::where('id', $order->user_id)->first()->toarray())->only(['id', 'phone', 'full_name']);
        $driver_tarif = DriverTariff::where('user_id', $order->driver_id)->first();
        if(count($driver_tarif)==0)
        {
            $tariff_title = 'Default Tarif';
            $tariff_id = 0;
            $base_price = Option::where('name', 'base_price')->first()->value;
            $base_distance = Option::where('name', 'base_distance_m')->first()->value;
            $extend_price = Option::where('name', 'price_per_km')->first()->value;
        }
        else
        {
            $driver_tarif_id = $driver_tarif->tariff_id;
            $tariff = Tariff::where('id', $driver_tarif_id)->first();
            $tariff_id = $tariff->id;
            $tariff_title = $tariff->title;
            $base_price = $tariff->base_price;
            $base_distance = $tariff->base_distance;
            $extend_price = $tariff->extend_price;
        }

        $data['id'] = $order->id;
        $points = json_decode($order->points);
        $data['points'] = $points;
        $data['datetime'] = APIController::StrTounixTime($order->created_at);
        $data['price'] = floatval($order->price);
        $data['status'] = $order->status;
        $data['driver_id'] = $order->driver_id;
        $data['expected_price'] = $order->expected_price;
        $data['user_id'] = $order->user_id;
        $data['client_coment'] = $order->client_coment;
        $data['type'] = $order->status_2;
        $data["tarif"] = [
            'id' => $tariff_id,
            'title'=> $tariff_title,
            'base_price'=> $base_price,
            'base_distance' => $base_distance,
            'extend_price'=> $extend_price
        ];
		$driver = User::where('id', $order->driver_id)->first();
		if ($driver)
		{
			$data['driver'] =collect($driver->toarray())->only(['phone', 'full_name', 'car_number', 'car_marka', 'car_color']);
			$image = Images::where('user_id', $order->driver_id)->where('type', 0)->first();
			if (!empty($image))
			{
				$accepted = false;
				if($image->accepted == 1)
				{
					$accepted = true;
				}
				$type_data = ImagesController::get_type_str($image->type);
				$fc = ImagesController::get_base64_encoded_image($image->file_name);
				$data['driver'][$type_data] = [
					"file_name" => $image->file_name,
					"file_content" => $fc,
					"accepted" => $accepted
				];
			}
			$rate_data=Rates::get_totalbyuser($order->driver_id);
            $data['driver']['rate']= number_format($rate_data['avg'], 2);
		}
		else
		{
			$data['driver'] =null;
		}
		$client = User::where('id', $order->user_id)->first();
		if ($client)
		{
			$data['client'] =collect($client->toarray())->only(['phone', 'full_name']);
		}
		else
		{
			$data['client'] =null;
		}

        return APIController::json_success($data);
    }


    public function add_invoice($subscription_id, request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $invoice = new Invoices();
        $invoice->sid = $subscription_id;
        $invoice->uid = $user_id;
        $invoice->save();
        return APIController::json_empty_success();
    }


}