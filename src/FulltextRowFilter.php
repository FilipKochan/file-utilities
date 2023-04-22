<?php

namespace FilipKochan\FileUtilities;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;

/**
 * Provides full-text search over all columns of the table.
 */
class FulltextRowFilter implements RowFilter
{
    private static int $rf_count = 0;

    private string $key;

    public function __construct()
    {
        $this->key = "fulltext_query" . ++self::$rf_count;
    }

    public function keep_row(Row $row): bool
    {
        $query = $this->get_query();
        if ($query == null || strlen($query) == 0) return true;

        $query = strtolower($query);
        foreach ($row->getCellIterator() as $cell) {
            if (strstr(strtolower($cell->getFormattedValue()), $query)) return true;
        }

        return false;
    }

    public function get_query_input(): string
    {
        return "<form style='display: flex; gap: 15px;'>
                    <input name='" . $this->key . "' type='text' placeholder='Zadejte jméno k vyhledání' value='" . $this->get_query() . "'>
                    <input type='submit' class='btn btn-primary' name='submit_" . $this->key . "' value='Hledej'>
                    <input type='submit' class='btn btn-danger' name='reset_" . $this->key . "' value='Smaž filtr'>
                </form>";
    }

    private function get_query(): string
    {
        return !array_key_exists('reset_' . $this->key, $_GET) && array_key_exists($this->key, $_GET) ? $_GET[$this->key] : '';
    }
}