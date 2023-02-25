<?php

namespace FilipKochan\FileUtilities;

use Closure;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class TableHeaderField
{
    public string $name;
    public string $width;
    public FieldMapper $mapper;
    public bool $hidden;

    /**
     * @param string $name name of the column
     * @param string $width desired column width in standard CSS format
     * @param ?FieldMapper $mapper transforms the raw value of the field
     * @param bool $hidden if true, this field will not be shown in the table.
     */
    public function __construct(string $name, string $width = "", ?FieldMapper $mapper = null, bool $hidden = false)
    {
        $this->name = $name;
        $this->width = $width;
        $this->hidden = $hidden;
        if ($mapper) {
            $this->mapper = $mapper;
        } else {
            $this->mapper = (new class implements FieldMapper {
                public function map_field(Cell $old_value): string
                {
                    return $old_value->getFormattedValue();
                }
            });
        }
    }

    public function __toString(): string
    {
        if ($this->hidden) {
            return "";
        }
        $w = "";
        if ($this->width) {
            $w = "style=\"width: " . $this->width . ";\"";
        }
        return "<th $w>" . $this->name . "</th>";
    }
}