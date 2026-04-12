<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    //
    protected $fillable = ['name', 'default_nominal', 'period_type'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
