<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;

use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function referrals()
    {
        return $this->hasMany(User::class, 'ref_id')->where('ref_id', Auth::user()->id);
    }

    public function payment()
    {
        return $this->hasMany(Deposit::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function mdeposit()
    {
        return $this->hasMany(Mdeposit::class);
    }

    public function refer()
    {
        return $this->belongsTo(User::class, 'ref_id');
    }
    public function ticket_comments()
    {
        return $this->hasMany(TicketComment::class);
    }
    public function tickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
    public function cryptoTransactions()
{
    return $this->hasMany(CryptoTransaction::class, 'user_id');
}
}
