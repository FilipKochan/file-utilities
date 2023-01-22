<?php


use FilipKochan\FileUtilities\CalendarFileValidator;
use FilipKochan\FileUtilities\FileUploader;
use FilipKochan\FileUtilities\TableHeaderField;
use FilipKochan\FileUtilities\UploadStatus;
use PHPUnit\Framework\TestCase;

class FileUploaderTest extends TestCase
{
    private FileUploader $uploader;
    private static string $dir = __DIR__.DIRECTORY_SEPARATOR.'tmp';

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->uploader = new FileUploader(self::$dir,
            "uploaded",
            "xlsx",
            new CalendarFileValidator(array(new TableHeaderField("date"), new TableHeaderField("title")), null),
            "",
            "password1");
    }

    public static function setUpBeforeClass(): void
    {
        mkdir(self::$dir);
    }

    public static function tearDownAfterClass(): void
    {
        rmdir(self::$dir);
    }

    public function testCanGenerateForm() {
        $f = trim($this->uploader->generate_form('index.php', ""));
        $this->assertStringStartsWith("<form ", $f);
        $this->assertStringContainsString("submit", $f);
        $this->assertStringContainsString("</form>", $f);
    }

    public function testValidatesPassword() {
        $_POST['sent'] = true;
        if (key_exists("pwd", $_POST)) {
            unset($_POST['pwd']);
        }
        $this->uploader->handle_upload();
        $this->assertEquals(UploadStatus::UPLOAD_UNAUTHORIZED,
            $this->uploader->current_upload_status(),
            "upload status unauthorized");

        $_POST['pwd'] = '$#^@*@__wrong_password__';
        $this->uploader->handle_upload();
        $this->assertEquals(UploadStatus::UPLOAD_UNAUTHORIZED,
            $this->uploader->current_upload_status());

        $_POST['pwd'] = 'password1';
        $this->uploader->handle_upload();
        $this->assertNotEquals(UploadStatus::UPLOAD_UNAUTHORIZED,
            $this->uploader->current_upload_status());
    }

    public function testIgnoresUntouchedForm() {
        unset($_POST['sent']);
        $this->uploader->handle_upload();
        $this->assertEquals(UploadStatus::UPLOAD_IDLE, $this->uploader->current_upload_status());
    }

    public function testCanHandleNoFile() {
        $_POST['sent'] = true;
        $_POST['pwd'] = 'password1';
        $this->uploader->handle_upload();
        $this->assertEquals(UploadStatus::UPLOAD_NOFILE, $this->uploader->current_upload_status());
    }
}
