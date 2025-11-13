<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Decoder extends Model
{
    use HasFactory;
    
    public function plans()
    {
        return $this->hasMany(CablePlan::class);
    }
}
