<?php

namespace App\Services;

use phpDocumentor\Reflection\Types\Object_;
use Sura\Libs\Auth;

class User
{
    public function new_news(): string
    {

        return '';
    }

    /**
     * @return array
     */
    public function info() : array
    {
        return Auth::index();
    }
}