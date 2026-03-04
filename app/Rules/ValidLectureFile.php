<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use App\Services\HelperService;

class ValidLectureFile implements ValidationRule
{
    protected $type;
    protected $lectureType;
    protected $allowedTypes;

    public function __construct($type, $lectureType, $allowedTypes)
    {
        $this->type = $type;
        $this->lectureType = $lectureType;
        $this->allowedTypes = $allowedTypes;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->type === 'lecture' && $this->lectureType === 'file') {
            if (empty($value)) {
                $fail("The $attribute field is required when lecture type is file.");
                return;
            }

            $ext = strtolower($value->getClientOriginalExtension());
            if (!in_array($ext, $this->allowedTypes)) {
                $fail("The $attribute must be one of the following types: " . implode(', ', $this->allowedTypes) . ".");
            }

            // Get max video upload size from settings (in MB), default to 10MB
            $maxVideoSize = HelperService::systemSettings('max_video_upload_size');
            $maxSizeMB = !empty($maxVideoSize) ? (float) $maxVideoSize : 10;
            $maxSizeBytes = $maxSizeMB * 1024 * 1024;

            if ($value->getSize() > $maxSizeBytes) {
                $fail("The $attribute must not exceed {$maxSizeMB}MB.");
            }
        }
    }
}
