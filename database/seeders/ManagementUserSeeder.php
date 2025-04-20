<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class ManagementUserSeeder extends Seeder
{
    public function run()
    {
        $managementRole = Role::where('name', 'management')->first();
        
        $user = User::create([
            'name' => 'Management User',
            'email' => 'management@example.com',
            'password' => Hash::make('password123')
        ]);
        
        $user->roles()->attach($managementRole);
    }
}
