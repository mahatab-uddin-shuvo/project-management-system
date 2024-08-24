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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->enum('status', ['pending', 'in-progress', 'completed'])->default('pending');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('assigned_to');
            $table->unsignedBigInteger('assigned_by');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->timestamps();
            $table->foreign("project_id")->references("id")->on("projects")->onDelete("cascade");
            $table->foreign("assigned_to")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("assigned_by")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("parent_id")->references("id")->on("tasks")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
