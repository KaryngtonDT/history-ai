<?php

declare(strict_types=1);

namespace App\Domain\Content;

enum ContentSourceType: string
{
    case UploadPdf = 'upload_pdf';
    case UploadAudio = 'upload_audio';
    case UploadVideo = 'upload_video';
    case YoutubeUrl = 'youtube_url';
}
