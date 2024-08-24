<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user              =  new User();
        $user->name        = "Admin";
        $user->email       = "admin@admin.com";
        $user->password    = Hash::make('123456');
        $user->save();

        $role               =  new Role();
        $role->name         = "SuperAdmin";
        $role->guard_name   = "api";

        $role->save();

        DB::table('model_has_roles')->insert([
            'role_id' => 1,
            'model_type' => "App\Models\User",
            'model_id' => 1
        ]);
    }
}
