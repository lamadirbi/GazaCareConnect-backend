<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\PhysicianProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PlatformStatsController extends Controller
{
    public function index(): JsonResponse
    {
        $verifiedPhysicians = PhysicianProfile::query()
            ->where('verification_status', PhysicianProfile::STATUS_APPROVED)
            ->whereHas('user', fn ($q) => $q->where('is_disabled', false))
            ->count();

        $completedConsultations = Consultation::query()
            ->where('status', 'completed')
            ->count();

        $registeredPatients = User::query()
            ->where('role', User::ROLE_PATIENT)
            ->where('is_disabled', false)
            ->count();

        return response()->json([
            'stats' => [
                'completed_consultations' => $completedConsultations,
                'verified_physicians' => $verifiedPhysicians,
                'registered_patients' => $registeredPatients,
            ],
        ]);
    }
}
