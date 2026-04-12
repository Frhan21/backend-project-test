<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    //
    protected $fillable = [
        'name', 'ktp_image','status', 'no_telp', 'is_married'
    ];

    public function housings() {
        return $this->hasMany(Housing::class); 
    }
}
