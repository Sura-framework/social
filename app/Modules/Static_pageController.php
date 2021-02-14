<?php

namespace App\Modules;

use Exception;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;

class Static_pageController extends Module{

    /**
     * @return int
     */
    public function index(): int
    {
        $tpl = $params['tpl'];

        $db = $this->db();
        $logged = Registry::get('logged');
        // $user_info = Registry::get('user_info');
        $lang = $this->get_langs();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $alt_name = $db->safesql(Gramatic::totranslit($request['page']));
            $row = $db->super_query("SELECT title, text FROM `static` WHERE alt_name = '".$alt_name."'");
            if($row){
                $tpl->load_template('static.tpl');
                $tpl->set('{alt_name}', $alt_name);
                $tpl->set('{title}', stripslashes($row['title']));
                $tpl->set('{text}', stripslashes($row['text']));
                $tpl->compile('content');
            } else
                msg_box( 'Страница не найдена.', 'info_2');

            $tpl->clear();
            $db->free();
            return view('info.info', $params);
        }
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);


    }
}