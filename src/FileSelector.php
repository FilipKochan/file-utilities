<?php

namespace FilipKochan\FileUtilities {
    interface FileSelector
    {
        /**
         * traverses the directory and selects a file to be used
         * @param string $directory
         * @return string
         */
        public function get_filename(string $directory): string;
    }
}