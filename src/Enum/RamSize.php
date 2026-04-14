<?php

namespace App\Enum;

enum RamSize: int
{
    case GB_2 = 2;
    case GB_3 = 3;
    case GB_4 = 4;
    case GB_6 = 6;
    case GB_8 = 8;
    case GB_12 = 12;
    case GB_16 = 16;
    case GB_18 = 18;

    public function label(): string
    {
        return $this->value . ' GB';
    }
}
