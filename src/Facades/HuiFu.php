<?php

namespace Aoeng\Laravel\Huifu\Facades;

use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * @method static
 */
class HuiFu extends LaravelFacade
{
    protected static function getFacadeAccessor(): string
    {
        return 'huifu';
    }
}