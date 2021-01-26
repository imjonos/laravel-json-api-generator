<?php

namespace App\Exceptions;

use Nos\JsonApiGenerator\Exceptions\ApiException;

class UnprocessableEntityException extends ApiException
{
    /**
     * UnprocessableEntityException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->status = 422;
        $this->title = trans("jsonApi::exceptions.unprocessable_entity.title");
        $this->detail = trans("jsonApi::exceptions.unprocessable_entity.detail");
        parent::__construct($message, $code, $previous);
    }
}
