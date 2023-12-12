<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appliance extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'a_name',
        'a_watt',
        'a_consumption',
        'a_status',
        'a_IP',
        'a_MAC',
        'user_id'
    ];

    //define user-appliance relationship in model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //define appliance-scheduling relationship in model
    public function appliances()
    {
        return $this->hasMany(Scheduling::class);
    }
    
}
