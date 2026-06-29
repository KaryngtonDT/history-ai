<?php

declare(strict_types=1);

namespace App\Domain\Translation;

interface TranslationProviderResolverInterface
{
    public function resolve(?TranslationProvider $provider = null): TranslationProviderInterface;
}
