<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    use HasFactory;
    protected $fillable = [
        'ticket_id', 'comment','type',
    ];
    public function userComment()
    {
        return $this->where('type', 0);
    }

    public function adminComment()
    {
        return $this->where('type', 1);
    }
}
