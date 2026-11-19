<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingNotification extends Model
{
    protected $fillable = ['message', 'is_read'];
    protected $casts = ['is_read' => 'boolean'];
}
