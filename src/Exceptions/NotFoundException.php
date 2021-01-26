<?php

namespace Nos\JsonApiGenerator\Exceptions;

use Nos\JsonApiGenerator\Exceptions\ApiException;

class NotFoundException extends ApiException
{
    /**
     * NotFoundException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->status = 404;
        $this->title = trans("jsonApi::exceptions.not_found.title");
        $this->detail = trans("jsonApi::exceptions.not_found.detail");
        parent::__construct($message, $code, $previous);
    }
}
