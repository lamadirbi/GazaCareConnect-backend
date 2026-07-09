<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhysicianProfile;
use App\Models\User;
use Illuminate\Http\Request;

class VerifiedPhysicianController extends Controller
{
    public function index(Request $request)
    {
        $specialty = $request->query('specialty');

        $query = PhysicianProfile::query()
            ->where('verification_status', PhysicianProfile::STATUS_APPROVED)
            ->whereHas('user', fn ($q) => $q->where('is_disabled', false))
            ->with(['user:id,name,email,role'])
            ->orderBy('specialty');

        if (is_string($specialty) && $specialty !== '') {
            $query->where('specialty', 'like', '%'.$specialty.'%');
        }

        $items = $query->paginate(30);

        return response()->json($items);
    }

    public function show(User $physician)
    {
        if ($physician->role !== User::ROLE_PHYSICIAN || $physician->is_disabled) {
            return response()->json(['message' => 'الطبيب غير متاح.'], 404);
        }

        $profile = $physician->physicianProfile;
        if (! $profile || $profile->verification_status !== PhysicianProfile::STATUS_APPROVED) {
            return response()->json(['message' => 'الطبيب غير موثّق.'], 404);
        }

        return response()->json([
            'physician' => [
                'id' => $physician->id,
                'name' => $physician->name,
                'specialty' => $profile->specialty,
                'certificate' => $profile->certificate,
            ],
        ]);
    }
}
