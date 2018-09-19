<?php

namespace App\Http\Controllers\API\mobile;


use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Roles;
use Response;
use DB;
use Hash;
use App\Models\Images as Images;
use App\Models\Activation;
use App\Http\Controllers\API\mobile\ActivationController as ActivationController;
use App\Http\Controllers\API\mobile\SMSController as SMSController;
use App\Http\Controllers\API\mobile\APIController as APIController;
use App\Http\Controllers\API\mobile\SocialController as SocialController;
use App\Http\Controllers\API\mobile\ImagesController as ImagesController;
use App\Models\Rates;

class APIUserController extends Controller
{

    public function get_my_profile(request $request, $type)
    {
        $user_id = APIController::get_APIUserID($request);
        $user = User::where('id', $user_id)->first();

        if(count($user)==0)
        {
            return APIController::json_error(10);
        }

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

        $role_id = (int) Roles::where('role', $type)->first()->id;

        if($user->role_id != $role_id)
        {
            return APIController::json_error(12);
        }

        if($type == 'user')
        {
            $photo = ImagesController::get_user_avatar_base64($user->id, 0);
            $data = [
                "full_name" => $user->full_name,
                "email" => $user->email,
                "phone" => $user->phone,
                'rate'=> number_format($rate_data['avg'], 2),
                'user_photo' => [
                    "file_name" => $photo['name'],
                    "file_content" => $photo['fc'],
                    "accepted" => $photo['accepted']
                ],
            ];
        }

        if($type == 'driver')
        {
            $data = [
                "full_name" => $user->full_name,
                "email" => $user->email,
                "phone" => $user->phone,
                'rate'=> number_format($rate_data['avg'], 2),
                "license_number" => $user->license,
                "car_number" => $user->car_number,
                "car_color" => $user->car_color,
                "car_marka" => $user->car_marka
            ];
            $images = Images::where('user_id', $user_id)->get();
            foreach($images as $image)
            {
                $accepted = false;
                if($image->accepted == 1)
                {
                    $accepted = true;
                }

                $type = ImagesController::get_type_str($image->type);
                $fc = ImagesController::get_base64_encoded_image($image->file_name);
                $data[$type] = [
                    "file_name" => $image->file_name,
                    "file_content" => $fc,
                    "accepted" => $accepted
                ];

                if($image->accepted == -1)
                {
                    $data[$type]["accepted"] = false;
                    $data[$type]["comment"] = false;
                }

            }
        }

        return APIController::json_success($data);

    }

    public function get_my_profile_client(request $request)
    {
        return $this->get_my_profile($request, 'user');
    }

    public function get_my_profile_driver(request $request)
    {
        return $this->get_my_profile($request, 'driver');
    }

    public function update_my_profile(request $request, $type)
    {
        $user_id = APIController::get_APIUserID($request);
        $user = User::where('id', $user_id)->first();

        if(count($user)==0)
        {
            return APIController::json_error(10);
        }

        $role_id = (int) Roles::where('role', $type)->first()->id;
        if($user->role_id != $role_id)
        {
            return APIController::json_error(12);
        }

        $full_name = $request->get('full_name');
        $email = $request->get('email');
        $old_password = $request->get('old_password');
        $phone = $request->get('phone');

        if(!empty($old_password)) {
            if (Hash::check($old_password, $user->password))
            {
                $password = Hash::make($request->get('password'));
                $user->password = $password;
            }
            else
            {
                return APIController::json_error(20);
            }
        }

        if(!empty($full_name))
        {
            $user->full_name = $full_name;
        }

        if(!empty($email))
        {
            if($user->email!=$email)
            {
                $search = User::where('email', $email)->first();
                if(count($search)==0)
                {
                    $valid = APIController::isValidEmail($email);
                    if($valid)
                    {
                        $user->email = $email;
                    }
                    else
                    {
                        return APIController::json_error(8, "E-mail is not valid");
                    }
                }
                else
                {
                    return APIController::json_error(2, "E-mail is already used");
                }
            }
        }

        if($type == 'driver')
        {
            $license_number = $request->get('car_number');
			$car_number = $request->get('car_number');
			$car_color = $request->get('car_color');
			$car_marka = $request->get('car_marka');

            if(!empty($license_number))
            {
                $user->license = $license_number;
            }
            if(!empty($car_number))
            {
				$user->car_number = $car_number;
            }
            if(!empty($car_color))
            {
				$user->car_color = $car_color;
            }
            if(!empty($car_marka))
            {
				$user->car_marka = $car_marka;
            }
			

            $photos[0] = $request->get('user_photo');
            $photos[1] = $request->get('license_plate');
            $photos[2] = $request->get('drivers_license');
        }

        if($type == 'user')
        {
            $photos[0] = $request->get("user_photo");
        }

        foreach($photos as $key => $photo)
        {
            if($photo==null)
            {
                continue;
            }
            $img = Images::where('user_id', $user_id)->where('type', $key)->first();
            if(count($img)==0)
            {
                $img = new Images();
                $img->user_id = $user->id;
                $img->type = $key;
                $img->accepted = 0;
            }
            else
            {
                $old_file = $img->file_name;
                if($img->accepted != 0)
                {
                    if($key != 0) {
                        continue;
                    }
                }
                ImagesController::unset_old_file($old_file);
            }

            $fn = ImagesController::generate_file_name($user->id, $photo['file_name'], $key);
            $img->file_name = $fn;
            $content = explode(',', $photo["file_content"]);
            $cnt = count($content)-1;
            $photo["file_content"] = $content[$cnt];
            $photo["file_content"] = str_replace(' ', '+', $photo["file_content"]);
            $strImage = base64_decode($photo["file_content"]);
            $path = ImagesController::get_image_path($fn);
            file_put_contents($path, $strImage);
            $img->save();

            ImagesController::image_orientate($fn);
        }

        if(!empty($phone)) {
            $search_phone = User::where('phone', $phone)->first();
            if (count($search_phone) > 0) {
                return APIController::json_error(6);
            }

            $activation = Activation::where('phone', $phone)->first();
            if (count($activation) == 0 || $activation->activated == 0) {
                return APIController::json_error(19);
            }

            if($activation->activated == 1) {
                $user->phone = $phone;
            }
        }

        $user->save();
        return APIController::json_empty_success();
    }

    public function update_my_profile_driver(request $request)
    {
        return $this->update_my_profile($request, 'driver');
    }

    public function update_my_profile_client(request $request)
    {
        return $this->update_my_profile($request, 'user');
    }
}
