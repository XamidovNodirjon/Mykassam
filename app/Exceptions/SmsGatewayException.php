<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when the SMS provider is unreachable or rejects a request.
 */
class SmsGatewayException extends RuntimeException {}
