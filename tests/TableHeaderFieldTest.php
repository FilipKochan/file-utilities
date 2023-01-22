<?php


use FilipKochan\FileUtilities\TableHeaderField;
use PHPUnit\Framework\TestCase;

class TableHeaderFieldTest extends TestCase
{
    public function testCanDisplay() {
        $thf = new TableHeaderField("header1", "40px");
        self::assertStringContainsString("width: 40px", $thf);
        self::assertStringContainsString("header1", $thf);
    }
}
