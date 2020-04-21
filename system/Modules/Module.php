<?php  
namespace System\Modules;

use System\Classes\Db;
use System\Contracts\ModuleInterface;
use System\Libs\Langs;
use System\Libs\Registry;

/**
 *  Module
 *
 */
class Module implements ModuleInterface
{
	public function user_info(){
	    return Registry::get('user_info');
    }

    function logged(){
        return Registry::get('logged');
    }

    function db(){
        return Db::getDB();
    }

    function get_langs(){
        return langs::get_langs();
    }
}
