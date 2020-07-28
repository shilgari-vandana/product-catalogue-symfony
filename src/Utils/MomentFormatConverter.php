<?php

namespace App\Utils;

/**
 * This class is used to convert PHP date format to moment.js format.
 */
class MomentFormatConverter
{
    /**
     * This defines the mapping between PHP ICU date format (key) and moment.js date format (value)
     * @var array
     */
    private static $formatConvertRules = [
        // year
        'yyyy' => 'YYYY', 'yy' => 'YY', 'y' => 'YYYY',
        // day
        'dd' => 'DD', 'd' => 'D',
        // day of week
        'EE' => 'ddd', 'EEEEEE' => 'dd',
        // timezone
        'ZZZZZ' => 'Z', 'ZZZ' => 'ZZ',
        // letter 'T'
        '\'T\'' => 'T',
    ];

    /**
     * Returns associated moment.js format.
     */
    public function convert(string $format): string
    {
        return strtr($format, self::$formatConvertRules);
    }
}
