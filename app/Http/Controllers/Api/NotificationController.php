<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = $user->notifications()->latest();
        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $limit = min(50, max(1, (int) $request->query('limit', 20)));
        $rows = $query->limit($limit)->get();

        return response()->json([
            'data' => $rows->map(fn (DatabaseNotification $n) => $this->format($n)),
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $row = $user->notifications()->whereKey($notification)->first();
        if (! $row) {
            return response()->json(['message' => 'الإشعار غير موجود.'], 404);
        }

        if ($row->read_at === null) {
            $row->markAsRead();
        }

        return response()->json([
            'notification' => $this->format($row->fresh()),
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'تم تعليم جميع الإشعارات كمقروءة.',
            'unread_count' => 0,
        ]);
    }

    /** @return array<string, mixed> */
    protected function format(DatabaseNotification $notification): array
    {
        /** @var array<string, mixed> $data */
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'title' => (string) ($data['title'] ?? ''),
            'body' => (string) ($data['body'] ?? ''),
            'href' => (string) ($data['href'] ?? '/'),
            'kind' => (string) ($data['kind'] ?? 'general'),
            'meta' => is_array($data['meta'] ?? null) ? $data['meta'] : [],
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }
}
