<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CaregiverModeController extends Controller
{
    public const RELATIONSHIPS = [
        'son',
        'daughter',
        'spouse',
        'father',
        'mother',
        'brother',
        'sister',
    ];

    public function update(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== User::ROLE_PATIENT) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'relationship' => [
                'nullable',
                'string',
                Rule::in(self::RELATIONSHIPS),
            ],
        ]);

        if ($data['enabled']) {
            if (empty($data['relationship'])) {
                return response()->json([
                    'message' => 'يرجى اختيار صلة القرابة.',
                    'errors' => ['relationship' => ['يرجى اختيار صلة القرابة.']],
                ], 422);
            }
            $user->caregiver_mode_enabled = true;
            $user->caregiver_relationship = $data['relationship'];
        } else {
            $user->caregiver_mode_enabled = false;
            $user->caregiver_relationship = null;
        }

        $user->save();

        return response()->json([
            'user' => $user,
        ]);
    }
}
