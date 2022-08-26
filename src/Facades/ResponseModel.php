<?php

namespace Cruxinator\ResponseModel\Facades;

use Illuminate\Support\Facades\Facade;
use Cruxinator\ResponseModel\Facades\Contracts\ResponseModelContract;

/**
 * @see \Cruxinator\ResponseModel\ResponseModel
 */
class ResponseModel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResponseModelContract::class;
    }
}
