<?php

namespace FilipKochan\FileUtilities;
use DateTime;
use DateTimeZone;
use Exception;

class LastUploadedFileSelector implements FileSelector {
    private string $file_prefix;
    public function __construct(string $file_prefix) {
        $this->file_prefix = $file_prefix;
    }
    public function get_filename(string $directory): ?string
    {
        if (!is_dir($directory)) {
            return "";
        }
        try {
            $last = null;
            $last_file = null;
            $d = opendir($directory);
            while (($f = readdir($d))) {
                if (is_dir($f)) {
                    continue;
                }

                if (preg_match("/^".$this->file_prefix."/", $f)) {
                    $dt = new DateTime(explode(".", explode("_", $f)[1])[0],
                        new DateTimeZone('Europe/Prague'));
                    if (!$last || ($dt > $last)) {
                        $last = $dt;
                        $last_file = $f;
                    }
                }
            }
            return $last_file;
        } catch (Exception $e) {
            return "";
        } finally {
            closedir($d);
        }
    }
}