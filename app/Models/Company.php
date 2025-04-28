<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'phone',
        'industry',
        'hs_object_id',
    ];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class);
    }
}
