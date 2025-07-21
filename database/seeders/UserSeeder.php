<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Nonactive foreign key check temporary ( if use postgre )
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate users table first (opsional)
        DB::table('users')->truncate();

        // Start transaction
        DB::beginTransaction();

        try {
            // 1. Insert Admin User (manual)
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@blog.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'bio' => 'Administrator of the blog',
                'avatar' => 'avatars/admin.jpg'
            ]);

            //2. Insert Chunk Data (100 user )
            $users = [];
            for ($i = 1; $i <= 5; $i++) {
                $users[] = [
                    'name' => 'User ' . $i,
                    'email' => 'user' . $i . '@blog.com',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            //Insert into batch (100 data per chunk)
            $chunks = array_chunk($users, 100);
            foreach ($chunks as $chunk) {
                DB::table('users')->insert($chunk);
            }

            // 3. Generate Fake Users with Factory (10 user)
            User::factory(10)->create();

            // Commit transaction if all success
            DB::commit();
        } catch (\Exception $e) {
            // Rollback transaction if get error
            DB::rollBack();
            throw $e; // Re-throw exception with logging
        }

        // Reactivate foreign key check ( if use postgre )
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
