<?php

namespace FilipKochan\FileUtilities;

use Closure;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class TableHeaderField
{
    public string $name;
    public string $width;
    public FieldMapper $mapper;

    /**
     * @param string $name name of the column
     * @param string $width desired column width in standard CSS format
     * @param FieldMapper|null $mapper transforms the raw value of the field
     */
    public function __construct(string $name, string $width = "", FieldMapper|null $mapper = null)
    {
        $this->name = $name;
        $this->width = $width;
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
        $w = "";
        if ($this->width) {
            $w = "style=\"width: " . $this->width . ";\"";
        }
        return "<th $w>" . $this->name . "</th>";
    }
}