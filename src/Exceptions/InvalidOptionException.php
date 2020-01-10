<?php

declare(strict_types=1);

namespace Printing\Exceptions;

use InvalidArgumentException;
use Printing\Exceptions\Exception;

final class InvalidOptionException extends InvalidArgumentException implements Exception
{
}