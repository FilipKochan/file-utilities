<?php


use FilipKochan\FileUtilities\LastUploadedFileSelector;
use PHPUnit\Framework\TestCase;

class LastUploadedFileSelectorTest extends TestCase
{
    private const dir = __DIR__.'/tmp/uploads';
    private const prefix = 'f';
    private LastUploadedFileSelector $lastUploadedFileSelector;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->lastUploadedFileSelector = new LastUploadedFileSelector(self::prefix);
    }


    public static function setUpBeforeClass(): void {
        mkdir(self::dir, recursive: true);

        touch(self::dir."/f_2022-09-04T11:06:22+01:00");
        touch(self::dir."/f_2023-01-26T11:06:22+01:00");
        touch(self::dir."/f_2023-01-20T11:06:22+01:00");
        touch(self::dir."/f_2021-04-04T11:06:22+01:00");
    }

    public function testCanChooseLastFile() {
        self::assertEquals('f_2023-01-26T11:06:22+01:00', $this->lastUploadedFileSelector->get_filename(self::dir));
    }

    public static function tearDownAfterClass(): void {
        $d = opendir(self::dir);
        try {
            while (($f = readdir($d))) {
                if (is_file(self::dir.'/'.$f)) {
                    unlink(self::dir.'/'.$f);
                }
            }
            rmdir(self::dir);
            rmdir(dirname(self::dir));
        } finally {
            closedir($d);
        }
    }
}
