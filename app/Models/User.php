<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Company;
class User extends Authenticatable
{
    use Billable;
	protected $table = 'users';
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'role_id', 'company_id', 'agreement_flag', 'agreement_time', 'first_name', 'middle_name', 'last_name',
        'date_birthday', 'phone', 'country_id', 'state_id', 'city',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'role_id', 'company_id', 'remember_token', 'created_at', 'updated_at'
    ];

    public function Role()
    {
        return $this->hasOne('App\Models\Role', 'id', 'role_id');
    }

    public function Company()
    {
        return $this->hasOne('App\Models\Company', 'id', 'company_id');
    }

    public function Subscriptions()
    {
        return $this->hasMany('App\Models\UserSubscriptions');
    }
    
    public function LoadForShow()
    {
        $this->load(['Company','Role','Subscriptions' => function($query) {
            $query->orderBy('id', 'desc')->first();
        }]);
    }

    public static function create_empty_company($name)
    {
        return Company::create([
            'name'=>$name,
            'isactive'=>1,
//            'created_at'=>Carbon::now()->format('Y-m-d H:i:s'),
//            'updated_at'=>Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }
}
