<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $primaryKey = 'hs_object_id';
    protected $fillable = [
        'name',
        'domain',
        'phone',
        'industry',
        'hs_object_id',
    ];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'company_contact', 'company_id', 'contact_id', 'hs_object_id', 'hs_object_id');
    }
}
