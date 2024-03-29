<?php

namespace FilipKochan\FileUtilities;

use DateTime;
use DateTimeZone;
use Exception;
use IntlDateFormatter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class FileUploader
{
    private string $upload_directory;
    private string $file_prefix;
    private string $extension;
    private string $upload_status;
    private static int $count = 0;
    private string $captcha_secret;
    private string $upload_password;
    private FileValidator $file_validator;

    public function __construct(string $upload_directory, string $file_prefix,
                                string $extension, FileValidator $file_validator,
                                string $captcha_secret, string $upload_password)
    {
        $this->file_prefix = $file_prefix;
        $this->upload_directory = $upload_directory;
        $this->extension = $extension;
        $this->captcha_secret = $captcha_secret;
        $this->upload_password = $upload_password;
        $this->file_validator = $file_validator;
        $this->upload_status = UploadStatus::UPLOAD_IDLE;
        static::$count++;
    }

    public function get_last_upload(): string
    {
        if (!is_dir($this->upload_directory)) {
            return "";
        }

        try {
            $d = opendir($this->upload_directory);
            if (!$d) {
                return "";
            }
            $last = null;
            while (($f = readdir($d))) {
                if (is_dir($f)) {
                    continue;
                }
                if (preg_match("/^" . $this->file_prefix . "/", $f)) {
                    try {
                        $dt = new DateTime(explode(".", explode("_", $f)[1])[0],
                            new DateTimeZone('Europe/Prague'));
                        if (!$last || ($dt > $last)) {
                            $last = $dt;
                        }
                    } catch (Exception $e) {
                    }
                }
            }
            if (!$last) {
                return "";
            }

            $f = new IntlDateFormatter(
                "cs_CZ",
                IntlDateFormatter::FULL,
                IntlDateFormatter::SHORT,
                new DateTimeZone("Europe/Prague")
            );

            return "<p>Poslední soubor byl nahrán: <i>" . $f->format($last) . "</i>.</p>";
        } finally {
            closedir($d);
        }
    }

    private function validate_captcha(): bool
    {
        if (!$this->captcha_secret) {
            return true;
        }
        try {
            $client = new Client();
            $res = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => $this->captcha_secret,
                    'response' => $_POST['g-recaptcha-response'],
                ]
            ]);

            if ($res->getStatusCode() !== 200) {
                return false;
            }
            $data = json_decode($res->getBody());
            return !!$data->success;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    public function current_upload_status(): string
    {
        return $this->upload_status;
    }

    public function handle_upload(): void
    {
        if (!key_exists('sent', $_POST) || !$_POST['sent']) {
            return;
        }

        if (!$this->validate_captcha()) {
            $this->upload_status = UploadStatus::UPLOAD_CAPTCHA_FAILED;
            return;
        }

        if (!key_exists('pwd', $_POST)
            || ($this->upload_password !== $_POST['pwd'])) {
            $this->upload_status = UploadStatus::UPLOAD_UNAUTHORIZED;
            return;
        }

        if (!key_exists($this->file_prefix, $_FILES) || !($f = $_FILES[$this->file_prefix])['size']) {
            $this->upload_status = UploadStatus::UPLOAD_NO_FILE;
            return;
        }

        try {
            if (!$this->file_validator->is_valid($f['tmp_name'])) {
                $this->upload_status = UploadStatus::UPLOAD_WRONG_FORMAT;
                return;
            }

            if (!is_dir($this->upload_directory)) {
                mkdir($this->upload_directory);
            }

            $new_filename = $this->upload_directory .
                DIRECTORY_SEPARATOR .
                $this->file_prefix .
                '_' .
                (new DateTime('now', new DateTimeZone('Europe/Prague')))->format("c") .
                '.' .
                $this->extension;

            move_uploaded_file($f['tmp_name'], $new_filename);
            $this->upload_status = UploadStatus::UPLOAD_SUCCESS;
        } catch (Exception $e) {
            $this->upload_status = UploadStatus::UPLOAD_ERROR;
        }
    }

    public function get_upload_status(?string $calendarVisitLink = null): string
    {
        switch ($this->upload_status) {
            case UploadStatus::UPLOAD_NO_FILE:
                return "<div class='alert alert-info'>Nebyl zvolen žádný soubor k nahrání.</div>";
            case UploadStatus::UPLOAD_SUCCESS:
                return "<div class='alert alert-success'>Soubor byl úspěšně nahrán."
                    . ($calendarVisitLink
                        ? "<br/>Pro zobrazení nového kalendáře můžete kliknout <a href='$calendarVisitLink'>zde</a>."
                        : "") . "</div>";
            case UploadStatus::UPLOAD_ERROR:
                return "<div class='alert alert-danger'>Během nahrávání souboru nastala chyba.</div>";
            case UploadStatus::UPLOAD_UNAUTHORIZED:
                return "<div class='alert alert-danger'>Neplatné heslo.</div>";
            case UploadStatus::UPLOAD_CAPTCHA_FAILED:
                return "<div class='alert alert-danger'>Jste robot.</div>";
            case UploadStatus::UPLOAD_WRONG_FORMAT:
                return "<div class='alert alert-danger'>Vámi nahraný soubor není ve správném formátu." .
                    $this->file_validator->get_error_help() . "</div>";
            default:
                return "";
        }
    }

    public function generate_form(string $action, string $site_key): string
    {
        $f = $this->file_prefix;
        $e = $this->extension;
        $c = self::$count;
        $gr = $site_key ? "g-recaptcha" : "";
        $script = self::$count === 1 || $site_key ? '<script defer src="https://www.google.com/recaptcha/api.js"></script>' : "";
        $rules = $this->file_validator->get_rules();
        return <<<STR
                <form action="$action" id="_form_$c" method="post" enctype="multipart/form-data" class="d-flex flex-column gap-3 align-items-start">
                    <div>
                        $rules
                        <label for="_file_input_$c">Zvolte soubor ve formátu <code>$e</code>.</label>
                        <input id="_file_input_$c" type="file" name="$f" accept=".$e" class="form-control">
                    </div>
                    <div>
                        <label for="_pwd_$c">Heslo</label>
                        <input id="_pwd_$c" type="password" name="pwd" placeholder="heslo" class="form-control">
                    </div>
                    <input type="hidden" name="sent" value="true" />
                    <button class="$gr btn btn-primary" data-sitekey="$site_key" data-callback='onSubmit$c' data-action='submit'>Nahrát</button>
                </form>
                $script
                <script>
                    function onSubmit$c(token) {
                        document.getElementById("_form_$c").submit();
                    }
                </script>
            STR;
    }
}