<?php

namespace FilipKochan\FileUtilities;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;

/**
 * Compose two row filters with a callback.
 */
class ComposedRowFilter implements RowFilter
{
    private RowFilter $rf1;
    private RowFilter $rf2;
    private $compose_with;

    /**
     * @param RowFilter $rf1 first row filter
     * @param RowFilter $rf2 second row filter
     * @param callable $compose_with 'compose_with(first result, second result)' is returned as the overall result
     */
    public function __construct(RowFilter $rf1, RowFilter $rf2, callable $compose_with)
    {
        $this->rf1 = $rf1;
        $this->rf2 = $rf2;
        $this->compose_with = $compose_with;
    }

    public function keep_row(Row $row): bool
    {
        $f = $this->compose_with;
        return $f($this->rf1->keep_row($row), $this->rf2->keep_row($row));
    }
}