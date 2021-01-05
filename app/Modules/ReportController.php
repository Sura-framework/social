<?php

namespace App\Modules;

use Sura\Libs\Tools;

class ReportController extends Module{

    /**
     * Жалобы
     *
     * @param $params
     */
    public function index($params)
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $act = textFilter($_POST['act']);
            $mid = intval($_POST['id']);
            $type_report = intval($_POST['type_report']);
            $text_report = ajax_utf8(textFilter($_POST['text_report']));
            $arr_act = array('photo', 'video', 'note', 'wall');
            if($act == 'wall') $type_report = 6;
            if(in_array($act, $arr_act) AND $mid AND $type_report <= 6 AND $type_report > 0){
                $server_time = \Sura\Libs\Tools::time();

                $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `report` WHERE ruser_id = '".$user_info['user_id']."' AND mid = '".$mid."' AND act = '".$act."'");
                if(!$check['cnt'])
                    $db->query("INSERT INTO `report` SET act = '".$act."', type = '".$type_report."', text = '".$text_report."', mid = '".$mid."', date = '".$server_time."', ruser_id = '".$user_info['user_id']."'");
            }
        }
    }
}