<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Ambil notifikasi terbaru untuk dropdown (AJAX).
     */
    public function dropdown(Request $request)
    {
        $notifications = $request->user()
            ->notifications() // semua notifikasi user
            ->latest()
            ->take(5)         // ambil 5 terakhir
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Semua notifikasi ditandai sudah dibaca.'
        ]);
    }
}
