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
        'slot_1',
        'slot_2',
        'slot_3',
        'user_id',];
}
