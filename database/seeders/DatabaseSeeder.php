<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $email = 'test@example.com';
        $password = \Illuminate\Support\Facades\Hash::make('password123');

        $user = \App\Models\User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => $password,
            'is_employee' => 1,
            'otp_verify' => '1',
            'role_id' => 1
        ]);

        /*
        \App\Models\Employee::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'role_id' => 1,
            'employee_id' => 'EMP001'
        ]);
        */

        \App\Models\UserPermissions::create([
            'role_id' => 1,
            'modules' => json_encode(['all'])
        ]);

        $mobileUser = \App\Models\MobileAppUser::create([
            'name' => 'Mobile Test User',
            'email' => 'mobile@example.com',
            'password' => $password,
            'login_type' => 'User',
            'otp_verify' => 1
        ]);

        $adminUser = \App\Models\MobileAppUser::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => $password,
            'login_type' => 'Admin',
            'otp_verify' => 1
        ]);

        // Seed Masters
        $field1 = \App\Models\Master::create(['type' => 'field', 'name' => 'Ayurveda', 'status' => 1]);
        $field2 = \App\Models\Master::create(['type' => 'field', 'name' => 'Yoga', 'status' => 1]);
        
        $loc1 = \App\Models\Master::create(['type' => 'location', 'name' => 'Mumbai', 'status' => 1]);
        $loc2 = \App\Models\Master::create(['type' => 'location', 'name' => 'Delhi', 'status' => 1]);

        $type1 = \App\Models\Master::create(['type' => 'post', 'name' => 'Article', 'status' => 'Active']);
        $type2 = \App\Models\Master::create(['type' => 'post', 'name' => 'Question', 'status' => 'Active']);

        // Seed Posts
        for ($i = 1; $i <= 5; $i++) {
            \App\Models\Post::create([
                'user_id' => $mobileUser->id,
                'user_name' => $mobileUser->name,
                'title' => "Healthcare Post $i",
                'description' => "This is a detailed description for healthcare post number $i. It contains useful information about health and wellness.",
                'field_id' => $field1->id,
                'field_name' => $field1->name,
                'post_type' => $type1->name,
                'post_type_id' => $type1->id,
                'location' => $loc1->name,
                'status' => 'active',
                'date' => now()->format('Y-m-d')
            ]);
        }

        // Seed Notifications
        \App\Models\Notification::create([
            'user_id' => $mobileUser->id,
            'title' => 'Welcome!',
            'body' => 'Welcome to SR Health Community.',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        \App\Models\Notification::create([
            'user_id' => $mobileUser->id,
            'title' => 'New Post',
            'body' => 'A new post was added in Ayurveda.',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
