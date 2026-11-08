<?php

namespace App\Support;

final class UploadLimits
{
    public static function current(): array
    {
        $file = max(
            0,
            ini_parse_quantity((string) ini_get('upload_max_filesize'))
        );

        $post = max(
            0,
            ini_parse_quantity((string) ini_get('post_max_size'))
        );

        $count = max(
            0,
            (int) ini_get('max_file_uploads')
        );

        return [
            'fileBytes' => $file > 0
                ? min(10 * 1024 * 1024, $file)
                : 10 * 1024 * 1024,

            'postBytes' => $post,

            'maxFiles' => min(10, $count),

            'enabled' => filter_var(
                ini_get('file_uploads'),
                FILTER_VALIDATE_BOOLEAN
            ),
        ];
    }
}