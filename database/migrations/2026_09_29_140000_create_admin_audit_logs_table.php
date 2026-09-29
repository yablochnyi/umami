<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            // Keep history when an account or the affected record is deleted.
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_email');
            $table->string('actor_name');
            $table->string('action', 32);
            $table->string('resource', 64);
            $table->string('subject_id', 64)->nullable();
            $table->string('subject_label')->nullable();
            $table->string('page', 32)->nullable();
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['actor_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['resource', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};
