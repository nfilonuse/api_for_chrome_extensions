<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
	protected $table = 'states';
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'smallname', 'name', 'country_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
