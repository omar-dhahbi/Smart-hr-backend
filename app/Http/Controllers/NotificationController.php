<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function getNotification($user_id)
    {
        $notifications = Notification::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (! $notification) {
            return response()->json([
                'status' => false,
                'message' => 'Notification non trouvée',
            ], 404);
        }
        $notification->read = true;
        $notification->save();

        return response()->json([
            'status' => true,
            'message' => 'Notification marquée comme lue',
        ]);
    }
}
