<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Validation\Rule;

enum ModuleEvaluationStatus: string
{
    case PLANNED   = 'planned';
    case DONE      = 'done';
    case VALIDATED = 'validated';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PLANNED   => 'Planifiée',
            self::DONE      => 'Réalisée',
            self::VALIDATED => 'Validée',
            self::CANCELLED => 'Annulée',
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

    public function isFinal(): bool
    {
        return in_array($this, [self::VALIDATED, self::CANCELLED], true);
    }
}
