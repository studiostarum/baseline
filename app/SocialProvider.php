<?php

namespace App;

enum SocialProvider: string
{
    case Google = 'google';
    case Github = 'github';

    /**
     * Get all supported provider names as array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a provider is supported.
     */
    public static function isValid(string $provider): bool
    {
        return in_array(strtolower($provider), self::values(), true);
    }

    /**
     * Get provider from string.
     */
    public static function fromString(string $provider): ?self
    {
        return self::tryFrom(strtolower($provider));
    }
}
