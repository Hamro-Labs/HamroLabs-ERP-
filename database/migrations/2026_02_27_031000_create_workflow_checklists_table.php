<?php

/**
 * Migration: Create workflow checklists table for Institute Admin Dashboard
 * 
 * This table stores the daily workflow checklist completion status
 * so that checked items remain saved across sessions.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('task_key', 100); // Unique identifier for the task
            $table->string('task_name', 255);
            $table->text('task_description')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->date('checklist_date'); // The date this checklist item belongs to
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['tenant_id', 'user_id', 'checklist_date'], 'idx_workflow_lookup');
            $table->index(['tenant_id', 'checklist_date', 'is_completed'], 'idx_workflow_pending');
            $table->index('checklist_date', 'idx_workflow_date');
            $table->unique(['tenant_id', 'user_id', 'task_key', 'checklist_date'], 'unique_workflow_item');
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_checklists');
    }
};
