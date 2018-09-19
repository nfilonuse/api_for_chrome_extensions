<?php

namespace App\Http\Controllers\API\mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Roles;
use Response;
use DB;
use Hash;
use App\Models\Geo;
use App\Models\Option;
use App\Models\Images as Images;
use App\Models\Activation;
use App\Models\Tariff;
use App\Http\Controllers\API\mobile\APIController as APIController;
use App\Helpers\Options;
use App\Models\Orders;
use Log;

class APIGPSController extends Controller
{

    public function get_current_pos(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $driver_id = $request->get('driver_id');

        $geo_stamp = Geo::where('user_id', $driver_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if( count($geo_stamp) == 0)
        {
            return APIController::json_error(16);
        }

        $data['lat'] = $geo_stamp->lat;
        $data['lng'] = $geo_stamp->lng;
        $data['timestamp'] = $geo_stamp->created_at;
        return APIController::json_success($data);
    }

    public function set_pos(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $points = $request->get('points');
        $role_id = (int) Roles::where('role', 'driver')->first()->id;
        $user = User::where('id', $user_id)->first();
        if($user->role_id != $role_id)
        {
            return APIController::json_error(12);
        }

        foreach($points as $key => $point)
        {
            $geo = new Geo();
            if(empty($point['timestamp']))
                $dt = time();
            else
                $dt = $point['timestamp'];
            $geo->user_id = $user_id;
            $geo->lat = $point['lat'];
            $geo->lng = $point['lng'];
            $geo->created_at = $dt;
            $geo->save();
        }
        return APIController::json_empty_success();
    }

    public function get_pos(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $driver_id = $request->get('driver_id');
        $time_from = $request->get('time_from');
        $time_to = $request->get('time_to');

        $geo_stamps = Geo::where('created_at', '>', $time_from)
            ->where('created_at', '<', $time_to)
            ->where('user_id', $driver_id)
            ->get();
        $data = [];

        if( count($geo_stamps) == 0)
        {
            return APIController::json_error(16);
        }

        foreach($geo_stamps as $key => $geo_stamp)
        {
            $data[$key]['lat'] = $geo_stamp->lat;
            $data[$key]['lng'] = $geo_stamp->lng;
            $data[$key]['timestamp'] = $geo_stamp->created_at;
        }

        return APIController::json_success($data);
    }

    public static function get_drivers_in_radius($point, $radius, $user_info = false)
    {
        $center_lat = $point['lat'];
        $center_lng = $point['lng'];
        $time = time();
        $min_time = $time - 300;

        $query = "SELECT      a.user_id, a.lat, a.lng, a.created_at, ";
        $query.= "( 6371 * acos( cos( radians('".$center_lat."') ) * cos( radians( a.lat ) ) * cos( radians( a.lng ) - radians('".$center_lng."') ) + sin( radians('".$center_lat."') ) * sin( radians( a.lat ) ) ) ) as distance ";
        $query.= "FROM        driver_geo AS a ";
        $query.= "LEFT JOIN   driver_geo AS c ON ( ";
        $query.= "SELECT      MIN(id) ";
        $query.= "FROM        driver_geo AS b ";
        $query.= "WHERE       b.id > a.id ";
        $query.= " ) = c.id ";
        $query.= "WHERE (a.user_id <> c.user_id OR c.user_id IS NULL) AND ";
        $query.= "( 6371 * acos( cos( radians('".$center_lat."') ) * cos( radians( a.lat ) ) * cos( radians( a.lng ) - radians('".$center_lng."') ) + sin( radians('".$center_lat."') ) * sin( radians( a.lat ) ) ) ) < ".$radius." AND a.created_at > ".$min_time." AND a.created_at < ".$time." ";
        $query.= "ORDER BY a.created_at DESC";

        $results = DB::select( DB::raw($query) );
        $data = [];
        $users = [0];
        foreach($results as $key => $result)
        {
            $res = array_search($result->user_id, $users);
            if(!$res)
            {
                $users[] = $result->user_id;
            }
            else
            {
                continue;
            }

            $data[$key]['lat'] = $result->lat;
            $data[$key]['lng'] = $result->lng;
            if($user_info)
            {
                $data[$key]['distance'] = $result->distance;
                $data[$key]['user_id'] = $result->user_id;
            }
        }
        return $data;
    }

    public function get_drivers_list(request $request)
    {
        $user_id = APIController::get_APIUserID($request);
        $radius = Option::where('name', 'search_radius')->first()->value;
        $radius_m = $radius / 1000;
        $point=[];
        $point['lat'] = $request->get('lat');
        $point['lng'] = $request->get('lng');

        $input = APIGPSController::get_drivers_in_radius($point, $radius_m, true);
        $data = [];
        foreach($input as $item)
        {
            if(! APIController::is_in_blacklist($user_id, $item['user_id']))
                $data[] = ["lat"=>$item['lat'], "lng"=>$item['lng']];
        }
        return APIController::json_success($data);
    }

    public static function get_lenght($from, $to)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$from->lat.",".$from->lng."&destinations=".$to->lat.",".$to->lng;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // times out after 4s
        curl_setopt($ch, CURLOPT_POST, 1); // set POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, "url=index%3Dbooks&field-keywords=PHP+MYSQL"); // add POST fields
        $result = curl_exec($ch); // run the whole process
        curl_close($ch);
        $json = json_decode($result);
        if($json->status == 'OK') {
            if(!empty($json->rows[0]->elements[0]->distance->value))
            {
                $m = $json->rows[0]->elements[0]->distance->value;
            }
            else
            {
                $m = 0;
            }
            return $m;
        }
        else
        {
            return false;
        }
    }

    public static function get_zoom_gmap($l)
    {
        if($l>=0 && $l<500)
        {
            return 17;
        }

        if($l>=500 && $l<1000)
        {
            return 15;
        }

        if($l>=1000 && $l<2000)
        {
            return 13;
        }

        if($l>=2000 && $l<4000)
        {
            return 13;
        }

        if($l>=4000 && $l<8000)
        {
            return 12;
        }

        if($l>=8000)
        {
            return 11;
        }
    }

    public function get_price(request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $from['lat'] . "," . $from['lng'] . "&destinations=" . $to['lat'] . "," . $to['lng'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // times out after 4s
        curl_setopt($ch, CURLOPT_POST, 1); // set POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, "url=index%3Dbooks&field-keywords=PHP+MYSQL"); // add POST fields
        $result = curl_exec($ch); // run the whole process
        curl_close($ch);
        $json = json_decode($result);
        if (isset($json->rows[0]))
        {
            if ($json->rows[0]->elements[0]->status == 'OK') {
                $m = $json->rows[0]->elements[0]->distance->value;
                $tariff_id = $request->get('tariff_id');
                $tariff = Tariff::where('id', $tariff_id)->first();

                /* ���� �������� tariff_id */
                if (empty($tariff) || empty($tariff_id)) {
                    $price_per_km = Option::where('name', 'price_per_km')->first()->value;
                    $base_price = Option::where('name', 'base_price')->first()->value;
                    $base_distance_m = Option::where('name', 'base_distance_m')->first()->value;
                } else {
                    $price_per_km = $tariff->extend_price;
                    $base_price = $tariff->base_price;
                    $base_distance_m = $tariff->base_distance;
                }

                $extended_tarif = 0;

                if ($m <= $base_distance_m) {
                    $price = $base_price;
                } else {
                    $price_per_m = $price_per_km / 1000;
                    $extended_tarif = ($m - $base_distance_m) * $price_per_m;
                    $price = $base_price + $extended_tarif;
                }

                $price = (float)round($price, 2);

                $data = [
                    "price" => $price,
                    "lenght" => $m,
                    "bd" => $base_distance_m,
                    "bp" => $base_price,
                    "et" => $extended_tarif,

                ];
                return APIController::json_success($data);
            }
            else
            {
                return APIController::json_error(16, 'Google.maps cant find any routes');
            }
        }
        else
        {
            return APIController::json_error(16, 'Google.maps cant find any routes');
        }

    }


    public function SendNotifications(request $request)
    {

    }




}