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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('property_type'); // 'residence' | 'suwung'
            $table->string('property_name'); // 'Kubu Mawar Residence' | 'Kubu Mawar Suwung'
            $table->string('property_category'); // 'homestay' | 'kost'
            $table->string('room_number'); // e.g. 'R-101', 'SW-01'
            $table->string('name'); // e.g. 'Deluxe Garden Suite', 'Kamar Standard AC'
            $table->string('type_label')->nullable(); // e.g. 'Boutique Suite', 'Kost Eksklusif'
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price_weekly', 12, 2)->nullable(); // residence only
            $table->decimal('price_monthly', 12, 2); // both residence and suwung
            $table->decimal('price_daily', 12, 2)->nullable();
            $table->string('allowed_rental_types')->default('weekly,monthly'); // 'weekly,monthly' for residence, 'monthly' for suwung
            $table->json('features')->nullable();
            $table->integer('max_guests')->default(2);
            $table->integer('size_sqm')->nullable();
            $table->string('floor')->nullable();
            $table->string('status')->default('available'); // 'available' (kosong) | 'occupied' (terisi) | 'maintenance'
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
