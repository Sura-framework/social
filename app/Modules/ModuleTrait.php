<?php

namespace App\Modules;

use Sura\Libs\Registry;

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

    /**
     * @return array|null
     */
	function user_info(): array|null
    {
	    return Registry::get('user_info');
    }

    /**
     * @return array|null
     */
    function logged(): array|null
    {
        return Registry::get('logged');
    }

    /**
     * @return array|string|\Sura\Libs\unknown|null
     */
    function db(){
        return Registry::get('db');
    }
}
