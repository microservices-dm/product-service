<?php

namespace App\Enum;

enum ScreenSize: string
{
    case S_5_5 = '5.5';
    case S_6_1 = '6.1';
    case S_6_4 = '6.4';
    case S_6_7 = '6.7';
    case S_6_9 = '6.9';
    case S_8_3 = '8.3';
    case S_10_2 = '10.2';
    case S_10_9 = '10.9';
    case S_11_0 = '11.0';
    case S_12_9 = '12.9';
    case S_13_0 = '13.0';

    public function label(): string
    {
        return $this->value . '"';
    }
}
