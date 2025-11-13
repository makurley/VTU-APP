<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EduTrx extends Model
{
    use HasFactory;
    public function user()
    {
    return $this->belongsTo(User::class);
    }
    public function education()
    {
    return $this->belongsTo(Education::class);
    }
}
