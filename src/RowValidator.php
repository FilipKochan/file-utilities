<?php

namespace FilipKochan\FileUtilities;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;

interface RowValidator
{
    public function is_valid(Row $row): bool;

    /**
     * @return string description of the row requirements
     */
    public function get_rules(): string;

    /**
     * get help for the last invalid row
     * @return string
     */
    public function get_error_help(): string;
}