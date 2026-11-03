<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique()->index();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained('carriers')->nullOnDelete();
            $table->foreignId('origin_location_id')->constrained('locations');
            $table->foreignId('destination_location_id')->constrained('locations');

            $table->string('cargo_type')->default('Biological Vaccine - Cold Chain');
            $table->decimal('target_temp_min', 5, 2)->default(-25.00);
            $table->decimal('target_temp_max', 5, 2)->default(-15.00);
            $table->decimal('total_weight_kg', 10, 2);
            $table->decimal('total_volume_cbm', 10, 2)->nullable();

            $table->dateTime('expected_departure');
            $table->dateTime('expected_arrival')->index();
            $table->dateTime('actual_departure')->nullable();
            $table->dateTime('actual_arrival')->nullable();

            $table->enum('current_status', [
                'CREATED',
                'BOOKED',
                'PICKED_UP',
                'IN_TRANSIT',
                'DELIVERED',
                'CANCELLED',
            ])->default('CREATED')->index();

            $table->boolean('is_delayed')->default(false)->index();
            $table->integer('delay_duration_hours')->default(0);
            $table->enum('delay_severity', ['NONE', 'LOW', 'MEDIUM', 'HIGH'])->default('NONE');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
