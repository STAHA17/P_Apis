<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scheduling extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'date',
        'user_id',
        'appliance_id',
    ];


    //define user-scheduling relationship in model
    // public function user()
    // {
    //     return $this->belongsTo(Scheduling::class);
    // }
    public function appliance()
    {
        return $this->belongsTo(Appliance::class, 'appliance_id');
    }
}
