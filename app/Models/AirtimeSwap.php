<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirtimeSwap extends Model
{
    use HasFactory;
    public function network()
    {
        return $this->belongsTo(Network::class);
    }
}
