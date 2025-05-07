<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $primaryKey = 'hs_object_id';
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
        return $this->belongsToMany(Company::class, 'company_contact', 'contact_id', 'company_id', 'hs_object_id', 'hs_object_id');
    }
}
