<?php

namespace App\Enum;

enum StorageCapacity: int
{
    case GB_32 = 32;
    case GB_64 = 64;
    case GB_128 = 128;
    case GB_256 = 256;
    case GB_512 = 512;
    case TB_1 = 1024;
    case TB_2 = 2048;

    public function label(): string
    {
        return match (true) {
            $this->value >= 1024 => ($this->value / 1024) . ' TB',
            default => $this->value . ' GB',
        };
    }
}
