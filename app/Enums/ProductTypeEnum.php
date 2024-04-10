<?php

namespace App\Enums;

class ProductTypeEnum
{
    const ONLINE = 1;
    const OFFLINE = 2;

    /**
     * Get the human-readable string for the type.
     *
     * @param ?int $value
     * @return string
     */
    public static function getTypeString(?int $value = null): string
    {
        return match ($value) {
            self::ONLINE => 'Online',
            self::OFFLINE => 'Offline',
            default => 'Unknown',
        };
    }

    /**
     * Get the array of types.
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::ONLINE => 'Online',
            self::OFFLINE => 'Offline',
        ];
    }
}
