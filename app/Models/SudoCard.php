<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SudoCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sudo_card_id',
        'last4',
        'card_type',
        'status',
    ];
}
