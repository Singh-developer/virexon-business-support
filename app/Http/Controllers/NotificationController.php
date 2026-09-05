<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Notifications marked as read.');
    }

    public function read($id, Request $request)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Redirect based on query param or data
        if ($request->has('redirect')) {
            return redirect($request->query('redirect'));
        }

        return back();
    }
}
