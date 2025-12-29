<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Custom field definitions
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // text, number, date, dropdown, checkbox, url, email, phone, currency, rating, progress, label
            $table->json('options')->nullable(); // For dropdown, label types
            $table->boolean('is_required')->default(false);
            $table->string('default_value')->nullable();
            $table->timestamps();
        });

        // Custom field values for tasks
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['custom_field_id', 'task_id']);
        });

        // Link custom fields to lists
        Schema::create('custom_field_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_list_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['custom_field_id', 'task_list_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_list');
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
