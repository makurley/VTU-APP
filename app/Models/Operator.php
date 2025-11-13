<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'country_id', 'fee', 'discount', 'status'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function products()
    {
        return $this->hasMany(BillProduct::class, 'operator_id');
    }
    public function airtimeproducts()
    {
        return $this->hasMany(BillProduct::class, 'operator_id')->where('type', 'airtime');
    }
    public function dataProducts()
    {
        return $this->hasMany(BillProduct::class, 'operator_id')->where('type', 'data');
    }

}
