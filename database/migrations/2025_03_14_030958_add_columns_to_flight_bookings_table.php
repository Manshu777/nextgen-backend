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
        Schema::table('bookflights', function (Blueprint $table) {
            $table->string('token')->nullable();
            $table->string('trace_id')->nullable();
            $table->string('user_ip')->nullable();
            $table->string('pnr')->nullable();
            $table->string('booking_id')->nullable();
            $table->string('username')->nullable();
            $table->string('phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flight_bookings', function (Blueprint $table) {
            //
        });
    }
};
