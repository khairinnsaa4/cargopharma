<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->enum('event_type', [
                'CREATED',
                'BOOKED',
                'PICKED_UP',
                'IN_TRANSIT',
                'TRANSIT_HUB',
                'CUSTOMS_INSPECTION',
                'DELIVERED',
                'EXCEPTION_OCCURRED',
            ]);

            $table->string('status_snapshot');
            $table->dateTime('event_time')->index();
            $table->decimal('recorded_lat', 10, 7)->nullable();
            $table->decimal('recorded_lng', 10, 7)->nullable();

            // Cold chain telemetry fields
            $table->decimal('ambient_temperature', 5, 2)->nullable(); // Recorded from Weather API
            $table->decimal('container_temperature', 5, 2)->nullable(); // Sensor reading in container

            $table->text('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};
