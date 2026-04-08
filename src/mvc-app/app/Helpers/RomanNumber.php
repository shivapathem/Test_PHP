<?php

namespace App\Helpers;

/**
 * Class used to convert number to Roman
 */
class RomanNumber
{
    public static function numberToRoman($num)
    {
        $map = [
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1,
        ];

        $roman = '';
        foreach ($map as $romanChar => $value) {
            while ($num >= $value) {
                $roman .= $romanChar;
                $num -= $value;
            }
        }

        return $roman;
    }
}
