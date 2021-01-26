<?php

namespace Nos\JsonApiGenerator\Exceptions;

use Nos\JsonApiGenerator\Exceptions\ApiException;
use Throwable;

class  ForbiddenException extends ApiException
{
    /**
     * BadRequestException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->status = 403;
        $this->title = trans("jsonApi::exceptions.forbidden.title");
        $this->detail = trans("jsonApi::exceptions.forbidden.detail");
        parent::__construct($message, $code, $previous);
    }
}
