<?php

namespace App\Modules;

use Sura\Libs\Auth;

/**
 * Class LogoutController
 * @package App\Modules
 */
class LogoutController extends Module{

    /**
     * Если делаем выход
     */
    public static function index()
    {
        Auth::logout();
    }
}