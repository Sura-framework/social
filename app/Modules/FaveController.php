<?php

namespace App\Modules;

use Exception;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;

class FaveController extends Module{

    /**
     * Добвление юзера в закладки
     *
     * @return int
     * @throws \JsonException
     */
    public function add(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 70;
            $params['title'] = $lang['fave'].' | Sura';

            Tools::NoAjaxRedirect();

            $request = (Request::getRequest()->getGlobal());

            $fave_id = (int)$request['fave_id'];
            //Проверяем на факт существования юзера которого добавляем в закладки
            $row = $db->super_query("SELECT `user_id` FROM `users` WHERE user_id = '{$fave_id}'");
            if($row AND $user_id != $fave_id){

                //Проверям на факт существование этого юзера в закладках, если нету то пропускаем
                $db->query("SELECT `user_id` FROM `fave` WHERE user_id = '{$user_id}' AND fave_id = '{$fave_id}'");
                if(!$db->num_rows()){//TODO update
                    $db->query("INSERT INTO `fave` SET user_id = '{$user_id}', fave_id = '{$fave_id}', date = NOW()");
                    $db->query("UPDATE `users` SET user_fave_num = user_fave_num+1 WHERE user_id = '{$user_id}'");

                    $status = Status::OK;
                } else {
                    $status = Status::FOUND;
                }
            } else {
                $status = Status::NOT_FOUND;
            }
        } else {
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Удаление юзера из закладок
     *
     * @throws \JsonException
     */
    public function delete(): int
    {
        //TODO add to route
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 70;
            $params['title'] = $lang['fave'].' | Sura';

            $fave_id = (int)$request['fave_id'];

            //Проверям на факт существование этого юзера в закладках, если есть то пропускаем
            $row = $db->super_query("SELECT `user_id` FROM `fave` WHERE user_id = '{$user_id}' AND fave_id = '{$fave_id}'");
            if($row){
                $db->query("DELETE FROM `fave` WHERE user_id = '{$user_id}' AND fave_id = '{$fave_id}'");
                $db->query("UPDATE `users` SET user_fave_num = user_fave_num-1 WHERE user_id = '{$user_id}'");

                $status = Status::OK;
            } else{
                $status = Status::NOT_FOUND;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Вывод людей которые есть в закладках
     *
     * @return int
     */
    public function index(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
            $gcount = 70;
            $limit_page = ($page-1)*$gcount;

            $params['title'] = $lang['fave'].' | Sura';

            //Выводим кол-во людей в закладках
            $user = $db->super_query("SELECT user_fave_num FROM `users` WHERE user_id = '{$user_id}'");

            //Если кто-то есть в заклаках то выводим
            if($user['user_fave_num']){
//                $titles = array('человек', 'человека', 'человек');//fave
//                $user_speedbar = '<span id="fave_num">'.$user['user_fave_num'].'</span> '.Gramatic::declOfNum($user['user_fave_num'], $titles);

                //Загружаем поиск на странице
//                $tpl->load_template('fave_search.tpl');
//                $tpl->compile('content');

                //Выводи из базы
                $sql_ = $db->super_query("SELECT tb1.fave_id, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `fave` tb1, `users` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.fave_id = tb2.user_id ORDER by `date` LIMIT {$limit_page}, {$gcount}", 1);
                $config = Settings::load();
                foreach($sql_ as $key => $row){
                    if($row['user_photo']){
                        $sql_[$key]['ava'] = $config['home_url'].'uploads/users/'.$row['fave_id'].'/100_'.$row['user_photo'];
                    }
                    else{
                        $sql_[$key]['ava'] = '/images/100_no_ava.png';
                    }
                    $sql_[$key]['name'] = $row['user_search_pref'];
                    $sql_[$key]['user_id'] = $row['fave_id'];
                    $online = \App\Libs\Profile::Online($row['user_last_visit'], $row['user_logged_mobile']);
                    $sql_[$key]['online'] = $online;
                }
                $params['fave'] = $sql_;
//                navigation($gcount, $user['user_fave_num'], $config['home_url'].'fave/page/');
//                $sql_[$key]['ttt'] =
            } else {
//                $user_speedbar = $lang['no_infooo'];
//                msgbox('', $lang['no_fave'], 'info_2');

            }
            return view('fave.fave', $params);
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }
}