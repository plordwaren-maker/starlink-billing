<?php
namespace App\Http\Controllers;
use App\Models\BillingNotification;
use Illuminate\Http\RedirectResponse;
class BillingNotificationController extends Controller {
 public function read(BillingNotification $notification): RedirectResponse { $notification->update(['is_read'=>true]); return back()->with('success','Notification marked as read.'); }
 public function readAll(): RedirectResponse { BillingNotification::where('is_read',false)->update(['is_read'=>true]); return back()->with('success','All notifications marked as read.'); }
}
