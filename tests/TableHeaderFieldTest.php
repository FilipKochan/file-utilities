<?php


use FilipKochan\FileUtilities\TableHeaderField;
use PHPUnit\Framework\TestCase;

class TableHeaderFieldTest extends TestCase
{
    public function testCanDisplay() {
        $thf = new TableHeaderField("header1", "40px");
        $this->assertStringContainsString("width: 40px", $thf);
        $this->assertStringContainsString("header1", $thf);
    }
}
