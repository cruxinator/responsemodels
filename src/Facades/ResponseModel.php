<?php

namespace Cruxinator\ResponseModel\Facades;

use Cruxinator\ResponseModel\Facades\Contracts\ResponseModelContract;
use Illuminate\Support\Facades\Facade;

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
