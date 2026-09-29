<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = config('app.admin_email', 'artem.yablochnyi@gmail.com');
        $adminPassword = env('ADMIN_PASSWORD', 'password');

        $user = User::updateOrCreate([
            'email' => $adminEmail,
        ], [
            'name' => 'Umami Admin',
            'password' => Hash::make($adminPassword),
        ]);

        $user->forceFill(['role_id' => Role::query()->where('is_admin', true)->sole()->id])->save();

        $this->call(UmamiContentSeeder::class);
    }
}
