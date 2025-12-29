<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->string('identifier')->nullable(); // e.g., TASK-123
            $table->longText('description')->nullable();

            // Priority: 1 = urgent, 2 = high, 3 = normal, 4 = low
            $table->tinyInteger('priority')->default(3);

            // Dates
            $table->timestamp('start_date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Time tracking (in minutes)
            $table->integer('time_estimate')->nullable();
            $table->integer('time_spent')->default(0);

            // Ordering
            $table->integer('position')->default(0);

            // Archived/Closed
            $table->boolean('is_archived')->default(false);

            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['task_list_id', 'status_id']);
            $table->index(['task_list_id', 'position']);
            $table->index('due_date');
            $table->index('parent_id');
        });

        // Task assignees (many-to-many)
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('assignee'); // assignee, watcher
            $table->timestamps();

            $table->unique(['task_id', 'user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_user');
        Schema::dropIfExists('tasks');
    }
};
