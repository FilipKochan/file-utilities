<?php


use FilipKochan\FileUtilities\DateRowValidator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PHPUnit\Framework\TestCase;

class DateRowValidatorTest extends TestCase
{
    private DateRowValidator $drv;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->drv = new DateRowValidator('B');
    }

    public function testCanValidateCorrectDate()
    {
        $sheet = (new Spreadsheet())->getActiveSheet();

        $i = 1;
        $this->add_date($sheet, "01.01.2000", $i++);
        $this->add_date($sheet, "12. 11.2020   ", $i++);
        $this->add_date($sheet, "7. 10.  2020", $i++);
        $this->add_date($sheet, "  6.10.", $i++);
        $this->add_date($sheet, "5   /  12", $i++);
        $this->add_date($sheet, "13 /    12   /  2025 ", $i++);
        $this->add_date($sheet, "17.2. - 18.3.", $i++);
        $this->add_date($sheet, "17.2.2020-19.2.2020 ", $i++);
        $this->add_date($sheet, "5 /   12 - 7 / 12    ", $i++);
        $this->add_date($sheet, "7/2/2023-9/2/2026", $i++);

        foreach ($sheet->getRowIterator() as $row) {
            self::assertTrue($this->drv->is_valid($row), "date '" .
                $row->getCellIterator('B')->current()->getFormattedValue()
                . "' should be valid");
        }
    }

    public function testCanDetectInvalidDate()
    {
        $sheet = (new Spreadsheet())->getActiveSheet();
        $i = 1;
        $this->add_date($sheet, "", $i++);
        $this->add_date($sheet, "xxx", $i++);
        $this->add_date($sheet, "17.a.2022", $i++);
        $this->add_date($sheet, "/3/6", $i++);
        $this->add_date($sheet, "/", $i++);
        $this->add_date($sheet, "-", $i++);
        $this->add_date($sheet, "1.4.-14,5", $i++);
        $this->add_date($sheet, "-18.5.2023", $i++);
        $this->add_date($sheet, "3.11.-2022", $i++);
        $this->add_date($sheet, "7.7.7.7", $i++);
        $this->add_date($sheet, "2022-2023", $i++);
        $this->add_date($sheet, "13/abc", $i++);
        $this->add_date($sheet, "x/12/2020", $i++);
        $this->add_date($sheet, "//", $i++);

        foreach ($sheet->getRowIterator() as $row) {
            self::assertFalse($this->drv->is_valid($row), "date '" .
                $row->getCellIterator('B')->current()->getFormattedValue()
                . "' should be invalid");
        }
    }

    private function add_date(Worksheet $sheet, string $date, int $row)
    {
        $sheet->setCellValue("B$row", $date);
    }
}