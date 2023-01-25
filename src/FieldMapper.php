<?php

namespace FilipKochan\FileUtilities;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

interface FieldMapper
{
    /**
     * transforms raw cell value to be displayed
     * @param Cell $old_value
     * @return string
     */
    public function map_field(Cell $old_value): string;
}