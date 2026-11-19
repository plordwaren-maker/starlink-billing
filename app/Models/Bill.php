<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Bill extends Model
{
    protected $fillable = [
        'client_id', 'cycle_number', 'period_start', 'due_at',
        'amount', 'paid_amount', 'is_manual',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'due_at' => 'datetime',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_manual' => 'boolean',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function transactions(): HasMany { return $this->hasMany(PaymentTransaction::class); }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    /** True for any bill whose calendar due date is today, regardless of due time. */
    public function isDueToday(): bool
    {
        return $this->due_at !== null && $this->due_at->toDateString() === now()->toDateString();
    }

    /** True only after the calendar due date has passed and a balance remains. */
    public function isOverdue(): bool
    {
        return $this->balance > 0.005
            && $this->due_at !== null
            && $this->due_at->toDateString() < now()->toDateString();
    }

    public function getStatusAttribute(): string
    {
        if ($this->balance <= 0.005) return 'paid';
        if ($this->due_at && $this->due_at->toDateString() < now()->toDateString()) return 'overdue';
        if ($this->paid_amount > 0) return 'partial';
        if ($this->period_start->isFuture()) return 'upcoming';
        return 'unpaid';
    }

    public static function generateDueBills(): void
    {
        Client::where('active', true)->whereNotNull('registered_at')->with('skippedCycles')->each(function (Client $client) {
            for ($n = 0; $n < 400; $n++) {
                $start = $client->cycleStart($n);
                if ($start->isFuture()) break;
                if ($client->skippedCycles->contains('cycle_number', $n)) continue;

                $bill = static::firstOrNew([
                    'client_id' => $client->id,
                    'cycle_number' => $n,
                ]);

                if (!$bill->exists) {
                    $bill->fill([
                        'period_start' => $start,
                        'due_at' => $client->cycleDue($n),
                        'amount' => $client->share,
                        'paid_amount' => 0,
                        'is_manual' => false,
                    ])->save();
                } elseif (!$bill->is_manual) {
                    // Correct dates on previously generated fixed-day cycles while
                    // preserving the bill's saved amount and payment history.
                    $bill->update([
                        'period_start' => $start,
                        'due_at' => $client->cycleDue($n),
                    ]);
                }
            }
        });
    }
}
