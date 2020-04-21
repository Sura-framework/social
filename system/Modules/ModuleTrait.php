<?php  
namespace System\Modules;

use System\Libs\Registry;

/**
 *  Module
 *
 */
trait ModuleTrait
{

//    public $db = [];
//    public $logged = false;
//    public $user_info = array();

	function __construct()
	{
//		$this->db = Registry::get('db');
//		$this->logged = Registry::get('logged');
//		$this->user_info = Registry::get('user_info');
	}

	function user_info(){
	    return Registry::get('user_info');
    }

    public static function user_info2(){
        return Registry::get('user_info');
    }

    function logged(){
        return Registry::get('logged');
    }

    function db(){
        return Registry::get('db');
    }
}
