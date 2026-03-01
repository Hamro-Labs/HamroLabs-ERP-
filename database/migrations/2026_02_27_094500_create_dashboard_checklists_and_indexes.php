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
        // 1. Create dashboard_checklists table (if workflow_checklists doesn't exist or we want a fresh one per spec)
        // Checking realdb.sql, workflow_checklists already exists. 
        // The spec asked for 'dashboard_checklists'. Let's check if we should rename or create new.
        // PRD §4.2 spec says dashboard_checklists.
        if (!Schema::hasTable('dashboard_checklists')) {
            Schema::create('dashboard_checklists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('user_id');
                $table->date('checklist_date');
                $table->string('step_key', 100);
                $table->boolean('is_completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'user_id', 'checklist_date', 'step_key'], 'unique_checklist_step');
                $table->index(['tenant_id', 'checklist_date']);
                
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 2. Add Missing Compound Indexes for Dashboard Performance
        Schema::table('attendance', function (Blueprint $table) {
            if (!Schema::hasIndex('attendance', 'idx_tenant_date')) {
                $table->index(['tenant_id', 'attendance_date'], 'idx_tenant_date');
            }
        });

        Schema::table('fee_records', function (Blueprint $table) {
            if (!Schema::hasIndex('fee_records', 'idx_tenant_paid_date')) {
                $table->index(['tenant_id', 'paid_date'], 'idx_tenant_paid_date');
            }
            if (!Schema::hasIndex('fee_records', 'idx_tenant_due_date')) {
                $table->index(['tenant_id', 'due_date'], 'idx_tenant_due_date');
            }
        });

        Schema::table('inquiries', function (Blueprint $table) {
            if (!Schema::hasIndex('inquiries', 'idx_tenant_created_at')) {
                $table->index(['tenant_id', 'created_at'], 'idx_tenant_created_at');
            }
        });

        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasIndex('exams', 'idx_tenant_start_at')) {
                $table->index(['tenant_id', 'start_at'], 'idx_tenant_start_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_checklists');
        
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropIndex('idx_tenant_date');
        });

        Schema::table('fee_records', function (Blueprint $table) {
            $table->dropIndex('idx_tenant_paid_date');
            $table->dropIndex('idx_tenant_due_date');
        });

        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropIndex('idx_tenant_created_at');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex('idx_tenant_start_at');
        });
    }
};
