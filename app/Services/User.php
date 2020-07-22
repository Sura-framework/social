<?php

namespace App\Services;

use phpDocumentor\Reflection\Types\Object_;
use Sura\Libs\Auth;

class User
{
    public function new_news()
    {

        return '';
    }

    public function info()
    {
        $user = Auth::index();
        return $user;
    }
}