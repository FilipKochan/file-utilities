<?php

namespace FilipKochan\FileUtilities;

class TableHeaderField
{
    public string $name;
    public string $width;

    /**
     * @param string $name name of the column
     * @param string $width desired column width in standard CSS format
     */
    public function __construct(string $name, string $width = "")
    {
        $this->name = $name;
        $this->width = $width;
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