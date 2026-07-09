<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->string('assignment_mode', 32)->default('queue')->after('physician_id');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn('assignment_mode');
        });
    }
};
