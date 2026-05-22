<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();           
            $table->string('status', 20)->default('pending'); // pending | assigned | in_progress | completed | cancelled
            $table->decimal('pickup_latitude', 10, 7);
            $table->decimal('pickup_longitude', 10, 7);
            $table->decimal('dropoff_latitude', 10, 7)->nullable();
            $table->decimal('dropoff_longitude', 10, 7)->nullable();
            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['driver_id', 'status']);
            $table->index('pickup_latitude');
            $table->index('pickup_longitude');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
