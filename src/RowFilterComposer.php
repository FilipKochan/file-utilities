<?php

namespace FilipKochan\FileUtilities;

/**
 * Allows combining multiple row filters with predefined methods.
 */
class RowFilterComposer
{
    public static function and(RowFilter $first, RowFilter $second): RowFilter
    {
        return new ComposedRowFilter($first, $second, function ($f, $s) {
            return $f && $s;
        });
    }
    public static function or(RowFilter $first, RowFilter $second): RowFilter
    {
        return new ComposedRowFilter($first, $second, function ($f, $s) {
            return $f || $s;
        });
    }
}
