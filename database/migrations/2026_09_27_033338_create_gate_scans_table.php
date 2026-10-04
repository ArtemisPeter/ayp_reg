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
        Schema::create('gate_scans', function (Blueprint $table) {
            $table->id();
            $table->string('client_scan_id')->unique();
            $table->foreignId('attendee_id')->nullable()->constrained();
            $table->string('manual_name')->nullable();
            $table->string('direction');
            $table->foreignId('scanned_by')->constrained('users');
            $table->timestamp('scanned_at');
            $table->boolean('is_mismatch')->default(false);
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_scans');
    }
};
