<?php

namespace Nos\JsonApiGenerator\Exceptions;

use Exception;

abstract class ApiException extends Exception
{
    /**
     * HTTP status code
     * @var int
     */
    protected int $status;

    /**
     * Title
     * @var string
     */
    protected string $title;

    /**
     * Description
     * @var string
     */
    protected string $detail;

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        \Log::debug($this->title);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        return response()->json([
            'errors' => [
                [
                    "status" => $this->status,
                    "title" =>  $this->title,
                    "detail" => $this->detail
                ]
            ]
        ], $this->status);
    }
}
