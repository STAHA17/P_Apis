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
        'a_name', 'a_category','a_watt','a_consumption','device','user_id'
    ];

    //define user-appliance relationship in model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
