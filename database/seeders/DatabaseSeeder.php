<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'admin',
            'email' => 'hieuvm.85@gmail.com',
            'password' => '123456',
            'phone' =>'037656185',
            'role' =>'admin',    
        ]);
        \App\Models\User::factory()->create([
            'name' => 'hieu 1',
            'email' => 'unique08052002@gmail.com',
            'password' => '123456',
            'phone' =>'037656186', 
        ]);
    }
}
