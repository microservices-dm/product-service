<?php

namespace App\Enum;

enum OperatingSystem: string
{
    case IOS = 'ios';
    case ANDROID = 'android';
    case HARMONY_OS = 'harmonyos';
    case WINDOWS = 'windows';

    public function label(): string
    {
        return match ($this) {
            self::IOS => 'iOS',
            self::ANDROID => 'Android',
            self::HARMONY_OS => 'HarmonyOS',
            self::WINDOWS => 'Windows',
        };
    }
}
