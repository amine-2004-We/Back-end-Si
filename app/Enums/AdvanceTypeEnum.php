<?php

namespace App\Enums;

enum AdvanceTypeEnum: string
{
    case OrdreMission = 'Ordre de mission';
    case Initial = 'Initial';
    case NoteDepense = 'Note de dépense';
    case Reliquat = 'Reliquat';

    /**
     * Returns all enum cases as an array of options.
     *
     * @return array<int, array<string, string>> Array of options
     */
    public static function options(): array
    {
        return array_map(fn(self $case) => [
            'value' => $case->value,
        ], self::cases());
    }

    /**
     * Returns all enum values as a simple array.
     *
     * @return string[] Array of values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
