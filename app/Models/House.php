<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    //
    protected $fillable = ['name'];
    public function housings ()
    {
        return $this->hasMany(Housing::class);
    }
}
