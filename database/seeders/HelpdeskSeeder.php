<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Status;
use Illuminate\Database\Seeder;

class HelpdeskSeeder extends Seeder
{
    public function run(): void
    {
        Status::upsert([
            ['name' => 'Open', 'color' => '#6b7280', 'is_resolved' => false],
            ['name' => 'In Progress', 'color' => '#3b82f6', 'is_resolved' => false],
            ['name' => 'Resolved', 'color' => '#22c55e', 'is_resolved' => true],
            ['name' => 'Closed', 'color' => '#ef4444', 'is_resolved' => true],
        ], uniqueBy: ['name'], update: ['color', 'is_resolved']);

        Category::insert([
            ['name' => 'Bug Report'],
            ['name' => 'Feature Request'],
            ['name' => 'General Inquiry'],
            ['name' => 'Support'],
        ]);
    }
}
