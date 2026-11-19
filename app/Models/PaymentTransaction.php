<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = ['bill_id', 'client_id', 'amount', 'received_at', 'method', 'reference_number'];
    protected $casts = ['amount' => 'decimal:2', 'received_at' => 'date'];

    public function bill(): BelongsTo { return $this->belongsTo(Bill::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
