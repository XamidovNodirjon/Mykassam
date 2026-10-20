<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a request is valid but breaks a business rule (e.g. deleting a shop that has sales).
 * Rendered as an error toast for Inertia requests and as a 422 JSON response for API calls.
 */
class BusinessException extends RuntimeException {}
