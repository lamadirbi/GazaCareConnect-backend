<?php

namespace App\Http\Middleware;

use App\Models\PhysicianProfile;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsurePhysicianVerified
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || $user->role !== User::ROLE_PHYSICIAN) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $profile = $user->physicianProfile;
        if (! $profile || $profile->verification_status !== PhysicianProfile::STATUS_APPROVED) {
            return response()->json([
                'message' => 'حسابك بانتظار موافقة الإدارة قبل الوصول لسجلات المرضى.',
                'verification_status' => $profile?->verification_status ?? PhysicianProfile::STATUS_PENDING,
            ], 403);
        }

        return $next($request);
    }
}
