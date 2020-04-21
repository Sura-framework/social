<?php
/* 
	Appointment: Загрузка городов
	File: loadcity.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Modules;

use System\Libs\Auth;

/**
 * Class LogoutController
 * @package System\Modules
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