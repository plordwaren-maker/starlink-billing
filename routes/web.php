<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Bill;
use App\Models\BillingNotification;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\BillingNotificationController;
use App\Http\Controllers\ProfileController;

// Public authentication routes.
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Every billing page and data-changing action requires an authenticated user.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Keep the shared notification bell and recurring billing data in sync
    // on every page that uses the main layout.
    View::composer('layouts.app', function ($view) {
        Bill::generateDueBills();
        Bill::with('client')->whereHas('client', fn ($query) => $query->where('active', true))
            ->whereDate('due_at', '<=', now()->toDateString())
            ->whereRaw('amount > paid_amount')->get()->each(function ($bill) {
                $message = 'Payment due: '.$bill->client->name.' has an unpaid Starlink bill of ₱'.number_format($bill->balance, 2).' (due '.$bill->due_at->format('M d, Y g:i A').').';
                BillingNotification::firstOrCreate(['message' => $message], ['is_read' => false]);
            });

        $view->with('notifications', BillingNotification::latest()->take(12)->get());
        $view->with('unreadNotificationCount', BillingNotification::where('is_read', false)->count());
    });

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::patch('clients/{client}/archive', [ClientController::class, 'archive'])->name('clients.archive');

    Route::resource('bills', BillController::class)->except(['show']);
    Route::resource('payments', PaymentController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/compose', [MessageController::class, 'compose'])->name('messages.compose');

    Route::get('/setup', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/setup', [SettingController::class, 'update'])->name('settings.update');

    Route::post('/notifications/{notification}/read', [BillingNotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [BillingNotificationController::class, 'readAll'])->name('notifications.read-all');
});
