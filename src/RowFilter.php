<?php

namespace FilipKochan\FileUtilities;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;

interface RowFilter
{
    /**
     * @param Row $row
     * @return bool true, if row should be kept. false otherwise
     */
    public function keep_row(Row $row): bool;
}