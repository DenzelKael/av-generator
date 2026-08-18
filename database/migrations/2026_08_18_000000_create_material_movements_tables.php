<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number')->index();
            $table->string('type');
            $table->boolean('has_correlation');
            $table->dateTime('movement_at')->nullable();
            $table->string('responsible')->nullable();
            $table->string('office')->nullable();
            $table->string('source_file');
            $table->string('assignment_reference')->nullable()->index();
            $table->foreignId('assignment_id')->nullable()->constrained('material_movements')->nullOnDelete();
            $table->timestamps();

            $table->unique(['movement_number', 'type']);
        });

        Schema::create('material_movement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_movement_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('line_number')->nullable();
            $table->string('description');
            $table->string('unit', 50)->nullable();
            $table->string('lot', 100)->nullable();
            $table->string('serial_from', 100)->nullable();
            $table->string('serial_to', 100)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->string('status', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_movement_items');
        Schema::dropIfExists('material_movements');
    }
};
