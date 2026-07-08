<?php

namespace App\Support;

final class ColorUtil
{
    public static function randomHexColor(): string
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
