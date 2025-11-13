<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount','user_id','type','code','new_balance',
        'old_balance','service','charge','message','status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'meta' => 'object',
    ];
}
