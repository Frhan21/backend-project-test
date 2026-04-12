<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outcome extends Model
{
    //
    protected $fillable = [
        'description',
        'total',
        'category_id',
        'outcome_date'
    ];


    public function categories()
    {
        return $this->belongsTo(Category::class);
    }
}
