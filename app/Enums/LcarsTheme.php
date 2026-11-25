<?php

namespace App\Enums;

enum LcarsTheme: string
{
    case Classic = 'classic';
    case LowerDecks = 'lower-decks';
    case LowerDecksPadd = 'lower-decks-padd';
    case NemesisBlue = 'nemesis-blue';
    case Picard = 'picard';
    case Voyager = 'voyager';

    /**
     * Get the name of the template file in public/LCARS.
     */
    public function fileName(): string
    {
        return $this->value.'.html';
    }

    /**
     * Get the name displayed to the user.
     */
    public function label(): string
    {
        return match ($this) {
            self::Classic => 'Classic',
            self::LowerDecks => 'Lower Decks',
            self::LowerDecksPadd => 'Lower Decks PADD',
            self::NemesisBlue => 'Nemesis Blue',
            self::Picard => 'Picard',
            self::Voyager => 'Voyager',
        };
    }

    /**
     * Get all themes as options for a select field.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $theme): array => ['value' => $theme->value, 'label' => $theme->label()],
            self::cases(),
        );
    }
}
