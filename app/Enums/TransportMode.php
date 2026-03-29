<?php

namespace App\Enums;

use Illuminate\Validation\Rule;

enum TransportMode: string
{
    case GRAND_TAXI = 'grand_taxi';
    case PETIT_TAXI = 'petit_taxi';
    case TRAIN      = 'train';

    public function label(): string
    {
        return match ($this) {
            self::GRAND_TAXI => 'Grand Taxi',
            self::PETIT_TAXI => 'Petit Taxi',
            self::TRAIN      => 'Train',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn(self $c) => $c->value, self::cases());
    }

    /** @return array<string,string> value => label */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $c) {
            $out[$c->value] = $c->label();
        }
        return $out;
    }

    public static function rule(): \Illuminate\Contracts\Validation\Rule
    {
        return Rule::in(self::values());
    }
}
