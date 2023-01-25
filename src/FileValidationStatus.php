<?php

namespace FilipKochan\FileUtilities;

enum FileValidationStatus
{
    case FILE_INVALID_ROW;
    case FILE_IDLE;
    case FILE_SUCCESS;
    case FILE_INCORRECTLY_FORMATTED;
    case FILE_ERROR;
}
