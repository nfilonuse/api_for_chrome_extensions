<?php

namespace App\Http\Controllers\API\mobile;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\APIController as APIController;

use Faker\Provider\Image;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Auth;

use App\Models\User;
use App\Models\Roles;
use Response;
use DB;
use Hash;
use App\Models\Images as Images;
use App\Models\Activation;
//use App\Http\Controllers\API\mobile\ActivationController as ActivationController;
//use App\Http\Controllers\API\mobile\SMSController as SMSController;
//use App\Http\Controllers\API\mobile\APIController as APIController;
//use App\Http\Controllers\API\mobile\SocialController as SocialController;
//use App\Http\Controllers\API\mobile\ImagesController as ImagesController;
use App\Models\Rates;
use App\Models\Restores;
use Log;

class APIAuthController extends Controller
{

    public $msg_confirm_prefix = 'Confirm code is : %code%'; // %code% will be replaced with sms-code
    public $msg_restore_password = 'Your new password is : %password%'; // %code% will be replaced with sms-code

	/**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api', ['except' => ['login']]);
    }

    public function auth(request $request, $type)
    {

		$credentials = $request->only('email', 'password');
		try {
            if (!$access_token = JWTAuth::attempt($credentials)) {
                return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'message' => 'Could not create token'], 500);
        }
		$user=Auth::user();
		$user->load('Company');
		$user->load('Role');

		$data = [
			'api_token' => $access_token,
			'user'=> $user->toarray(),
		];
		return APIController::json_success($data);

/*
        if (Auth::attempt($request->only('email', 'password'))) {

		$user=Auth::user();
			print_r($user);
			exit;
		}
		else{
            return APIController::json_error(1, 'E-mail or password is wrong');
		}
/*
        $email = $request->get('email');
        $password = $request->get('password');
        $device_type = $request->get('device_type');
        $gcm_token = $request->get('gcm_token');
        $data = ['email' => $email, 'password' => $password];
        if (Auth::attempt($data)) {
            $user = User::where('email', $email)->first();
            if($user->is_active==0)
            {
                return APIController::json_error(11);
            }

            $client_role_id = (int) Roles::where('role', $type)->first()->id;
            $user_role_id = (int) $user->role_id;
            if($user_role_id!=$client_role_id)
            {
                return APIController::json_error(12);
            }

            $token = APIController::generate_api_token();
            $user->api_token = $token;
            if($device_type!=$user->device_type)
            {
                $user->device_type = $device_type;
            }

            if($gcm_token!=$user->gcm_id)
            {
                if($gcm_token!=null)
                    $user->gcm_id = $gcm_token;
            }

            $user->save();
            $activation = Activation::where('phone', $user->phone)->first();
            if(count($activation)==0)
            {
                $activated = false;
            }
            else
            {
                if($activation->activated==0)
                {
                    $activated = false;
                }
                else
                {
                    $activated = true;
                }
            }

            $rates = Rates::where('to', $user->id)->get();
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

            $user_array = [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'was_active_phone' => $activated,
                'rate'=> number_format($rate_data['avg'], 2)
            ];


            $images = Images::where('user_id', $user->id)->get();
            foreach($images as $image)
            {
                $accepted = false;
                if($image->accepted == 1)
                {
                    $accepted = true;
                }

                $type_data = ImagesController::get_type_str($image->type);
                $fc = ImagesController::get_base64_encoded_image($image->file_name);
                $user_array[$type_data] = [
                    "file_name" => $image->file_name,
                    "file_content" => $fc,
                    "accepted" => $accepted
                ];

                if($image->accepted == -1)
                {
                    $user_array[$type_data]["accepted"] = false;
                    $user_array[$type_data]["comment"] = false;
                }

            }
            if($type == 'driver')
            {
                $user_array['license'] = $user->license;
            }

            $data = [
                'api_token' => $token,
                'user'=> $user_array,
            ];
            return APIController::json_success($data);
        } else {
            return APIController::json_error(1, 'E-mail or password is wrong');
        }
*/
    }

    /*
    * v1/client/auth
    * POST :
    * email
    * password
    * device_type
    * gcm_token
    */
    public function auth_client(request $request)
    {
        return $this->auth($request, 'user');
    }


    /*
    * v1/driver/auth
    * POST :
    * email
    * password
    * device_type
    * gcm_token
    */
    public function auth_driver(request $request)
    {
        return $this->auth($request, 'driver');
    }


    public function register(request $request, $type)
    {
        $email = $request->get('email');
        $name = $request->get('full_name');
        $phone = $request->get('phone');

        $password = Hash::make($request->get('password'));
        $role_id = (int) Roles::where('role', $type)->first()->id;
        $user_id = APIController::get_APIUserID($request);

        if(empty($email) || empty($name) || empty($password) || empty($role_id) || empty($phone))
        {
            return APIController::json_error(8, 'You must input all required fields');
        }

        $agreement = $request->get('agreement');
        if(empty($agreement) || $agreement!=1)
        {
            return APIController::json_error(8, 'You must agree with the Agreement');
        }

        $search_phone = User::where('phone', $phone)->first();
        if( count($search_phone) > 0)
        {
            return APIController::json_error(6, 'User with this phone is already exist');
        }

        $activation = Activation::where('phone', $phone)->first();
        if(count($activation) == 0 || $activation->activated == 0)
        {
            return APIController::json_error(11, 'Your phone is not activated right now');
        }

        $device_type = $request->get('device_type');
        $gcm_token = $request->get('gcm_token');

        if($user_id)
        {
            $user = User::where('id', $user_id)->first();
            if($user->email != $email){
                $user->email = $email;
            }
            if($user->name != $name){
                $user->name = $name;
            }
            $user->full_name = $name;

            $user->phone = $phone;
            $user->password = $password;

            if($device_type!=$user->device_type  && !empty($device_type))
            {
                $user->device_type = $device_type;
            }

            if($gcm_token!=$user->gcm_id && !empty($gcm_token))
            {
                if($gcm_token!=null)
                    $user->gcm_id = $gcm_token;
            }


            $token = APIController::generate_api_token();
            $user->api_token = $token;
            $user->agreement_flag = 1;
            $user->agreement_time = date("Y-m-d H:i:s");
            $user->save();
            $activation = Activation::where('phone', $user->phone)->first();
            if(count($activation)==0)
            {
                $activated = false;
            }
            else
            {
                if($activation->activated==0)
                {
                    $activated = false;
                }
                else
                {
                    $activated = true;
                }
            }

            $rates = Rates::where('to', $user->id)->get();
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


            $user_array = [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'was_active_phone' => $activated,
                'rate'=> number_format($rate_data['avg'], 2)
            ];

            $images = Images::where('user_id', $user->id)->get();
            foreach($images as $image)
            {
                $accepted = false;
                if($image->accepted == 1)
                {
                    $accepted = true;
                }

                $type = ImagesController::get_type_str($image->type);
                $fc = ImagesController::get_base64_encoded_image($image->file_name);
                $user_array[$type] = [
                    "file_name" => $image->file_name,
                    "file_content" => $fc,
                    "accepted" => $accepted
                ];

                if($image->accepted == -1)
                {
                    $user_array[$type]["accepted"] = false;
                    $user_array[$type]["comment"] = false;
                }

            }

            if($type == 'driver')
            {
				$user_array['car_number'] = $user->car_number;
				$user_array['car_color'] = $user->car_color;
				$user_array['car_marka'] = $user->car_marka;
                $user_array['license'] = $user->car_number;
            }

            $data = [
                'api_token' => $token,
                'user'=> $user_array,
            ];

            return APIController::json_success($data);
        }


        if($type=='driver')
        {
			$car_number = $request->get('car_number');
			$car_color = $request->get('car_color');
			$car_marka = $request->get('car_marka');
            $license_number = $request->get('car_number');
            $photos[0] = $request->get('user_photo');
            $photos[1] = $request->get('license_plate');
            $photos[2] = $request->get('drivers_license');
            if(empty($license_number) || empty($photos[0]) || empty($photos[1]) || empty($photos[2]))
            {
                return APIController::json_error(8, 'You must input all required fields and upload photos');
            }
        }


        $search = count(
            User::where('email', $email)->get()
        );
        if($search>0)
        {
            return APIController::json_error(2, 'User with this e-mail is already exist');
        }

        $token = APIController::generate_api_token();
        $user = new User();
        $user->email = $email;
        $user->password = $password;
        $user->name = $name;
        $user->device_type = $device_type;
        $user->gcm_id = $gcm_token;

        $user->agreement_flag = 1;
        $user->agreement_time = date("Y-m-d H:i:s");

        $result = $user->save();
        if($result)
        {
            $user = User::where('email', $email)->first();
            $user->role_id = $role_id;
            $user->api_token = $token;
            $user->full_name = $name;
            $user->phone = $phone;
            if($type=='driver')
            {
                $user->license = $license_number;
				$user->car_number = $car_number;
				$user->car_color = $car_color;
				$user->car_marka = $car_marka;
				
                foreach($photos as $key => $photo)
                {
                    $img = new Images();
                    $img->user_id = $user->id;
                    $fn = ImagesController::generate_file_name($user->id, $photo['file_name'], $key);
                    $img->file_name = $fn;
                    $img->accepted = 0;
                    $img->type = $key;
                    $photo["file_content"] = str_replace('data:image/png;base64,', '', $photo["file_content"]);
                    $photo["file_content"] = str_replace('data:image/jpeg;base64,', '', $photo["file_content"]);
                    $photo["file_content"] = str_replace('data:image/jpg;base64,', '', $photo["file_content"]);
                    $photo["file_content"] = str_replace('data:image/bmp;base64,', '', $photo["file_content"]);
                    $photo["file_content"] = str_replace(' ', '+', $photo["file_content"]);
                    $strImage = base64_decode($photo["file_content"]);
                    $path = ImagesController::get_image_path($fn);
                    file_put_contents($path, $strImage);
                    $img->save();

                    ImagesController::image_orientate($fn);
                }
            }
            $user->save();


            $token = APIController::generate_api_token();
            $user->api_token = $token;
            $activation = Activation::where('phone', $user->phone)->first();
            if(count($activation)==0)
            {
                $activated = false;
            }
            else
            {
                if($activation->activated==0)
                {
                    $activated = false;
                }
                else
                {
                    $activated = true;
                }
            }

            $rates = Rates::where('to', $user->id)->get();
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

            $fn = ImagesController::get_default_avatar_path();
            $fc = ImagesController::get_base64_encoded_image($fn, false);

            $user_array = [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_photo' =>
                    [
                        "file_name"=>'default.jpg',
                        "file_content"=>$fc,
                        "accepted" => false,
                    ],
                'was_active_phone' => $activated,
            ];
            $data = [
                'api_token' => $token,
                'user'=> $user_array,
                'rate'=> number_format($rate_data['avg'], 2)
            ];

            return APIController::json_success($data);
        }
        else
        {
            return APIController::json_error(3);
        }
    }

    /*
    * v1/client/register
    * Post :
    * email
    * phone
    * password
    * name
    */
    public function register_client(request $request)
    {
        return $this->register($request, 'user');
    }

    /*
    * v1/driver/register
    * Post :
    * email
    * phone
    * password
    * name
    */
    public function register_driver(request $request)
    {
        return $this->register($request, 'driver');
    }

    /*
    * v1/auth/sendActivation
    * Authorization: Bearer <api_token>
    * Post :
    * user_id
    * phone
    */
    public function setActivation(request $request, $type)
    {
        //$user_id = APIController::get_APIUserID($request);
        $phone = $request->get('phone');
        $user_search = User::where('phone', $phone)->first();


        if(count($user_search)==0)
        {
            $search = Activation::where('phone', $phone)->first();
            if(count($search) == 0)
            {
                //Creating new activation code
                $activation = new Activation();
                $activation->phone = $phone;
                $code = rand(111111, 999999);
                $activation->confirm_code = $code;
                $activation->save();
            }
            else
            {
                //re-generate activation code
                $code = rand(111111, 999999);
                $search->confirm_code = $code;
                $search->save();
            }

            $msg = str_replace('%code%', $code, $this->msg_confirm_prefix);
            $sms_res = smsController::sendSMS($phone, $msg);

            if($sms_res)
            {
                $data =
                [
                    'confirm_code' => $code,
                ];
                return APIController::json_success($data);
            }
            else
            {
                return APIController::json_error(5);
            }
        }
        else
        {
            return APIController::json_error(6, "You can't use this phone number, because it's already used by another user");
        }
    }

    public function setActivation_client(request $request)
    {
        return $this->setActivation($request, 'user');
    }

    public function setActivation_driver(request $request)
    {
        return $this->setActivation($request, 'driver');
    }

    /*
    * v1/auth/activate
    * Authorization: Bearer <api_token>
    * Post :
    * user_id
    * confirm_code
    */
    public function activate(request $request)
    {
        $confirm_code = $request->get('confirm_code');
        $phone = $request->get('phone');
        if(count(User::where('phone', $phone)->get())==0)
        {
            $search = Activation::where('phone', $phone)->first();
            if(count($search) > 0)
            {
                if($search->confirm_code == $confirm_code)
                {
                    $search->activated = 1;
                    $search->save();
                    return APIController::json_empty_success();
                }
                else
                {
                    return APIController::json_error(4, 'The activation code is wrong. You can find correct code in sms messages in one-two minutes');
                }
            }
            else
            {
                return APIController::json_error(13);
            }
        }
        else
        {
            return APIController::json_error(6);
        }
    }

    public function activate_client(request $request)
    {
        return $this->activate($request, 'user');
    }

    public function activate_driver(request $request)
    {
        return $this->activate($request, 'driver');
    }

    /*
    * v1/auth/social_reg
    */
    public function social_reg(request $request, $social_user)
    {
        $social['network'] = $request->get('social_type');
        $social['token'] = $request->get('social_user_token');
        if(empty($social['network']) || empty($social['token']))
        {
            return APIController::json_error(8, 'Social network error');
        }
/*
        $agreement = $request->get('agreement');
        if(empty($agreement) || $agreement==0)
        {
            return APIController::json_error(8, 'You must agree with the Agreement');
        }
*/
		$request['agreement']=1;
        $social_data = User::where('social_id', $social_user['id'] )
            ->where('social_network', $social['network'] )
            ->first();
        if(count($social_data)==0)
        {
            /*
             * register new user form SOCIAL Data from token
             */

            $password = Hash::make(str_random());

            $search = count(
                User::where('email', $social_user['email'])
                    ->get()
            );

            if($search>0)
            {
                return APIController::json_error(2, 'This E-mail is already in use');
            }

            $token = APIController::generate_api_token();
            $user = new User();
            $user->email = $social_user['email'];
            $user->password = $password;
            $user->name = $social_user['email'];
            $result = $user->save();
            if($result)
            {
                $user = User::where('email', $social_user['email'])->first();
                $role_id = (int) Roles::where('role', 'user')->first()->id;
                $user->role_id = $role_id;
                $user->api_token = $token;
                $user->full_name = $social_user['full_name'];
                $user->social_network = $social['network'];
                $user->social_id = $social_user['id'];
                $user->save();

                $rates = Rates::where('to', $user->id)->get();
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

                $user_array = [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'was_active_phone' => false,
                ];

                $images = Images::where('user_id', $user->id)->get();
                foreach($images as $image)
                {
                    $accepted = false;
                    if($image->accepted == 1)
                    {
                        $accepted = true;
                    }

                    $type_data = ImagesController::get_type_str($image->type);
                    $fc = ImagesController::get_base64_encoded_image($image->file_name);
                    $user_array[$type_data] = [
                        "file_name" => $image->file_name,
                        "file_content" => $fc,
                        "accepted" => $accepted
                    ];

                    if($image->accepted == -1)
                    {
                        $user_array[$type_data]["accepted"] = false;
                        $user_array[$type_data]["comment"] = false;
                    }

                }


                if($social_user['empty_email'])
                {
                    $user_array['email'] = '';
                }
                $data =
                    [
                        'api_token' => $token,
                        'user' => $user_array,
                        'rate' => number_format($rate_data['avg'], 2)
                    ];
                return APIController::json_success($data);
            }

        }
        else
        {
            return APIController::json_error(7, 'This social network user is already registered');
        }
    }

    /*
    * v1/auth/social_auth
    */
    public function social_auth(request $request)
    {
        $social = [];
        $social['network'] = $request->get('social_type');
        $social['token'] = $request->get('social_user_token');
        if(empty($social['network']) || empty($social['token']))
        {
            return APIController::json_error(8, 'Social network error');
        }
        $s = new SocialController();
        $social_input = $s->get_social_info($social['network'], $social['token']);

        $social_user = User::where('social_id', $social_input['id'] )
            ->where('social_network', $social['network'] )
            ->first();
	
        if(count($social_user)==0)
        {
            return $this->social_reg($request, $social_input);
        }
        else
        {
            $api_token = APIController::generate_api_token();
            $social_user->api_token = $api_token;

            $device_type = $request->get('device_type');
            $gcm_token = $request->get('gcm_token');

            if($device_type!=$social_user->device_type)
            {
                $social_user->device_type = $device_type;
            }

            if($gcm_token!=$social_user->gcm_id)
            {
                if($gcm_token!=null)
                    $social_user->gcm_id = $gcm_token;
            }

            $social_user->save();
            $activation = Activation::where('phone', $social_user->phone)->first();

            $rates = Rates::where('to', $social_user->id)->get();
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
            if(count($activation)==0)
            {
                $activate = false;
            }
            else
            {
                $activate = (boolean) $activation->activated;
            }

            $user_array = [
                'full_name' => $social_user->full_name,
                'email' => $social_user->email,
                'phone' => $social_user->phone,
                'was_active_phone' => (boolean) $activate,
                'rate'=> number_format($rate_data['avg'], 2)
            ];

            $images = Images::where('user_id', $social_user->id)->get();
            foreach($images as $image)
            {
                $accepted = false;
                if($image->accepted == 1)
                {
                    $accepted = true;
                }

                $type_data = ImagesController::get_type_str($image->type);
                $fc = ImagesController::get_base64_encoded_image($image->file_name);
                $user_array[$type_data] = [
                    "file_name" => $image->file_name,
                    "file_content" => $fc,
                    "accepted" => $accepted
                ];

                if($image->accepted == -1)
                {
                    $user_array[$type_data]["accepted"] = false;
                    $user_array[$type_data]["comment"] = false;
                }

            }

        
            $data =
                [
                    'api_token' => $api_token,
                    'user' => $user_array,
                ];
            return APIController::json_success($data);
        }

    }

    public function set_token(request $request, $type)
    {
        $device_type = $request->get('device_type');
        $gcm_token = $request->get('gcm_token');
        $user_id = APIController::get_APIUserID($request);
        $user = User::where('id', $user_id)->first();

        if($device_type!=$user->device_type || $gcm_token!=$user->gcm_id)
        {
            $user->device_type = $device_type;

            if($gcm_token!=null)
                $user->gcm_id = $gcm_token;

            $user->save();
        }
        return APIController::json_empty_success();
    }

    public function set_token_client(request $request)
    {
        return $this->set_token($request, 'user');
    }

    public function set_token_driver(request $request)
    {
        return $this->set_token($request, 'driver');
    }

    public function restore(request $request, $type)
    {
        $phone = $request->get('phone');
        $dt_min = date('Y-m-d 00:00:00');
        $dt_max = date('Y-m-d 00:00:00', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));

        $user = User::where('phone', $phone)->first();
        if(count($user) == 0)
        {
            return APIController::json_error(10, 'User with this phone is not found');
        }


        $restore = Restores::where('phone', $phone)
            ->where('created_at', '>=', $dt_min)
            ->where('created_at', '<', $dt_max)
            ->first();


        if(count($restore)==0)
        {
            $cnt = 0;
            $restore = new Restores();
            $restore->phone = $phone;
            $restore->created_at = $dt_min;
        }
        else
        {
            $cnt = $restore->count;
        }

        if($user->Roles->role != $type)
        {
            return APIController::json_error(10, "There is no ".$type." with this phone number");
        }


        if($cnt<3)
        {
            $password = str_random(8);
            $message = str_replace('%password%', $password, $this->msg_restore_password);
            $restore->count = $cnt+1;
            $restore->save();
            $password_hashed = Hash::make($password);
            $user->password = $password_hashed;
            $user->save();
            $res = SMSController::sendSMS($phone, $message);
            if(!$res)
            {
                return APIController::json_error(5);
            }

            return APIController::json_empty_success();
        }
        else
        {
            return APIController::json_error(15, 'You can send only 3 restore message today... Try again tomorrow');
        }
    }

    public function restore_client(request $request)
    {
        return $this->restore($request, 'user');
    }

    public function restore_driver(request $request)
    {
        return $this->restore($request, 'driver');
    }
}
?>
