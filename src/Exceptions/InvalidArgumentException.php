<?php

declare(strict_types=1);

namespace Printing\Exceptions;

use Printing\Exceptions\Exception;

final class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
}