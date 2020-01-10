<?php

declare(strict_types=1);

namespace Printing\Exceptions;

use InvalidArgumentException;
use Printing\Exceptions\Exception;

final class InvalidPrinterException extends InvalidArgumentException implements Exception
{
}