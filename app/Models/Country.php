<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
        'currency',
        'prefix',
    ];
    public function operators()
    {
        return $this->hasMany(Operator::class, 'country_id');
    }
}
