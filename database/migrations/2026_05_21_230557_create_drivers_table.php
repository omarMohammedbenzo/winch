<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();            
            $table->string('status', 20)->default('offline');// available | busy | offline 

            // DECIMAL keeps them coordinates portable and exact;
            // a bounding-box pre-filter on these (indexed) narrows candidates before
            // the precise ST_Distance_Sphere() call. See README for the geo trade-off.
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Denormalized pointer to the driver's current active order. Lets the
            // assignment query check "has no active order" in O(1) and lock the
            // driver row. Kept in sync inside the assignment transaction.
            $table->unsignedBigInteger('current_order_id')->nullable();

            $table->timestamps();

            $table->index(['status', 'current_order_id']);
            $table->index('latitude');
            $table->index('longitude');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
