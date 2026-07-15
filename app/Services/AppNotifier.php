<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AppDatabaseNotification;
use Illuminate\Support\Collection;

class AppNotifier
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function notify(User $user, string $title, string $body, string $href, string $kind, array $meta = []): void
    {
        if ($user->is_disabled) {
            return;
        }

        $user->notify(new AppDatabaseNotification([
            'title' => $title,
            'body' => $body,
            'href' => $href,
            'kind' => $kind,
            'meta' => $meta,
        ]));
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function notifyAdmins(string $title, string $body, string $href, string $kind, array $meta = []): void
    {
        self::admins()->each(fn (User $admin) => self::notify($admin, $title, $body, $href, $kind, $meta));
    }

    /** @return Collection<int, User> */
    public static function admins(): Collection
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('is_disabled', false)
            ->get();
    }
}
