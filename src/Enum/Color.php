<?php

namespace App\Enum;

enum Color: string
{
    case BLACK = 'black';
    case WHITE = 'white';
    case SILVER = 'silver';
    case GOLD = 'gold';
    case BLUE = 'blue';
    case RED = 'red';
    case GREEN = 'green';
    case PURPLE = 'purple';
    case PINK = 'pink';
    case GRAY = 'gray';

    public function label(): string
    {
        return match ($this) {
            self::BLACK => 'Черный',
            self::WHITE => 'Белый',
            self::SILVER => 'Серебристый',
            self::GOLD => 'Золотой',
            self::BLUE => 'Синий',
            self::RED => 'Красный',
            self::GREEN => 'Зеленый',
            self::PURPLE => 'Фиолетовый',
            self::PINK => 'Розовый',
            self::GRAY => 'Серый',
        };
    }
}
