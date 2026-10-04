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
            $table->string('venue')->nullable()->after('theme');
            $table->string('verse_reference')->nullable()->after('venue');
            $table->string('tagline')->nullable()->after('verse_reference');
            $table->decimal('pre_registration_fee', 8, 2)->nullable()->after('tagline');
            $table->decimal('on_site_fee', 8, 2)->nullable()->after('pre_registration_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('youth_camps', function (Blueprint $table) {
            $table->dropColumn(['venue', 'verse_reference', 'tagline', 'pre_registration_fee', 'on_site_fee']);
        });
    }
};
