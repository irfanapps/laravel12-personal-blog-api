<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Postgre
        // DB::transaction(function () {
        //     DB::statement('SET CONSTRAINTS ALL DEFERRED');
        //     $this->call([
        //         UserSeeder::class,
        //         TagSeeder::class,
        //         CategorySeeder::class,
        //         PostSeeder::class,
        //         CommentSeeder::class,
        //     ]);
        // });

        // Mysql
        $this->call([
            UserSeeder::class,
            TagSeeder::class,
            CategorySeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
