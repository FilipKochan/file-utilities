<?php

namespace FilipKochan\FileUtilities;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class CalendarFileValidator implements FileValidator
{
    private array $fields;
    private static Xlsx $reader;
    private static bool $has_reader = false;

    /**
     * @param TableHeaderField[] $fields
     */
    public function __construct(array $fields) {
        if (!self::$has_reader) {
            self::$reader = new Xlsx();
            self::$has_reader = true;
        }
        $this->fields = $fields;
    }
    public function is_valid(string $file): bool {
        try {
            $sheet = self::$reader->load($file)->getActiveSheet();
            foreach ($sheet->getRowIterator() as $row) {
                $is_valid = true;
                $i = 0;
                foreach ($row->getCellIterator() as $cell) {
                    if ($i >= count($this->fields)) {
                        break;
                    }
                    if ($cell->getFormattedValue() !== $this->fields[$i++]->name) {
                        $is_valid = false;
                        break;
                    }
                }
                if ($is_valid) {
                    return true;
                }
            }
            return false;
        } catch (\Exception) {return false;}
    }

    public function get_rules(): string {
        return "<p>Formát souboru: tabulka se sloupci " .
            implode(", ", array_map(function ($item) {
                return "<code>" . $item->name . "</code>";
                }, $this->fields)) . ". <br />Musí začínat od sloupce <code>A</code> na libovolném řádku.</p>";
    }

    public function get_error_help(): string
    {
        return "<p>Ujistěte se, že nahrávaný soubor obsahuje tabulku se sloupci<ul>".
        implode("\n", array_map(function ($item) {
            return "<li><code>" . $item->name . "</code></li>";
        }, $this->fields)) .
        "</ul>v tomto pořadí.</p>";
    }
}