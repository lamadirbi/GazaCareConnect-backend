<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\PhysicianProfile;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('physician_profiles', function (Blueprint $table) {
            $table->string('verification_status', 32)->default('pending')->after('certificate_file_ids');
            $table->timestamp('verified_at')->nullable()->after('verification_status');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('verified_by');
        });

        DB::table('physician_profiles')->update([
            'verification_status' => PhysicianProfile::STATUS_APPROVED,
            'verified_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('physician_profiles', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verification_status', 'verified_at', 'verified_by', 'rejection_reason']);
        });
    }
};
