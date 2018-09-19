<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Citation extends Model
{
	protected $table = 'citations';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'col_num', 'line_num', 'patent_id','search_text','status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function Patent()
    {
        return $this->hasOne('App\Models\Patent', 'id', 'patent_id');
    }
}
