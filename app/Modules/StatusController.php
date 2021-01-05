<?php

namespace App\Modules;

use Sura\Libs\Tools;
use Sura\Libs\Validation;

class StatusController extends Module{

    /**
     * @deprecated
     */
    //#[Deprecated]
    public function index(){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){

            $Cache = cache_init(array('type' => 'file'));

            $user_id = $user_info['user_id'];
            $text = Validation::ajax_utf8(Validation::textFilter($_POST['text'], false, true));
            $public_id = intval($_POST['public_id']);

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $type = ($path['2']);

            //Если обновляем статус группы
            if($type == 'public'){

                $row = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$public_id}'");

                if(stripos($row['admin'], "u{$user_id}|") !== false){

                    $db->query("UPDATE `communities` SET status_text = '{$text}' WHERE id = '{$public_id}'");
//                    Cache::mozg_clear_cache_folder('groups');

                }

                //Если пользователь
            } else {

                $db->query("UPDATE `users` SET user_status = '{$text}' WHERE user_id = '{$user_id}'");

                //Чистим кеш
                $Cache->delete("users/{$user_id}/profile_{$user_id}");

            }

            echo stripslashes(stripslashes(Validation::textFilter(Validation::ajax_utf8($_POST['text']))));
        }

    }
}