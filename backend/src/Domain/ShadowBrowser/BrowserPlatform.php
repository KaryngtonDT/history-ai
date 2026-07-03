<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

enum BrowserPlatform: string
{
    case Youtube = 'youtube';
    case Wikipedia = 'wikipedia';
    case Mdn = 'mdn';
    case SymfonyDocs = 'symfony_docs';
    case PhpDocs = 'php_docs';
    case Github = 'github';
    case Gitlab = 'gitlab';
    case Stackoverflow = 'stackoverflow';
    case Reddit = 'reddit';
    case PdfViewer = 'pdf_viewer';
    case Unknown = 'unknown';
}
