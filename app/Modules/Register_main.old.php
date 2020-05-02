<?php

namespace System\Modules;

use System\Libs\Cache;
use System\Libs\Registry;

class old extends Module{

    public static function index($tpl)
    {

        $db = Registry::get('db');
//        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');

        $tpl->load_template('reg.tpl');

//################## Загружаем Страны ##################//
        if (file_exists(__DIR__.'/../cache/system/all_country.php')) {
            $sql_country = Cache::system_cache('all_country');
        }else{
            $sql_country = $db->super_query("SELECT * FROM `".PREFIX."_country` ORDER by `name` ASC", true, "country", true);
            $sql_country = Cache::system_cache('all_country', $sql_country);
        }

        foreach($sql_country as $row_country)
            $all_country .= '<option value="'.$row_country['id'].'">'.stripslashes($row_country['name']).'</option>';

        $tpl->set('{country}', $all_country);

        $tpl->compile('content');

    }
}