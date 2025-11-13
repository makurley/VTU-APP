<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletAddress extends Model
{
    use HasFactory;
    protected $fillable = ['crypto_type', 'wallet_address'];
    public $timestamps = false;
}
