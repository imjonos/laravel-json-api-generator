<?php

namespace Nos\JsonApiGenerator\Exceptions;

use Nos\JsonApiGenerator\Exceptions\ApiException;

class GoneException extends ApiException
{
    /**
     * ExpiredException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->status = 410;
        $this->title = trans("jsonApi::exceptions.expired.title");
        $this->detail = trans("jsonApi::exceptions.expired.detail");
        parent::__construct($message, $code, $previous);
    }
}
