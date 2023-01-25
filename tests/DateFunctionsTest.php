<?php


use FilipKochan\FileUtilities\DateFunctions;
use FilipKochan\FileUtilities\InvalidDateFormatException;
use PHPUnit\Framework\TestCase;

class DateFunctionsTest extends TestCase
{
    public function testParse_date_valid()
    {
        $dates = ['10.4.', '12.11.2020', '1/1/2023', '11/2', '1. 12. ', '11/ 10/ 2021', '1.2.-2.2.',
            '11/3 - 12 / 3', '4 / 2 / 2023 - 5 / 2 / 2023', '1.1.1'];
        try {
            foreach ($dates as $date) {
                self::assertNotEmpty(DateFunctions::parse_date($date), "date '" . $date . "' should be valid");
            }
        } catch (InvalidDateFormatException) {self::fail("date '" . $date . "' should be valid");}
    }

    public function testParse_date_invalid()
    {
        $dates = ["", '1.1.x', '1-2', 'abc-def', '1 2  / 3  . 2', '2/2/3/40-1/2', '1..2', '20.3.2020.',
            '2.6/2021', '13/1.2000', '1/14/2004/', '3//2001'];
        foreach ($dates as $date) {
            try {
                echo DateFunctions::parse_date($date)[0]->format('r');
                self::fail("date '" . $date . "' should be invalid");
            } catch (InvalidDateFormatException) {self::assertTrue(true, "date '" . $date . "' should be invalid");}
        }
    }

    public function testFormat_date()
    {
        $dates = [
            '1.1.2001' => 'ledna',
            '2.1.-11.2.' => '-',
            '3/2' => 'února',
            '11.12.' => 'prosince',
            '11.12.2021' => '2021',
            '11.12.2021-14/12/2021' => '14. prosince',
        ];

        $f = new IntlDateFormatter('cs_CZ',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Europe/Prague');

        foreach ($dates as $date => $should_contain) {
            try {
                self::assertStringContainsString($should_contain, DateFunctions::format_date($date, $f));
            } catch (InvalidDateFormatException) {self::fail();}
        }
    }
}
