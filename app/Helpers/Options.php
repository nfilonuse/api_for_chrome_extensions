<?php

namespace App\Helpers;

use App\Option;
use Auth;
use App\Subscriptions;
use App\Http\Controllers\APIOptionController;

class Options
{
    public static function get($name)
    {
        $op = Option::where('name', $name)->first();
        if(count($op)>0)
        {
            return $op->value;
        }
        else
        {
            return '';
        }
    }

    public static function input_option($name, $value, $type, $params)
    {

        $p = [];
        if(!empty($params))
        {
            $p_array = explode(';', $params);
            foreach($p_array as $item)
            {
                $d = explode(':', $item);
                if(isset($d[1])) {
                    $p[$d[0]] = $d[1];
                }
            }
        }

        switch ($type) {
            case 1: return Options::generate_text_input($name, $value, $p); break;
            case 2: return Options::generate_number_input($name, $value, $p); break;
            case 3: return Options::generate_textarea_input($name, $value, $p); break;
            case 4: return Options::generate_select_input($name, $value, $p); break;
            case 5: return Options::generate_select_subscription($name, $value, $p); break;
            case 6: return Options::generate_select_period($name, $value, $p); break;
        }

        return '';
    }

    public static function generate_text_input($name, $value, $p)
    {
        $html = '<input type="text" id="input_'.$name.'" value="'.$value.'" name="'.$name.'"';
        foreach($p as $key => $item)
        {
            $html .= $key.'="'.$item.'"';
        }
        $html .= '>';
        return $html;
    }

    public static function generate_number_input($name, $value, $p)
    {
        $html = '<input type="number" id="input_'.$name.'" value="'.$value.'" name="'.$name.'"';
        foreach($p as $key => $item)
        {
            $html .= $key.'="'.$item.'"';
        }
        $html .= '>';
        return $html;
    }

    public static function generate_textarea_input($name, $value, $p)
    {
        $html = '<textarea type="number" id="input_'.$name.'" name="'.$name.'"';
        foreach($p as $key => $item)
        {
            $html .= $key.'="'.$item.'"';
        }
        $html .= '>'.$value.'</textarea>';
        return $html;
    }

    public static function generate_select_input($name, $value, $p)
    {
        $html = '<select id="input_'.$name.'" name="'.$name.'"';
        foreach($p as $key => $item)
        {
            if($key!='options')
            $html .= $key.'="'.$item.'"';
        }

        $html .= '>';

        $options = explode("||", $p['options']);
        foreach($options as $option)
        {
            $data = explode('|', $option);
            if($data[0]!=$value)
            {
                $html .='<option value="'.$data[0].'">'.$data[1].'</option>';
            }
            else
            {
                $html .='<option value="'.$data[0].'" selected>'.$data[1].'</option>';
            }
        }
        $html .='</select>';
        return $html;
    }

    public static function generate_select_subscription($name, $value, $p)
    {
        $html = '<select id="input_'.$name.'" name="'.$name.'"';
        foreach($p as $key => $item)
        {
            if($key!='options')
                $html .= $key.'="'.$item.'"';
        }

        $html .= '>';

        $subscriptions = Subscriptions::all();
        foreach($subscriptions as $subscription)
        {
            if($subscription->id != $value)
            {
                $html .='<option value="'.$subscription->id.'">'.$subscription->name.'</option>';
            }
            else
            {
                $html .='<option value="'.$subscription->id.'" selected>'.$subscription->name.'</option>';
            }
        }
        $html .='</select>';
        return $html;
    }


    public static function generate_select_period($name, $value, $p)
    {
        $html = '<select id="input_'.$name.'" name="'.$name.'"';
        foreach($p as $key => $item)
        {
            if($key!='options')
                $html .= $key.'="'.$item.'"';
        }

        $html .= '>';

        $periods = APIOptionController::get_periods_array();
        $times = APIOptionController::get_periods_times();
        foreach($periods as $key => $period)
        {
            if($times[$key] != $value)
            {
                $html .='<option value="'.$times[$key].'">'.$period.'</option>';
            }
            else
            {
                $html .='<option value="'.$times[$key].'" selected>'.$period.'</option>';
            }
        }
        $html .='</select>';
        return $html;
    }

    public static function can_admin($permision)
    {
        $level = Auth::guard("admin_users")->user()->level;
        if($permision <= $level)
            return true;
        else
            return false;
    }

}

?>