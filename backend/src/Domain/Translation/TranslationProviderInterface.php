<?php

declare(strict_types=1);

namespace App\Domain\Translation;

use App\Domain\Speech\Transcript;

interface TranslationProviderInterface
{
    public function translate(Transcript $transcript, TranslationLanguage $target): Translation;
}
