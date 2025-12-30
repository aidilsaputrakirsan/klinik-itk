<?php

namespace App\Helpers;

class Terbilang
{
    private static array $angka = [
        '',
        'satu',
        'dua',
        'tiga',
        'empat',
        'lima',
        'enam',
        'tujuh',
        'delapan',
        'sembilan',
        'sepuluh',
        'sebelas'
    ];

    public static function convert(int $number): string
    {
        if ($number < 0) {
            return 'minus ' . self::convert(abs($number));
        }

        if ($number < 12) {
            return self::$angka[$number];
        }

        if ($number < 20) {
            return self::$angka[$number - 10] . ' belas';
        }

        if ($number < 100) {
            return self::$angka[(int)($number / 10)] . ' puluh ' . self::$angka[$number % 10];
        }

        if ($number < 200) {
            return 'seratus ' . self::convert($number - 100);
        }

        if ($number < 1000) {
            return self::$angka[(int)($number / 100)] . ' ratus ' . self::convert($number % 100);
        }

        if ($number < 2000) {
            return 'seribu ' . self::convert($number - 1000);
        }

        if ($number < 1000000) {
            return self::convert((int)($number / 1000)) . ' ribu ' . self::convert($number % 1000);
        }

        if ($number < 1000000000) {
            return self::convert((int)($number / 1000000)) . ' juta ' . self::convert($number % 1000000);
        }

        return self::convert((int)($number / 1000000000)) . ' milyar ' . self::convert($number % 1000000000);
    }
}
