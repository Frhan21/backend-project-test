<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Housing extends Model
{
    //
    protected $fillable = ['resident_id', 'house_id', 'start_date', 'end_date', 'is_active'];

    public function residents()
    {
        return $this->belongsTo(Resident::class);
    }

    public function houses()
    {
        return $this->belongsTo(House::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class); 
    }
}

