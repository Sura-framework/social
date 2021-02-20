<?php

namespace App\Modules;

use Sura\Libs\Gramatic;
use Sura\Libs\Request;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Validation;

class SupportController extends Module{

    /**
     * Страница создание нового вопроса
     *
     * @return int
     */
    public function new(): int
    {
        $lang = $this->get_langs();
//        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $params = array();
        if($logged){
            $request = (Request::getRequest()->getGlobal());
            $params['group'] = $user_info['user_group'];
            if($user_info['user_group'] <= 4){
                $params['support_title'] = 'Вопросы от пользователей';
            } else {
                $params['support_title'] = 'Мои вопросы';
            }
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['support_title'].' | Sura';
//            if($request['page'] > 0) $page = intval($request['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//
//            $mobile_speedbar = 'Новый вопрос';

//            $tpl->load_template('support/new.tpl');
//            $tpl->set('{uid}', $user_id);
            $params['uid'] = $user_id;
//            $tpl->compile('content');

            return view('support.new', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Отправка нового вопроса
     *
     * @return int
     * @throws \JsonException
     */
    public function send(): int
    {
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $params['title'] = $lang['support_title'].' | Sura';
            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;

              $title = Validation::ajax_utf8(Validation::textFilter($request['title']));
            $question = Validation::ajax_utf8(Validation::textFilter($request['question']));
            $server_time = \Sura\Time\Date::time();
            $limitTime = $server_time-3600;
            $rowLast = $db->super_query("SELECT COUNT(*) AS cnt FROM `support` WHERE сdate > '{$limitTime}'");
            if(!$rowLast['cnt'] AND isset($title) AND !empty($title) AND isset($question) AND !empty($question) AND $user_info['user_group'] != 4){
                $question = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<!--link:$1--><a href="$1" target="_blank">$1</a><!--/link-->', $question);
                $db->query("INSERT INTO `support` SET title = '{$title}', question = '{$question}', suser_id = '{$user_id}', sfor_user_id = '{$user_id}', sdate = '{$server_time}', сdate = '{$server_time}'");
                $dbid = $db->insert_id();
                $row = $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");
//                $tpl->load_template('support/show.tpl');
//                $tpl->set('{title}', stripslashes($title));
//                $tpl->set('{question}', stripslashes($question));
//                $tpl->set('{qid}', $dbid);

                $date = \Sura\Time\Date::megaDate($server_time);
//                $tpl->set('{date}', $date);
//                $tpl->set('{status}', 'Вопрос ожидает обработки.');
//                $tpl->set('{name}', $row['user_search_pref']);
//                $tpl->set('{uid}', $user_id);
                if($row['user_photo'])
                {
//                    $tpl->set('{ava}', '/uploads/users/'.$user_id.'/50_'.$row['user_photo']);
                }
                else
                {
//                    $tpl->set('{ava}', '/images/no_ava_50.png');
                }
//                $tpl->set('{answers}', '');
//                $tpl->compile('content');
//

//                echo 'r|x'.$dbid;
                $status = Status::OK;
            } else {
                $status = Status::LIMIT;
            }
        } else {
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Удаление вопроса
     *
     * @return int
     * @throws \JsonException
     */
    public function delet(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $params['title'] = $lang['support_title'].' | Sura';
            if($request['page'] > 0) $page = intval($request['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $qid = intval($request['qid']);
            $row = $db->super_query("SELECT suser_id FROM `support` WHERE id = '{$qid}'");
            if($row['suser_id'] == $user_id OR $user_info['user_group'] == 4){
                $db->query("DELETE FROM `support` WHERE id = '{$qid}'");
                $db->query("DELETE FROM `support_answers` WHERE qid = '{$qid}'");

                $status = Status::OK;
            }else{
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
     *  Удаление Ответа
     *
     * @return int
     * @throws \JsonException
     */
    public function delet_answer(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $params['title'] = $lang['support_title'].' | Sura';
            if($request['page'] > 0) $page = intval($request['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $id = intval($request['id']);
            $row = $db->super_query("SELECT auser_id FROM `support_answers` WHERE id = '{$id}'");
            if($row['auser_id'] == $user_id OR $user_info['user_group'] == 4)
            {
                $db->query("DELETE FROM `support_answers` WHERE id = '{$id}'");


            }else{
                $status = Status::NOT_FOUND;
                return _e_json(array(
                    'status' => $status,
                ) );
            }

            $status = Status::OK;
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Закрытие вопроса
     *
     * @return int
     * @throws \JsonException
     */
    public function close(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

//            $user_id = $user_info['user_id'];
            $params['title'] = $lang['support_title'].' | Sura';
            if($request['page'] > 0) $page = intval($request['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $qid = intval($request['qid']);
            if($user_info['user_group'] == 4){
                $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `support` WHERE id = '{$qid}'");
                if($row['cnt'])
                {
                    $db->query("UPDATE `support` SET sfor_user_id = 0 WHERE id = '{$qid}'");
                    $status = Status::OK;
                }else{
                    $status = Status::NOT_FOUND;
                }
            }else{
                $status = Status::BAD_RIGHTS;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Отправка ответа
     *
     * @return int
     */
    public function answer(): int
    {
        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $params['title'] = $lang['support_title'].' | Sura';
            if($request['page'] > 0) $page = intval($request['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $qid = intval($request['qid']);
            $answer = Validation::ajax_utf8(Validation::textFilter($request['answer']));
            $check = $db->super_query("SELECT suser_id FROM `support` WHERE id = '{$qid}'");
            if($check['suser_id'] == $user_id OR $user_info['user_group'] == 4 AND isset($answer) AND !empty($answer)){
                if($user_info['user_group'] == 4){
                    $auser_id = 0;
                    $db->query("UPDATE `users` SET user_support = user_support+1 WHERE user_id = '{$check['suser_id']}'");
                } else
                    $auser_id = $user_id;

                $answer = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<!--link:$1--><a href="$1" target="_blank">$1</a><!--/link-->', $answer);

                $server_time = \Sura\Time\Date::time();

                $db->query("INSERT INTO `support_answers` SET qid = '{$qid}', auser_id = '{$auser_id}', adate = '{$server_time}', answer = '{$answer}'");
                $db->query("UPDATE `support` SET sfor_user_id = '{$auser_id}', sdate = '{$server_time}' WHERE id = '{$qid}'");

                $row = $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");

                $tpl->load_template('support/answer.tpl');
                if(!$auser_id){
                    $tpl->set('{name}', 'Агент поддержки');
                    $tpl->set('{ava}', '/images/support.png');
                    $tpl->set_block("'\\[no-agent\\](.*?)\\[/no-agent\\]'si","");
                } else {
                    $tpl->set('{name}', $row['user_search_pref']);
                    if($row['user_photo'])
                        $tpl->set('{ava}', '/uploads/users/'.$user_id.'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');

                    $tpl->set('[no-agent]', '');
                    $tpl->set('[/no-agent]', '');
                }

                if($auser_id == $user_id OR $user_info['user_group'] == 4){
                    $tpl->set('[owner]', '');
                    $tpl->set('[/owner]', '');
                } else
                    $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                $tpl->set('{uid}', $user_id);
                $tpl->set('{answer}', stripslashes($answer));

                $date = \Sura\Time\Date::megaDate($server_time);
                $tpl->set('{date}', $date);
                $tpl->compile('content');
                return view('info.info', $params);
            }

        }
        return view('info.info', $params);
    }

    /**
     * Просмотр вопроса
     *
     * @return int
     */
    public function show(): int
    {
        $params = array();
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $server = Request::getRequest()->server;

            $path = explode('/', $server['REQUEST_URI']);
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $params['title'] = $lang['support_title'].' | Sura';
            if($request['page'] > 0) {
                $page = (int)$request['page'];
            } else {
                $page = 1;
            }
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $qid = $path[3];

            $mobile_speedbar = 'Просмотр вопроса';

            if($user_info['user_group'] == 4)
                $sql_where = "";
            else
                $sql_where = "AND tb1.suser_id = '{$user_id}'";

            $row = $db->super_query("SELECT tb1.id, title, question, sdate, sfor_user_id, suser_id, tb2.user_search_pref, user_photo FROM `support` tb1, `users` tb2 WHERE tb1.id = '{$qid}' AND tb1.suser_id = tb2.user_id {$sql_where}");
            if($row){
                //Выводим ответы
                $sql_answer = $db->super_query("SELECT id, adate, answer, auser_id FROM `support_answers` WHERE qid = '{$qid}' ORDER by `adate` ASC LIMIT 0, 100", 1);

//                $tpl->load_template('support/answer.tpl');
                foreach($sql_answer as $row_answer){
                    if(!$row_answer['auser_id']){
//                        $tpl->set('{name}', 'Агент поддержки');
//                        $tpl->set('{ava}', '/images/support.png');
//                        $tpl->set_block("'\\[no-agent\\](.*?)\\[/no-agent\\]'si","");
                    } else {
//                        $tpl->set('{name}', $row['user_search_pref']);
                        if($row['user_photo']) {
//                            $tpl->set('{ava}', '/uploads/users/'.$row['suser_id'].'/50_'.$row['user_photo']);
                        }
                        else{
//                            $tpl->set('{ava}', '/images/no_ava_50.png');
                        }

//                        $tpl->set('[no-agent]', '');
//                        $tpl->set('[/no-agent]', '');
                    }

                    if($row_answer['auser_id'] == $user_id OR $user_info['user_group'] == 4){
//                        $tpl->set('[owner]', '');
//                        $tpl->set('[/owner]', '');
                    } else
                    {
//                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                    }

//                    $tpl->set('{id}', $row_answer['id']);
//                    $tpl->set('{uid}', $user_id);
//                    $tpl->set('{answer}', stripslashes($row_answer['answer']));
                    $date = \Sura\Time\Date::megaDate(strtotime($row_answer['adate']));
//                    $tpl->set('{date}', $date);
//                    $tpl->compile('answers');
                }

//                $tpl->load_template('support/show.tpl');
//                $tpl->set('{title}', stripslashes($row['title']));
//                $tpl->set('{question}', stripslashes($row['question']));
//                $tpl->set('{qid}', $qid);

                $date = \Sura\Time\Date::megaDate(strtotime($row['sdate']));
//                $tpl->set('{date}', $date);

                if($row['sfor_user_id'] == $row['suser_id']) {
//                    $tpl->set('{status}', 'Вопрос ожидает обработки.');
                }
                else {
//                    $tpl->set('{status}', 'Есть ответ.');
                }

//                $tpl->set('{name}', $row['user_search_pref']);

                if($user_info['user_group'] == 4) {
//                    $tpl->set('{uid}', $row['suser_id']);
                }
                else {
//                    $tpl->set('{uid}', $user_id);
                }

                if($row['user_photo']) {
//                    $tpl->set('{ava}', '/uploads/users/' . $row['suser_id'] . '/50_' . $row['user_photo']);
                }
                else {
//                    $tpl->set('{ava}', '/images/no_ava_50.png');
                }

//                $tpl->set('{answers}', $tpl->result['answers']);
//                $tpl->compile('content');
            } else {
//                $speedbar = $lang['error'];
//                msg_box( $lang['support_no_quest'], 'info');
            }

            return view('support.show', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Просмотр всех вопросов
     *
     * @return int
     */
    public function index(): int
    {
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $db = $this->db();
        
        if($logged){
            $user_id = $user_info['user_id'];
//            $params['title'] = $lang['help'].' | Sura';

            $path = explode('/', $_SERVER['REQUEST_URI']);

            if(is_int($path['2']) ) {
                $page = $path['2'];
            }
            else {
                $page = 1;
            }

            $g_count = 20;
            $limit_page = ($page-1)*$g_count;
            if($user_info['user_support'] AND $user_info['user_group'] != 4) {
                $db->query("UPDATE `users` SET user_support = 0 WHERE user_id = '{$user_id}'");
            }

            $params['group'] = $user_info['user_group'];
            if($user_info['user_group'] <= 4){
                $sql_where = "ORDER by `sdate` DESC";
                $sql_where_cnt = "";
                $params['support_title'] = 'Вопросы от пользователей';
            } else {
                $sql_where = "AND tb1.suser_id = '{$user_id}' ORDER by `sdate` DESC";
                $sql_where_cnt = "WHERE suser_id = '{$user_id}'";
                $params['support_title'] = 'Мои вопросы';
            }

            $sql_ = $db->super_query("SELECT tb1.id, title, suser_id, sfor_user_id, sdate, tb2.user_photo, user_search_pref FROM `support` tb1, `users` tb2 WHERE tb1.suser_id = tb2.user_id {$sql_where} LIMIT {$limit_page}, {$g_count}", 1);

            if($sql_) {
                $count = $db->super_query("SELECT COUNT(*) AS cnt FROM `support` {$sql_where_cnt}");
            }

            if(isset($sql_) AND $sql_ == true){
                $titles = array('вопрос', 'вопроса', 'вопросов');//questions
                if($user_info['user_group'] <= 4){
                    $params['cnt'] = $count['cnt'].' '.Gramatic::declOfNum($count['cnt'], $titles);
                }
                else{
                    $params['cnt'] = 'Вы задали '.$count['cnt'].' '.Gramatic::declOfNum($count['cnt'], $titles);
                }
//                $tpl->load_template('support/question.tpl');

                foreach($sql_ as $key => $row){
                    $sql_[$key]['title'] = stripslashes($row['title']);
                    $date = \Sura\Time\Date::megaDate($row['sdate']);
                    $sql_[$key]['date'] = $date;
                    if($row['sfor_user_id'] == $row['suser_id'] OR $user_info['user_group'] == 4){
                        if($row['sfor_user_id'] == $row['suser_id']){
                            $sql_[$key]['status'] = 'Вопрос ожидает обработки.';
                        }
                        else{
                            $sql_[$key]['status'] = 'Есть ответ.';
                        }
                        $sql_[$key]['name'] = $row['user_search_pref'];
                        $sql_[$key]['answer'] = '';
                        if($row['user_photo']){
                            $sql_[$key]['ava'] = '/uploads/users/'.$row['suser_id'].'/50_'.$row['user_photo'];
                        }
                        else{
                            $sql_[$key]['ava'] = '/images/no_ava_50.png';
                        }
                    } else {
                        $sql_[$key]['name'] = 'Агент поддержки';
                        $sql_[$key]['status'] = 'Есть ответ.';
                        $sql_[$key]['ava'] = '/images/support.png';
                        $sql_[$key]['answer'] = 'ответил';
                    }
                    $sql_[$key]['qid'] = $row['id'];
                }
                $params['questions'] = $sql_;
                $params['navigation'] = '';
            } else{
                if($user_info['user_group'] == 4){
                    $params['alert_info'] = '';
                }
                else{
                    $params['alert_info'] = '';
                }
            }

            return view('support.support', $params);
        }
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);


//        $tpl->load_template('support/head.tpl');


//        $tpl->set('{content_info}', $tpl->result['alert_info']);



    }
}