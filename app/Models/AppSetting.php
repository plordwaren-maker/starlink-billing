<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['receiver_name', 'gcash_number', 'default_share', 'reminder_days', 'increase_reason'];
    protected $casts = ['default_share' => 'decimal:2', 'reminder_days' => 'integer'];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'receiver_name' => 'Account Owner',
            'gcash_number' => '',
            'default_share' => 1150,
            'reminder_days' => 3,
            'increase_reason' => '',
        ]);
    }
}
