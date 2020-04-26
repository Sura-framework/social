<?php
/* 
	Appointment: Опросы
	File: votes.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace App\Modules;

use Sura\Libs\Cache;
use Sura\Libs\Registry;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;

class VotesController extends Module{

    public function index($params){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxQuery();

        if($logged){

            $user_id = $user_info['user_id'];

            $vote_id = intval($_POST['vote_id']);
            $answer_id = intval($_POST['answer_id']);

            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$vote_id}'");

            if(!$row['cnt']){

                $db->query("INSERT INTO `votes_result` SET user_id = '{$user_id}', vote_id = '{$vote_id}', answer = '{$answer_id}'");

                $db->query("UPDATE `votes` SET answer_num = answer_num+1 WHERE id = '{$vote_id}'");

                Cache::mozg_mass_clear_cache_file("votes/vote_{$vote_id}|votes/vote_answer_cnt_{$vote_id}|votes/check{$user_id}_{$vote_id}");

                //Составляем новый ответ
                Cache::mozg_create_cache("votes/check{$user_id}_{$vote_id}", "a:1:{s:3:\"cnt\";s:1:\"1\";}");

                $row_vote = $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$vote_id}'", false, "votes/vote_{$vote_id}");

                $row_vote['title'] = stripslashes($row_vote['title']);

                $result .= "<div class=\"wall_vote_title\">{$row_vote['title']}</div>";

                $rowAnswers = stripslashes($row_vote['answers']);
                $arr_answe_list = explode('|', $rowAnswers);
                $max = $row_vote['answer_num'];

                $sql_answer = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$vote_id}' GROUP BY answer", 1, "votes/vote_answer_cnt_{$vote_id}");
                $answer = array();
                foreach($sql_answer as $row_answer){

                    $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                }

                for($ai = 0; $ai < sizeof($arr_answe_list); $ai++){

                    $num = $answer[$ai]['cnt'];

                    if(!$num ) $num = 0;
                    if($max != 0) $proc = (100 * $num) / $max;
                    else $proc = 0;
                    $proc = round($proc, 2);

                    $result .= "<div class=\"wall_vote_oneanswe cursor_default\">{$arr_answe_list[$ai]}<br /><div class=\"wall_vote_proc fl_l\"><div class=\"wall_vote_proc_bg\" style=\"width:".intval($proc)."%\"></div><div style=\"margin-top:-16px\">{$num}</div></div><div class=\"fl_l\" style=\"margin-top:-1px\"><b>{$proc}%</b></div></div><div class=\"clear\"></div>";

                }

                $titles = array('человек', 'человека', 'человек');//fave
                if($row_vote['answer_num']) $answer_num_text = Gramatic::declOfNum($row_vote['answer_num'], $titles);
                else $answer_num_text = 'человек';

                if($row_vote['answer_num'] <= 1) $answer_text2 = 'Проголосовал';
                else $answer_text2 = 'Проголосовало';

                $result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"clear\" style=\"margin-top:10px\"></div>";

                echo $result;

            }

        } else
            echo 'no_log';

        exit;

    }
}