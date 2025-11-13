<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'crypto_type', 'crypto_amount', 'fiat_amount', 'wallet_address', 'buyer_wallet_address', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
