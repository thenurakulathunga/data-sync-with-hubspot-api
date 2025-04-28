<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'company',
        'hs_object_id',
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }
}
