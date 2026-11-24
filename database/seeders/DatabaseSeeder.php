<?php

namespace Database\Seeders;

use App\Models\Handout;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->has(Handout::factory(3))->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
