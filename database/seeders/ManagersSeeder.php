<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ManagersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managers = ['Anna', "Lika", 'Toma'];

        foreach ($managers as $manager) {
            \App\Models\Stepper\Manager::create([
                'name' => $manager
            ]);
        }
    }
}
