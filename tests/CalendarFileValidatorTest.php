<?php


use FilipKochan\FileUtilities\CalendarFileValidator;
use PHPUnit\Framework\TestCase;
use FilipKochan\FileUtilities\TableHeaderField;

class CalendarFileValidatorTest extends TestCase
{
    private CalendarFileValidator $cfv;
    private array $fields;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->fields = array(
            new TableHeaderField("date"),
            new TableHeaderField("title"),
            new TableHeaderField("location"),
        );
        $this->cfv = new CalendarFileValidator($this->fields, null);
    }

    public function testCanDetectTruePositive() {
        $this->assertTrue($this->cfv->is_valid(__DIR__."/../data/calendar.xlsx"));
        $this->assertTrue($this->cfv->is_valid(__DIR__."/../data/calendar2.xlsx"));
        $this->assertTrue($this->cfv->is_valid(__DIR__."/../data/calendar3.xlsx"));
    }

    public function testCanDetectTrueNegative() {
        $this->assertFalse($this->cfv->is_valid(__DIR__.'/../data/calendar4.xlsx'));
        $this->assertFalse($this->cfv->is_valid(__DIR__.'/../data/calendar5.xlsx'));
        $this->assertFalse($this->cfv->is_valid(__DIR__.'/../data/calendar6.xlsx'));
    }

    public function testDisplaysFieldsInHint() {
        $hint = $this->cfv->get_rules();
        foreach ($this->fields as $f) {
            $this->assertStringContainsString($f->name, $hint);
        }
    }

    public function testDisplaysNoErrorsWhenIdle() {
        $hint = (new CalendarFileValidator($this->fields, null))->get_error_help();
        $this->assertEquals("", $hint);
    }
}
