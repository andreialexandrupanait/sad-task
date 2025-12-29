<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_list_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#6b7280');
            $table->string('type')->default('custom'); // open, in_progress, done, closed, custom
            $table->integer('position')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_closed')->default(false); // Tasks in closed status are considered done
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
