<?php

namespace Nos\JsonApiGenerator\Facades;

use Illuminate\Support\Facades\Facade;

class JsonApiGenerator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'jsonapigenerator';
    }
}
