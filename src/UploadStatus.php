<?php

namespace FilipKochan\FileUtilities;
enum UploadStatus {
    case UPLOAD_IDLE;
    case UPLOAD_ERROR;
    case UPLOAD_SUCCESS;
    case UPLOAD_NOFILE;
    case UPLOAD_UNAUTHORIZED;
    case UPLOAD_CAPTCHA_FAILED;
    case UPLOAD_WRONG_FORMAT;
}