<?php

namespace Database\Seeders;

use Modules\Auth\Models\User;
use Modules\Auth\Models\UserLevelPermission;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Hossein',
            'username' => 'admin',
            'user_level' => 1,
            'password' => bcrypt('password'),
        ]);

        UserLevelPermission::create([
            'name' => 'admin',
            'permission_do' => ["*"],
        ]);
    }
}
