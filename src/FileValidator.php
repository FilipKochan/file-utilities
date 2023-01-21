<?php

namespace FilipKochan\FileUtilities;

interface FileValidator {
    /**
     * checks, if the uploaded file is valid
     * @param string $file path to the file
     * @return bool
     */
    public function is_valid(string $file): bool;

    /**
     * @return string description of the uploaded file requirements
     */
    public function get_description(): string;

    /**
     * get help for the last invalid file
     * @return string
     */
    public function get_error_help(): string;
}
