<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhysicianProfile;
use App\Models\User;
use App\Services\AppNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function users(Request $request)
    {
        $role = $request->query('role');
        $status = $request->query('status');

        $query = User::query()
            ->with(['physicianProfile' => function ($q) {
                $q->select('id', 'user_id', 'specialty', 'verification_status', 'verified_at', 'verified_by')
                    ->with('verifier:id,name');
            }])
            ->orderByDesc('created_at');

        if (is_string($role) && $role !== '') {
            $query->where('role', $role);
        }

        if ($status === 'disabled') {
            $query->where('is_disabled', true);
        } elseif ($status === 'active') {
            $query->where('is_disabled', false);
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 30)));

        return response()->json($query->paginate($perPage));
    }

    public function setUserDisabled(Request $request, User $user)
    {
        /** @var User $admin */
        $admin = $request->user();

        if ($user->id === $admin->id) {
            return response()->json(['message' => 'لا يمكنك تعطيل حسابك.'], 422);
        }

        if ($user->role === User::ROLE_ADMIN) {
            return response()->json(['message' => 'لا يمكن تعطيل حساب مدير.'], 422);
        }

        $data = $request->validate([
            'disabled' => ['required', 'boolean'],
        ]);

        $user->is_disabled = $data['disabled'];
        $user->disabled_at = $data['disabled'] ? Carbon::now() : null;
        $user->save();

        if ($data['disabled']) {
            $user->tokens()->delete();
            AppNotifier::notify(
                $user,
                'تم تعطيل حسابك',
                'تم تعطيل حسابك من قبل الإدارة. تواصل مع الدعم إذا كان ذلك بالخطأ.',
                '/login',
                'account_disabled',
            );
        }

        return response()->json([
            'user' => $user->load('physicianProfile'),
        ]);
    }

    public function pendingPhysicians()
    {
        $items = PhysicianProfile::query()
            ->where('verification_status', PhysicianProfile::STATUS_PENDING)
            ->with([
                'user:id,name,email,phone,role,is_disabled,created_at',
            ])
            ->orderBy('created_at')
            ->paginate(20);

        foreach ($items as $profile) {
            $profile->hydrateCertificateFilesRelation();
        }

        return response()->json($items);
    }

    public function physicians(Request $request)
    {
        $status = $request->query('status', PhysicianProfile::STATUS_PENDING);

        $query = PhysicianProfile::query()
            ->with(['user:id,name,email,phone,role,is_disabled,created_at'])
            ->orderByDesc('updated_at');

        if (is_string($status) && $status !== 'all') {
            $query->where('verification_status', $status);
        }

        $items = $query->paginate(20);

        foreach ($items as $profile) {
            $profile->hydrateCertificateFilesRelation();
        }

        return response()->json($items);
    }

    public function approvePhysician(Request $request, PhysicianProfile $physicianProfile)
    {
        /** @var User $admin */
        $admin = $request->user();

        if ($physicianProfile->user?->is_disabled) {
            return response()->json(['message' => 'لا يمكن توثيق حساب طبيب معطّل.'], 422);
        }

        $physicianProfile->verification_status = PhysicianProfile::STATUS_APPROVED;
        $physicianProfile->verified_at = Carbon::now();
        $physicianProfile->verified_by = $admin->id;
        $physicianProfile->rejection_reason = null;
        $physicianProfile->save();

        $physicianProfile->load('user:id,name,email,phone,role,is_disabled,created_at');
        $physicianProfile->hydrateCertificateFilesRelation();

        if ($physicianProfile->user) {
            AppNotifier::notify(
                $physicianProfile->user,
                'تم توثيق حسابك',
                'وافقت الإدارة على طلب توثيق حسابك كطبيب. يمكنك الآن استقبال الاستشارات.',
                '/physician/dashboard',
                'physician_approved',
            );
        }

        return response()->json([
            'physician_profile' => $physicianProfile,
        ]);
    }

    public function rejectPhysician(Request $request, PhysicianProfile $physicianProfile)
    {
        /** @var User $admin */
        $admin = $request->user();

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $physicianProfile->verification_status = PhysicianProfile::STATUS_REJECTED;
        $physicianProfile->verified_at = Carbon::now();
        $physicianProfile->verified_by = $admin->id;
        $physicianProfile->rejection_reason = $data['reason'] ?? null;
        $physicianProfile->save();

        $physicianProfile->load('user:id,name,email,phone,role,is_disabled,created_at');
        $physicianProfile->hydrateCertificateFilesRelation();

        if ($physicianProfile->user) {
            AppNotifier::notify(
                $physicianProfile->user,
                'تم رفض طلب التوثيق',
                'رُفض طلب توثيق حسابك. يمكنك مراجعة السبب وإعادة الإرسال.',
                '/physician/dashboard',
                'physician_rejected',
            );
        }

        return response()->json([
            'physician_profile' => $physicianProfile,
        ]);
    }
}
