<?php

namespace FilipKochan\FileUtilities;

use FilipKochan\FileUtilities\utils\ReaderUtils;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class CalendarFileValidator implements FileValidator
{
    private array $fields;
    private static Xlsx $reader;
    private ?RowValidator $row_validator;
    private string $status;
    private array $invalid_rows;
    private static bool $has_reader = false;

    /**
     * @param TableHeaderField[] $fields
     */
    public function __construct(array $fields, ?RowValidator $row_validator = null)
    {
        if (!self::$has_reader) {
            self::$reader = new Xlsx();
            self::$has_reader = true;
        }
        $this->fields = $fields;
        $this->row_validator = $row_validator;
        $this->status = FileValidationStatus::FILE_IDLE;
    }

    public function is_valid(string $file): bool
    {
        try {
            $sheet = self::$reader->load($file)->getActiveSheet();
            ReaderUtils::complete_merged_cells($sheet);
            $start_index = -1;
            foreach ($sheet->getRowIterator() as $row) {
                $is_valid = true;
                $i = 0;
                foreach ($row->getCellIterator() as $cell) {
                    if ($i >= count($this->fields)) {
                        break;
                    }
                    if (mb_strtoupper(trim($cell->getFormattedValue())) !== mb_strtoupper(trim($this->fields[$i++]->name))) {
                        $is_valid = false;
                        break;
                    }
                }
                if ($is_valid) {
                    $start_index = $row->getRowIndex() + 1;
                    break;
                }
            }

            if ($start_index === -1) {
                $this->status = FileValidationStatus::FILE_INCORRECTLY_FORMATTED;
                return false;
            }

            $this->invalid_rows = array();
            if ($this->row_validator) {
                foreach ($sheet->getRowIterator($start_index) as $row) {
                    $is_empty = true;
                    foreach ($row->getCellIterator('A', chr(ord('A') + count($this->fields) - 1)) as $cell) {
                        if ($cell->getFormattedValue()) {
                            $is_empty = false;
                            break;
                        }
                    }
                    if ($is_empty) {
                        break;
                    }

                    if (!$this->row_validator->is_valid($row)) {
                        $this->invalid_rows[] = $row->getRowIndex();
                    }
                }

                if (count($this->invalid_rows) > 0) {
                    $this->status = FileValidationStatus::FILE_INVALID_ROW;
                    return false;
                }
            }

            $this->status = FileValidationStatus::FILE_SUCCESS;
            return true;
        } catch (\Exception $e) {
            $this->status = FileValidationStatus::FILE_ERROR;
            return false;
        }
    }

    public function get_rules(): string
    {
        return "<div>
                    <h2 class='fs-5'>Formát souboru:</h2>
                    <p>Tabulka se sloupci " .
            implode(", ", array_map(function ($item) {
                return "<code>" . $item->name . "</code>";
            }, $this->fields)) . ".</p><p>Musí začínat od sloupce <code>A</code> na libovolném řádku."
            . ($this->row_validator ? $this->row_validator->get_rules() : "") . "</div>";
    }

    public function get_error_help(): string
    {
        switch ($this->status) {
            case FileValidationStatus::FILE_INCORRECTLY_FORMATTED:
                return "<p>Ujistěte se, že nahrávaný soubor obsahuje tabulku se sloupci<ul>" .
                    implode("\n", array_map(function ($item) {
                        return "<li><code>" . $item->name . "</code></li>";
                    }, $this->fields)) .
                    "</ul>v tomto pořadí.</p>";
            case FileValidationStatus::FILE_ERROR:
                return "<p>Při nahrávání souboru nastala chyba. Zkuste to prosím později.</p>";
            case FileValidationStatus::FILE_INVALID_ROW:
                return ($this->row_validator->get_error_help()) .
                    "<p>na řádcích:</p>
                    <ul>" .
                    implode("\n", array_map(function ($item) {
                        return "<li><code>$item</code></li>";
                    }, $this->invalid_rows)) .
                    "</ul>";
            case FileValidationStatus::FILE_SUCCESS:
            case FileValidationStatus::FILE_IDLE:
            default:
                return "";
        }
    }
}