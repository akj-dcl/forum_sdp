<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default channels
        \App\Models\Channel::firstOrCreate(
            ['slug' => 'official-announcements'],
            [
                'name' => 'Official Announcements',
                'description' => 'Official communications and announcements.',
                'color' => '#3525cd',
            ]
        );
        \App\Models\Channel::firstOrCreate(
            ['slug' => 'it-support'],
            [
                'name' => 'IT Support',
                'description' => 'Get help with hardware, software, and systems.',
                'color' => '#39b8fd',
            ]
        );
        \App\Models\Channel::firstOrCreate(
            ['slug' => 'casual-lounge'],
            [
                'name' => 'Casual Lounge',
                'description' => 'Informal chat and off-topic discussions.',
                'color' => '#ba1a1a',
            ]
        );

        // Seed default corporate highlights
        \App\Models\CorporateHighlight::firstOrCreate(
            ['title' => 'Annual Townhall 2024'],
            [
                'description' => 'Starts in 3 days • RSVP Required',
                'link' => '#',
            ]
        );
        \App\Models\CorporateHighlight::firstOrCreate(
            ['title' => 'New ERP System Rollout'],
            [
                'description' => 'Check your migration schedule',
                'link' => '#',
            ]
        );
    }
}
