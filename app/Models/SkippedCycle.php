<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkippedCycle extends Model
{
    protected $fillable = ['client_id', 'cycle_number'];
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
