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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone');
            $table->string('property_type'); // 'residence' | 'suwung'
            $table->string('rental_type'); // 'weekly' | 'monthly'
            $table->integer('duration')->default(1); // e.g. 1 (week/month), 2, 3
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_price', 14, 2);
            $table->string('status')->default('confirmed'); // 'pending', 'confirmed', 'active', 'completed', 'cancelled'
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
