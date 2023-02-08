<?php

namespace FilipKochan\FileUtilities;

use Exception;
use IntlDateFormatter;
use DateTimeZone;
use DateTime;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelReader {
    private int $row;
    private int $data_start;
    private array $fields;
    private ?Worksheet $sheet;
    private FileSelector $fs;
    private string $files_directory;
    private static Xlsx $reader;
    private static bool $reader_set = false;
    private RowFilter $rf;

    /**
     * @param string $file_prefix
     * @param string $directory directory of files
     * @param TableHeaderField[] $fields table columns
     * @param FileSelector $fs this is used to select current calendar from a directory
     * @param RowFilter|null $rf which rows will be hidden
     * @throws Exception
     */
    public function __construct(string $file_prefix,
                                string $directory,
                                array $fields,
                                FileSelector $fs,
                                ?RowFilter $rf = null)
    {
        if (!static::$reader_set) {
            static::$reader = new Xlsx();
            static::$reader_set = true;
        }
        $this->fields = $fields;
        $this->fs = $fs;
        $this->files_directory = $directory;
        $this->sheet = null;
        if ($rf) {
            $this->rf = $rf;
        } else {
            $this->rf = (new class implements RowFilter {
                public function keep_row(Row $row): bool {return true;}
            });
        }
        try {
            $active_calendar = $this->fs->get_filename($directory);
            if ($active_calendar) {
                $this->sheet = static::$reader->load($directory.DIRECTORY_SEPARATOR.$active_calendar)->getActiveSheet();
            }
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $exception) {
            echo $exception->getMessage();
            throw new Exception("<p>Nelze otevřít soubor <code>" . basename($file_prefix) . "</code> - existuje?</p>");
        }
        $ds = $this->find_data_start();
        $this->row = $ds;
        $this->data_start = $ds;
    }

    /**
     * returns the `thead` element according to `fields`
     * @return string table header
     */
    private function get_header() : string {
        return "<thead><tr>" . implode('', $this->fields) . "</tr></thead>";
    }

    /**
     * reads a single row from file,
     * the data is wrapped in `tr` element with fields as `td`
     * @return string row
     */
    private function get_row() : string {
        if (!$this->sheet) {
            return "";
        }

        $row_empty = true;
        $res = "<tr>";
        if ($this->row > $this->sheet->getHighestRow()) {
            return "";
        }

        $r = ($this->sheet->getRowIterator($this->row))->current();
        $this->row++;

        if (!$this->rf->keep_row($r)) {
            // shouldn't keep the row -> skip to next
            return $this->get_row();
        }

        $idx = 0;
        foreach ($r->getCellIterator('A', chr(ord('A') + count($this->fields) - 1)) as $cell) {
            if ($cell->getFormattedValue()) {
                $row_empty = false;
            }
            $transformed = $this->fields[$idx++]->mapper->map_field($cell);
            $res .= "<td>$transformed</td>";
        }
        $res .= "</tr>";
        if ($row_empty) {
            return "";
        }
        return $res;
    }

    /**
     * @throws Exception
     */
    private function find_data_start(): int {
        if (!$this->sheet) {
            return 0;
        }
        foreach ($this->sheet->getRowIterator() as $row) {
            $is_header = true;
            $i = 0;
            foreach ($row->getCellIterator() as $cell) {
                if ($i >= count($this->fields)) {
                    break;
                }

                if ($cell->getFormattedValue() !== $this->fields[$i++]->name) {
                    $is_header = false;
                    break;
                }
            }
            if ($is_header) {
                return $row->getRowIndex() + 1;
            }
        }

        throw new Exception("Kalenář není v požadovaném formátu.");
    }

    /**
     * resets the position of next row to be read back to start
     * @return void
     */
    private function reset_position() : void {
        $this->row = $this->data_start;
    }

    /**
     * @param string $tag_name
     * @return string last modified date in format "<$tag_name>aktualizace: 'datum aktualizace'&lt;/$tag_name>".
     * if date couldn't be found, returns an empty string
     */
    public function get_modified_date(string $tag_name = "i") : string {
        try {
            if (!($file = $this->fs->get_filename($this->files_directory))) {
                return "";
            }
            $f = new IntlDateFormatter(
                "cs_CZ",
                IntlDateFormatter::FULL,
                IntlDateFormatter::SHORT,
                new DateTimeZone("Europe/Prague")
            );
            $date = $f->format(new DateTime(explode(".", explode("_", $file)[1])[0],
                new DateTimeZone('Europe/Prague')));

            return "<$tag_name>Poslední aktualizace: $date.</$tag_name>";
        } catch (Exception $e) {return "";}
    }

    /**
     * transforms the input `.xlsx` file into an HTML table
     * @param string $class css class applied to `table`
     * @return string table
     */
    public function get_table(string $class = "") : string {
        if (!$this->sheet) {
            return "<p>Žádný kalendář není k dispozici.</p>";
        }

        $c = $class ? "class=\"$class\"" : "";
        $res = "<table $c>";
        $res .= $this->get_header();
        $res .= "<tbody>";
        while (($row = $this->get_row())) {
            $res .= $row;
        }
        $res .= "</tbody></table>";
        $this->reset_position();
        return $res;
    }
}