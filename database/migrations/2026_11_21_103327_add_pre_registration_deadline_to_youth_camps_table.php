<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('youth_camps', function (Blueprint $table) {
            $table->date('pre_registration_deadline')->nullable()->after('on_site_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('youth_camps', function (Blueprint $table) {
            $table->dropColumn('pre_registration_deadline');
        });
    }
};
