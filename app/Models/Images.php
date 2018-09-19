<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Images extends Model
{
    protected $table = 'images';

    public function User()
    {
        return $this->belongsTo('App\Modules\User');
    }
    public static function get_image_path($file_name)
    {
        return  public_path().'/user_images/'.$file_name;
    }

    public static function image_url($file_name)
    {
        return  url('/user_images/'.$file_name);
    }

    public static function generate_file_name($user_id, $file_name, $type)
    {
        $arr = explode(".", $file_name);
        $ext = array_pop($arr);

        if($ext!='jpg' && $ext!='jpeg' && $ext!='png' && $ext!='bmp')
            $ext = 'jpg';

        $name = preg_replace('/\.\w+$/', '', $file_name);
        $file_name = str_slug($name).'_'.$type.'.'.$ext;
        return $user_id.'_'.$file_name;
    }

    public static function get_image_url($image_id)
    {
        $file_name = Images::where('id', $image_id)->first()->file_name;
        return url('/user_images/'.$file_name);
    }

    public static function unset_old_file($file_name)
    {
        $path = ImagesController::get_image_path($file_name);
        if( file_exists( $path ) )
        {
            unlink($path);
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function get_images_id($user_id, $type)
    {
        return Images::where('user_id', $user_id)
            ->where('type', $type)->first()->id;
    }

    public static function get_type_str($i)
    {
        $type = '';
        switch($i) {
            case 0: $type = 'user_photo'; break;
            case 1: $type = 'license_plate'; break;
            case 2: $type = 'drivers_license'; break;
        }
        return $type;
    }

    public static function get_base64_encoded_image($file_name, $public_path = true)
    {
        if ($public_path) {
            $img = public_path() . '/user_images/' . $file_name;
        }
        else {
            $img = $file_name;
        }

        if (file_exists($img)) {
            $imageSize = @getimagesize($img);
            $imageData = base64_encode(file_get_contents($img));
            $imageHTML = "data:{$imageSize['mime']};base64,{$imageData}";
            if(empty($imageData))
            {
                $default = self::get_default_avatar_path();
                $imageHTML = self::get_base64_encoded_image($default, false);
            }
        }
        else
        {
            $default = self::get_default_avatar_path();
            $imageHTML = self::get_base64_encoded_image($default, false);
        }
        return $imageHTML;
    }

    public static function get_default_avatar()
    {
        return url('/user_images/default-profile-image.jpg');
    }

    public static function get_default_avatar_path()
    {
        return public_path().'/user_images/default-profile-image.jpg';
    }

    public static function get_user_avatar_base64($user_id)
    {
        $user = User::where('id', $user_id)->first();
        if(count($user)==0)
        {
            $photo['path'] = self::get_default_avatar_path();
            $photo['name'] = 'default.jpg';
            $photo['accepted'] = false;
        }
        else
        {
            $image = Images::where('user_id', $user->id)->where('type', 0)->first();
            if(count($image) > 0)
            {
                $photo['path'] = ImagesController::get_image_path($image->file_name);
                $photo['name'] = $image->file_name;
                $photo['accepted'] = $image->accepted;
            }
            else
            {
                $photo['path'] = ImagesController::get_default_avatar_path();
                $photo['name'] = 'default.jpg';
                $photo['accepted'] = false;
            }
            $photo['fc'] = self::get_base64_encoded_image($photo['path'], false);
            return $photo;
        }

    }

    public static function image_orientate($file_name)
    {
        $file_path = SELF::get_image_path($file_name);
        $orientation=0;
        $f=fopen($file_path,'r');
        $tmp=fread($f, 2);
        if ($tmp==chr(0xFF).chr(0xD8)) {
            $section_id_stop=array(0xFFD8,0xFFDB,0xFFC4,0xFFDD,0xFFC0,0xFFDA,0xFFD9);
            while (!feof($f)) {
                $tmp=unpack('n',fread($f,2));
                $section_id=$tmp[1];
                $tmp=unpack('n',fread($f,2));
                $section_length=$tmp[1];

                // Началась секция данных, заканчиваем поиск
                if (in_array($section_id, $section_id_stop)) {
                    break;
                }

                // Найдена EXIF-секция
                if ($section_id==0xFFE1) {
                    $exif=fread($f,($section_length-2));
                    // Это действительно секция EXIF?
                    if (substr($exif,0,4)=='Exif') {
                        // Определить порядок следования байт
                        switch (substr($exif,6,2)) {
                            case 'MM': {
                                $is_motorola=true;
                                break;
                            }
                            case 'II': {
                                $is_motorola=false;
                                break;
                            }
                        }
                        // Количество тегов
                        if ($is_motorola) {
                            $tmp=unpack('N',substr($exif,10,4));
                            $offset_tags=$tmp[1];
                            $tmp=unpack('n',substr($exif,14,2));
                            $num_of_tags=$tmp[1];
                        }
                        else {
                            $tmp=unpack('V',substr($exif,10,4));
                            $offset_tags=$tmp[1];
                            $tmp=unpack('v',substr($exif,14,2));
                            $num_of_tags=$tmp[1];
                        }
                        if ($num_of_tags==0) { return true; }

                        $offset=$offset_tags+8;

                        // Поискать тег Orientation
                        for ($i=0; $i<$num_of_tags; $i++) {
                            if ($is_motorola) {
                                $tmp=unpack('n',substr($exif,$offset,2));
                                $tag_id=$tmp[1];
                                $tmp=unpack('n',substr($exif,$offset+8,2));
                                $value=$tmp[1];
                            }
                            else {
                                $tmp=unpack('v',substr($exif,$offset,2));
                                $tag_id=$tmp[1];
                                $tmp=unpack('v',substr($exif,$offset+8,2));
                                $value=$tmp[1];
                            }
                            $offset+=12;

                            // Orientation
                            if ($tag_id==0x0112) {
                                $orientation=$value;
                                break;
                            }
                        }
                    }
                }
                else {
                    // Пропустить секцию
                    fseek($f, ($section_length-2), SEEK_CUR);
                }
                // Тег Orientation найден
                if ($orientation) { break; }
            }
        }
        fclose($f);

        $image = imagecreatefromjpeg($file_path);
        if ($orientation) {
            switch($orientation) {
                // Поворот на 180 градусов
                case 3: {
                    $image=imagerotate($image,180,0);
                    break;
                }
                // Поворот вправо на 90 градусов
                case 6: {
                    $image=imagerotate($image,-90,0);
                    break;
                }
                // Поворот влево на 90 градусов
                case 8: {
                    $image=imagerotate($image,90,0);
                    break;
                }
            }
        }

        self::unset_old_file($file_name);
        imagejpeg($image, $file_path);
    }
}
