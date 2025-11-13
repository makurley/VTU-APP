<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SudoCardTransaction extends Model
{
    // Explicitly specify the table name if it's different from the plural of the model name
    protected $table = 'sudo_card_transaction';  // This matches the table name

    // Define the fillable fields to allow mass assignment
    protected $fillable = [
        'sudo_card_id', 
        'transaction_id', 
        'type', 
        'amount', 
        'description', 
        'transaction_date'
    ];

    public function card()
    {
        return $this->belongsTo(SudoCard::class, 'sudo_card_id');
    }
}
