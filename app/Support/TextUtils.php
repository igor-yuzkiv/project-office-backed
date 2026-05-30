<?php

namespace App\Support;

final class TextUtils
{
    public static function acronym(string $value, int $limit = 5): string
    {
        if ($limit <= 0) {
            return '';
        }

        $words = preg_split('/[^\pL\pN]+/u', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $letters = array_map(
            static fn (string $word): string => mb_substr($word, 0, 1),
            array_slice($words, 0, $limit),
        );

        return mb_strtoupper(implode('', $letters));
    }
}
