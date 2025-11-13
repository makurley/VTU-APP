<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPin extends Model
{
    use HasFactory;
    public function network()
    {
        return $this->belongsTo(Network::class);
    }
    public function plan()
    {
        return $this->belongsTo(DatacardPlan::class, 'plan_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
