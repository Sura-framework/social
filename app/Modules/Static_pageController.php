<?php

namespace App\Modules;

use Sura\Libs\Registry;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;

class Static_pageController extends Module{

    public function index($params){
        $tpl = Registry::get('tpl');

        $db = $this->db();
        $logged = Registry::get('logged');
        // $user_info = Registry::get('user_info');

        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            $alt_name = $db->safesql(Gramatic::totranslit($_GET['page']));
            $row = $db->super_query("SELECT title, text FROM `static` WHERE alt_name = '".$alt_name."'");
            if($row){
                $tpl->load_template('static.tpl');
                $tpl->set('{alt_name}', $alt_name);
                $tpl->set('{title}', stripslashes($row['title']));
                $tpl->set('{text}', stripslashes($row['text']));
                $tpl->compile('content');
            } else
                msgbox('', 'Страница не найдена.', 'info_2');

            $tpl->clear();
            $db->free();
        } else {
            $lang = langs::get_langs();
            $user_speedbar = $lang['no_infooo'];
            msgbox('', $lang['not_logged'], 'info');
        }

        Registry::set('tpl', $tpl);
    }
}