<?php

namespace Database\Seeders;

use App\Models\Cycle;
use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PrimaryCycleLevelSeeder  extends Seeder
{
    public function run(): void
    {
        /**
         * Get a random user
         */
        $randomUserId = User::inRandomOrder()->value('id');
        /**
         * 1. Find or create the "Primaire" cycle
         *    (including soft-deleted ones)
         */
        $cycle = Cycle::withTrashed()->firstOrCreate(
            ['title' => 'Primaire'],
            [
                'cycle_id'   => Str::uuid(),
                'code'       => 'PrS',
                'order'      => 1,
                'created_by' => $randomUserId,
            ]
        );

        if ($cycle->trashed()) {
            $cycle->restore();
        }

        /**
         * 2. Levels to seed
         */
        $levels = [
            [
                'title'   => '3ʳᵉ année primaire',
                'code'    => '3AP',
                'order'   => 3,
                'min_age' => 8,
                'max_age' => 9,
            ],
            [
                'title'   => '4ʳᵉ année primaire',
                'code'    => '4AP',
                'order'   => 4,
                'min_age' => 9,
                'max_age' => 10,
            ],
            [
                'title'   => '5ʳᵉ année primaire',
                'code'    => '5AP',
                'order'   => 5,
                'min_age' => 10,
                'max_age' => 11,
            ],
            [
                'title'   => '6ʳᵉ année primaire',
                'code'    => '6AP',
                'order'   => 6,
                'min_age' => 11,
                'max_age' => 12,
            ],
        ];

        /**
         * 3. Create levels if they don’t exist
         */
        foreach ($levels as $level) {
            Level::withTrashed()->firstOrCreate(
                [
                    'code'     => $level['code'],
                    'cycle_id' => $cycle->id,
                ],
                [
                    'level_id'   => Str::uuid(),
                    'title'      => $level['title'],
                    'order'      => $level['order'],
                    'min_age'    => $level['min_age'],
                    'max_age'    => $level['max_age'],
                    'created_by' => $randomUserId,
                ]
            );
        }
    }
}
