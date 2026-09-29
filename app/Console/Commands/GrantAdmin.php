<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class GrantAdmin extends Command
{
    protected $signature = 'admin:grant {email : Email of an existing user}';

    protected $description = 'Assign the protected administrator role to an existing account without changing its password';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('User not found. No changes made.');

            return self::FAILURE;
        }
        $user->forceFill(['role_id' => Role::query()->where('is_admin', true)->sole()->id])->save();
        $this->info('Administrator role assigned. Password unchanged.');

        return self::SUCCESS;
    }
}
