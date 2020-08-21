<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //
    protected $fillable = [
        'company_name', 'country_id',
    ];

    public function users()
    {
        return $this->belongsToMany('App\User', 'country_user');
    }

    public function country(){
        return $this->belongsTo('App\Country');
    }
}
