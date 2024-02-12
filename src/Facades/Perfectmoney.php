<?php

namespace Package\Perfectmoney\Facades;

use illuminate\Support\Facades\Facade;

class Perfectmoney extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'perfectmoney';
    }
}