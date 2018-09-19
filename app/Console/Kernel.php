<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
use App\Models\DriverSubscription;
use App\Models\Subscriptions;
use Illuminate\Support\Facades\View;
use App\Models\Views;
use App\Models\Blacklist;
use App\Models\Avaible;
use App\Http\Controllers\API\mobile\APIOptionController;
use App\Http\Controllers\PushController;



class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {

            /* All active orders where notification have not been send */
            //Log::info('Cron started :'.date("Y-m-d H:i:s"));
            $orders = Orders::where('active_client', 1)
                ->where('active_driver', 1)
                ->where('notification_radius', 0)
                ->get();

            foreach($orders as $order) {
                $points = json_decode($order->points);
                $start_point = $points[0];
                $driver_point = Geo::where('user_id', $order->driver_id)->orderBy('created_at', 'desc')->first();

                $client_notify_radius = Option::where('name', 'client_notify_radius')->first()->value;

                $distance = SELF::getDistance(doubleval($start_point->lat), doubleval($start_point->lng), doubleval($driver_point->lat), doubleval($driver_point->lng));
		        //Log::info($order->id.' '.$distance);
                if ($distance <= $client_notify_radius)
                {
                    $order->notification_radius = 1;
                    $order->save();
                    PushController::client_notify_radius($order->user_id, $order->driver_id);
                }
            }

            return true;
        })->everyMinute();

        $schedule->call(function () {
            set_time_limit(90);
            //Log::info('Cleanup started cron:'.date("Y-m-d H:i:s"));

            $expire = Option::where('name', 'empty_order_lifetime')->first()->value;
            $orders = Orders::where('active_client', 1)
            ->where('active_driver', 0)->get();

            $time = time();
            $subscriptions = DriverSubscription::where('active', 1)->get();
            foreach($subscriptions as $s)
            {
                $added = APIController::StrTounixTime($s->added_on);
                $ds = Subscriptions::where('id', $s->subscriptions_id)->first();
                $expire = APIOptionController::calculate_expire($added, $ds->expiration_time, $ds->expiration_period);
                if($expire < $time)
                {
                    $s->active = 0;
                    $s->save();
                }
            }

            $max_time = time() - $expire;
            foreach($orders as $order)
            {
                $order_time = strtotime($order->created_at);
                if($order_time > $max_time)
                {
                    PushController::delete_order($order->user_id, $order->id);
                    $order->active_client = 0;
                    $order->save();
                    //$order->delete();
                }
            }


            $stat_remove_time = Option::where('name', 'stat_remove_time')->first()->value;
            $max_time = time() - $stat_remove_time;
            $views = Views::all();
            foreach($views as $view)
            {
                $view_time = strtotime($view->date);
                if($view_time < $max_time)
                {
                    $view->delete();
                }
            }

            $geo_data = Geo::take(25000)->oldest()->get();
            $removed = 0;
            foreach($geo_data as $g)
            {
                $g_time = $g->created_at;
                if($g_time < $max_time)
                {
                    $g->delete();
                    $removed++;
                }
            }

            return true;
        })->everyTenMinutes();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }


    public static function getDistance($φA, $λA, $φB, $λB) {
        // перевести координаты в радианы
        $lat1 = $φA * M_PI / 180;
        $lat2 = $φB * M_PI / 180;
        $long1 = $λA * M_PI / 180;
        $long2 = $λB * M_PI / 180;

        // косинусы и синусы широт и разницы долгот
        $cl1 = cos($lat1);
        $cl2 = cos($lat2);
        $sl1 = sin($lat1);
        $sl2 = sin($lat2);
        $delta = $long2 - $long1;
        $cdelta = cos($delta);
        $sdelta = sin($delta);

        // вычисления длины большого круга
        $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
        $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

        //
        $ad = atan2($y, $x);
        $dist = $ad * 6372795;

        return $dist;
    }

}
