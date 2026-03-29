<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Validation\Rule;

enum ModuleEvaluationType: string
{
    case HOT        = 'hot';
    case COLD       = 'cold';
    case COMPETENCY = 'competency';

    public function label(): string
    {
        return match ($this) {
            self::HOT        => 'À chaud',
            self::COLD       => 'À froid',
            self::COMPETENCY => 'Évaluation de compétence',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn(self $c) => $c->value, self::cases());
    }

    /** @return array<string,string> */
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
