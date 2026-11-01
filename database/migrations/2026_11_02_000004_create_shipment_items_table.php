<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('item_code');
            $table->string('product_name');
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->integer('quantity_vials');
            $table->integer('doses_per_vial')->default(10);
            $table->string('container_type')->default('Envirotainer RKN e1 (Active Dry Ice)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
