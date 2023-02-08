<?php

namespace FilipKochan\FileUtilities;

use DateTime;
use DateTimeZone;
use IntlDateFormatter;

class DateFunctions
{
    private function __construct() {}

    /**
     * @param string|int|null $date_original date string or excel calculated value
     * @param string $timezone
     * @return DateTime[]
     * @throws InvalidDateFormatException when date couldn't be formatted
     */
    public static function parse_date($date_original, string $timezone = "Europe/Prague"): array {
        if (is_null($date_original)) {
            throw new InvalidDateFormatException("date must not be null");
        }
        if (is_numeric($date_original)) {
            $UNIX_DATE = (int)(($date_original - 25569) * 86400);
            if (($d = DateTime::createFromFormat("U", $UNIX_DATE, new DateTimeZone($timezone)))) {
                return array($d);
            }
            throw new InvalidDateFormatException();
        }

        $dates = explode("-", preg_replace("/\s/", "", $date_original));
        $res = [];
        foreach ($dates as $date) {
            if (
                ($d = DateTime::createFromFormat('j/n/Y', $date, new DateTimeZone($timezone))) ||
                ($d = DateTime::createFromFormat('j.n.Y', $date, new DateTimeZone($timezone))) ||
                ($d = DateTime::createFromFormat('j/n', $date, new DateTimeZone($timezone))) ||
                ($d = DateTime::createFromFormat('j.n.', $date, new DateTimeZone($timezone)))
            ) {
                $res[] = $d;
            } else {
                throw new InvalidDateFormatException("date '" . $date . "' is invalid");
            }
        }
        return $res;
    }

    /**
     * accepts formats `dd.mm.[yyyy][-dd.mm.[yyyy]]` and `dd/mm[/yyyy][-dd/mm[/yyyy]]`
     * @param string|int|null $date_original string or excel calculated value
     * @param IntlDateFormatter $formatter
     * @return string
     * @throws InvalidDateFormatException when date couldn't be formatted
     */
    public static function format_date($date_original, IntlDateFormatter $formatter): string {
        $dates = self::parse_date($date_original);
        return implode(" - ", array_map(function ($date) use ($formatter) {
            return $formatter->format($date);
        }, $dates));
    }
}