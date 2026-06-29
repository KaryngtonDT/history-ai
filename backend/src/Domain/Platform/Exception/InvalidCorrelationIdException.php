<?php

declare(strict_types=1);

namespace App\Domain\Platform\Exception;

use DomainException;

final class InvalidCorrelationIdException extends DomainException
{
}
