<?php

namespace FilipKochan\FileUtilities;

use DateTime;
use DateTimeZone;
use Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;

/**
 * filters out dates in the past
 */
class PastDateRowFilter implements RowFilter
{
    private string $date_column;
    public function __construct(string $date_column)
    {
        $this->date_column = $date_column;
    }

    public function keep_row(Row $row): bool
    {
        try {
            foreach ($row->getCellIterator() as $cell) {
                if ($cell->getColumn() !== $this->date_column) {
                    continue;
                }

                $v = $cell->getCalculatedValue();
                $dates = DateFunctions::parse_date($v);
                $yesterday = (new DateTime('now', new DateTimeZone('Europe/Prague')))->modify('-1 day');
                $date = $dates[count($dates) - 1];
                return (double)$date->format('U') > (double)$yesterday->format('U');
            }
            return false;
        } catch (Exception) {return false;}
    }
}