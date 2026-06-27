<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Search;

use App\Domain\Search\Exception\InvalidSearchQueryException;
use App\Domain\Search\SearchQuery;
use App\Presentation\Http\Request\Search\Exception\InvalidSearchRequestException;

final readonly class SearchLibraryRequest
{
    public function __construct(
        public SearchQuery $searchQuery,
    ) {
    }

    public static function fromQueryParameter(mixed $query): self
    {
        if (!is_string($query)) {
            throw new InvalidSearchRequestException('Query parameter q is required.');
        }

        try {
            return new self(new SearchQuery($query));
        } catch (InvalidSearchQueryException) {
            throw new InvalidSearchRequestException('Invalid search query.');
        }
    }
}
