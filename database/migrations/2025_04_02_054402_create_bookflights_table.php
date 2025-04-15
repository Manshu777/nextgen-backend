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
        Schema::create('bookflights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_number')->nullable();
            $table->string('flight_name')->nullable();
            $table->string('flight_number')->nullable();
            $table->string('departure_from')->nullable();
            $table->string('arrival_to')->nullable();
            $table->datetime('flight_date')->nullable();
            $table->datetime('date_of_booking')->nullable();
            $table->datetime('return_date')->nullable();
            $table->text('initial_response')->nullable();
            $table->boolean('refund')->default(0);
            $table->text('response')->nullable();
            $table->string('token')->nullable();
            $table->string('trace_id')->nullable();
            $table->string('user_ip')->nullable();
            $table->string('pnr')->nullable();
            $table->string('booking_id')->nullable();
            $table->string('username')->nullable();
            $table->string('phone_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookflights', function (Blueprint $table) {
            //
        });
    }
};
