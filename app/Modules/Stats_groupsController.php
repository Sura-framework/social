<?php

namespace App\Modules;

use Exception;
use Sura\Libs\Langs;
use Sura\Libs\Request;
use Sura\Libs\Tools;

class Stats_groupsController extends Module{

    /**
     * Статистика сообщества
     *
     * @return int
     */
    public function index(): int
    {
        $tpl = $params['tpl'];

        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            //################### Выводим статистику ###################//
            $gid = intval($request['gid']);

            $month = intval($request['m']);
            if($month AND $month <= 0 OR $month > 12) $month = 2;

            $year = intval($request['y']);
            if($year AND $year < 2013 OR $year > 2020) $year = 2013;

            //Выводим админа сообщества
            $owner = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$gid}'");

            //ПРоверка на админа
            if(strpos($owner['admin'], "u{$user_info['user_id']}|") !== false){

                $server_time = \Sura\Time\Date::time();

                if($month AND $year){

                    $stat_date = date($year.'-'.$month, $server_time);
                    $r_month = date($month, $server_time);

                } else {

                    $stat_date = date('Y-m', $server_time);
                    $r_month = date('m', $server_time);

                    $month = date('n', $server_time);

                }

                $stat_date = strtotime($stat_date);

                $t_date = Langs::lang_date('F', $stat_date);

                //Составляем массив для вывода за этот месяц
                $sql_ = $db->super_query("SELECT cnt, date, hits, new_users, exit_users FROM `communities_stats` WHERE gid = '{$gid}' AND date_x = '{$stat_date}' ORDER by `date` ", 1);

                if($sql_){

                    foreach($sql_ as $row){

                        $dat_exp = date('j', $row['date']);

                        $arr_r_unik[$dat_exp] = $row['cnt'];

                        $arr_r_hits[$dat_exp] = $row['hits'];

                        $arr_r_new_users[$dat_exp] = $row['new_users'];

                        $arr_r_exit_users[$dat_exp] = $row['exit_users'];

                    }

                }

                if($r_month == '01' OR $r_month == '03' OR $r_month == '05' OR $r_month == '07' OR $r_month == '08' OR $r_month == '10' OR $r_month == '12' OR $r_month == '1' OR $r_month == '3' OR $r_month == '5' OR $r_month == '7' OR $r_month == '8') $limit_day = 31;
                elseif($r_month == '02') $limit_day = 28;
                else $limit_day = 30;

                for($i = 1; $i <= $limit_day; $i++){

                    if(!$arr_r_unik[$i]) $arr_r_unik[$i] = 0;
                    $r_unik .= '['.$i.', '.$arr_r_unik[$i].'],';

                    if(!$arr_r_hits[$i]) $arr_r_hits[$i] = 0;
                    $r_hits .= '['.$i.', '.$arr_r_hits[$i].'],';

                    if(!$arr_r_new_users[$i]) $arr_r_new_users[$i] = 0;
                    $r_new_users .= '['.$i.', '.$arr_r_new_users[$i].'],';

                    if(!$arr_r_exit_users[$i]) $arr_r_exit_users[$i] = 0;
                    $r_exit_users .= '['.$i.', '.$arr_r_exit_users[$i].'],';

                }

                //Выводим максимальное кол-во юзеров за этот месяц
                $row_max = $db->super_query("SELECT cnt FROM `communities_stats` WHERE gid = '{$gid}' AND date_x = '{$stat_date}' ORDER by `cnt` DESC");

                $rNum = round($row_max['cnt'] / 15);
                if($rNum < 1) $rNum = 1;

//                $tickSize = $rNum;

                //Выводим максимальное кол-во просмотров за этот месяц
                $row_max_hits = $db->super_query("SELECT hits FROM `communities_stats` WHERE gid = '{$gid}' AND date_x = '{$stat_date}' ORDER by `hits` DESC");

                $rNum_hits = round($row_max_hits['hits'] / 15);
                if($rNum_hits < 1) $rNum_hits = 1;

                $tickSize_hits = $rNum_hits;
                $tickSize = $rNum;

                //Выводим максимальное кол-во новых юзеров за этот месяц
                $row_max_new_users = $db->super_query("SELECT new_users FROM `communities_stats` WHERE gid = '{$gid}' AND date_x = '{$stat_date}' ORDER by `new_users` DESC");

                $rNum_new_users = round($row_max_new_users['new_users'] / 15);
                if($rNum_new_users < 1) $rNum_new_users = 1;

                $tickSize_new_users = $rNum_new_users;

                //Выводим максимальное кол-во вышедних юзеров за этот месяц
                $row_max_exit_users = $db->super_query("SELECT exit_users FROM `communities_stats` WHERE gid = '{$gid}' AND date_x = '{$stat_date}' ORDER by `exit_users` DESC");

                $rNum_exit_users = round($row_max_exit_users['exit_users'] / 15);
                if($rNum_exit_users < 1) $rNum_exit_users = 1;

                $tickSize_exit_users = $rNum_exit_users;

                //Загружаем шаблон
                $tpl->load_template('public_stats/head.tpl');

                $tpl->set('{r_unik}', $r_unik);
                $tpl->set('{r_hits}', $r_hits);
                $tpl->set('{r_new_users}', $r_new_users);
                $tpl->set('{r_exit_users}', $r_exit_users);
                $tpl->set('{t-date}', $t_date);
                $tpl->set('{tickSize}', $tickSize);
                $tpl->set('{tickSize_hits}', $tickSize_hits);
                $tpl->set('{tickSize_new_users}', $tickSize_new_users);
                $tpl->set('{tickSize_exit_users}', $tickSize_exit_users);
                $tpl->set('{gid}', $gid);

                $tpl->set('{months}', installationSelected($month, '<option value="1">Январь</option><option value="2">Февраль</option><option value="3">Март</option><option value="4">Апрель</option><option value="5">Май</option><option value="6">Июнь</option><option value="7">Июль</option><option value="8">Август</option><option value="9">Сентябрь</option><option value="10">Октябрь</option><option value="11">Ноябрь</option><option value="12">Декабрь</option>'));
                $tpl->set('{year}', installationSelected($year, '<option value="2013">2013</option><option value="2014">2014</option><option value="2015">2015</option><option value="2016">2016</option><option value="2017">2017</option><option value="2018">2018</option><option value="2019">2019</option><option value="2020">2020</option>'));

                $tpl->compile('content');

            } else

                msg_box('Ошибка доступа!', 'info');


            $tpl->clear();
            $db->free();
            return view('info.info', $params);
        }
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);

    }
}