<?php

namespace FilipKochan\FileUtilities\utils;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReaderUtils
{
    public static function complete_merged_cells(Worksheet $sheet)
    {
        $merged_values = array();
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                if ($range = $cell->getMergeRange()) {
                    $value = $merged_values[$range] ?? self::find_value_for_range($sheet, $range);
                    $merged_values[$range] = $value;

                    try {
                        $cell->setValue($value);
                    } catch (\Exception $e) {
                    }
                }
            }
        }
    }

    private static function find_value_for_range(Worksheet $sheet, string $range): string
    {
        try {
            foreach (Coordinate::extractAllCellReferencesInRange($range) as $coordinate) {
                $cell = $sheet->getCell($coordinate);
                if ($cell->isMergeRangeValueCell()) {
                    return $cell->getCalculatedValue();
                }
            }
        } catch (\Exception $e) {
            return '';
        }

        return '';
    }
}