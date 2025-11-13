<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id', 'name', 'code', 'price', 'api', 'reseller', 'min', 'max', 'type', 'desc', 'currency', 'status'
    ];

    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

}
