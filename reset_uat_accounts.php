<?php

use App\Models\PhysicianProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function resetPending(string $email): void
{
    $u = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => 'د. معلّق اختبار',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PHYSICIAN,
            'email_verified_at' => now(),
            'is_disabled' => false,
        ]
    );

    PhysicianProfile::updateOrCreate(
        ['user_id' => $u->id],
        [
            'specialty' => 'طب الأسرة',
            'certificate' => 'شهادة اختبار UAT معلّق',
            'verification_status' => PhysicianProfile::STATUS_PENDING,
            'rejection_reason' => null,
            'verified_at' => null,
            'verified_by' => null,
        ]
    );

    echo $email.' => pending'.PHP_EOL;
}

resetPending('dr.pending.uat@example.com');

$m = User::where('email', 'dr.mohammad@example.com')->first();
if ($m) {
    $m->is_disabled = false;
    $m->save();
    echo 'dr.mohammad enabled'.PHP_EOL;
}
