<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $fillable = [
        'housing_id',
        'fee_id', 
        'month', 
        'year',
        'nominal',
        'payment_date', 
        'is_paid'
    ];

    public function housings() 
    {
        return $this->belongsTo(Housing::class); 
    }

    public function fees()
    {
        return $this->belongsTo(Fee::class);
    }

    
}
