<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_admin')->default(false);
            $table->json('permissions')->nullable();
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('role_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('admin_locale', 2)->default('pl');
        });

        $role = DB::table('roles')->insertGetId([
            'name' => 'Administrator', 'is_admin' => true, 'permissions' => '[]',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        // Preserve access for the previously configured administrator only.
        DB::table('users')->where('email', config('app.admin_email'))->update(['role_id' => $role]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('admin_locale');
        });
        Schema::dropIfExists('roles');
    }
};
