<?php

namespace Nos\JsonApiGenerator\Exceptions;

use Nos\JsonApiGenerator\Exceptions\ApiException;
use Throwable;

class BadRequestException extends ApiException
{
    /**
     * BadRequestException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->status = 400;
        $this->title = trans("jsonApi::exceptions.bad_request.title");
        $this->detail = trans("jsonApi::exceptions.bad_request.detail");
        parent::__construct($message, $code, $previous);
    }
}
