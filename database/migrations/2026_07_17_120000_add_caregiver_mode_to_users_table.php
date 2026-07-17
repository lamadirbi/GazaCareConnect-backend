<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('caregiver_mode_enabled')->default(false)->after('phone');
            $table->string('caregiver_relationship', 32)->nullable()->after('caregiver_mode_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['caregiver_mode_enabled', 'caregiver_relationship']);
        });
    }
};
