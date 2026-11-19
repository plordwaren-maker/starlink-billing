<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Client extends Model
{
    protected $fillable = ['name', 'phone', 'notes', 'share', 'registered_at', 'active'];

    protected $casts = [
        'share' => 'decimal:2',
        'registered_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function bills(): HasMany { return $this->hasMany(Bill::class); }
    public function transactions(): HasMany { return $this->hasMany(PaymentTransaction::class); }
    public function skippedCycles(): HasMany { return $this->hasMany(SkippedCycle::class); }

    /** Return the bill that should represent this subscription in follow-up views. */
    public function currentFollowUpBill(): ?Bill
    {
        $bills = $this->relationLoaded('bills') ? $this->bills : $this->bills()->get();
        $today = now()->toDateString();
        $unpaidDue = $bills
            ->filter(fn ($bill) => $bill->balance > 0.005 && $bill->due_at && $bill->due_at->toDateString() <= $today)
            ->sortBy('due_at')
            ->first();

        // If an unpaid due/overdue cycle exists, do not hide it behind a newly
        // generated next-month cycle. Otherwise show the latest subscription cycle.
        return $unpaidDue ?? $bills->sortByDesc('due_at')->first();
    }

    public function cycleStart(int $n): Carbon
    {
        return $this->registered_at->copy()->addMonthsNoOverflow($n);
    }

    public function cycleDue(int $n): Carbon
    {
        return $this->registered_at->copy()->addMonthsNoOverflow($n + 1);
    }
}
