<?php


use FilipKochan\FileUtilities\TableHeaderField;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;

class TableHeaderFieldTest extends TestCase
{
    public function testCanDisplay() {
        $thf = new TableHeaderField("header1", "40px");
        self::assertStringContainsString("width: 40px", $thf);
        self::assertStringContainsString("header1", $thf);
    }

    public function testHasDefaultFormatter() {
        $thf = new TableHeaderField('col1');
        $ss = new Spreadsheet();
        $s = $ss->getActiveSheet();
        $expected = 'xxxabc';
        $s->createNewCell('A1');
        $s->setCellValue('A1', $expected);
        $cell = $s->getCell('A1');
        $actual = $thf->mapper->map_field($cell);
        self::assertEquals($expected, $actual);
    }
}
