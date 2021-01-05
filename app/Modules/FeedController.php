<?php

namespace App\Modules;

use App\Libs\Wall;
use App\Models\News;
use App\Models\Stories;
use Exception;
use Sura\Libs\Request;


/**
 * Новости
 *
 * Class FeedController
 */
class FeedController extends Module
{

    private int $limit = 20;

    /**
     * Показ предыдущих записей
     *
     * @param $params
     * @return string
     * @throws Exception
     */
    public function next($params): string
    {
//        $lang = $this->get_langs();
        $logged = $this->logged();
        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];

        $request = (Request::getRequest()->getGlobal());

//        Tools::NoAjaxRedirect();

        //Если вызваны предыдущие новости
        if($logged && $request['page_cnt']) {

//                $limit = 20;
            $page = (int)$request['page_cnt'] *$this->limit;

            $sql_ = News::load_news($user_id, $page);
/*
            foreach($sql_ as $key => $row){

//                $query[$key]['action_type'] = $row['action_type'];

//                Выводим данные о том кто инсцинировал действие
//                    if($row['user_sex'] == 2){
//                        $sex_text = array(
//                            '1' => 'добавила',
//                            '2' => 'ответила',
//                            '3' => 'оценила',
//                            '4' => 'прокомментировала',
//                        );
//                    } else {
//                        $sex_text = array(
//                            '1' => 'добавил',
//                            '2' => 'ответил',
//                            '3' => 'оценил',
//                            '4' => 'прокомментировал',
//                        );
//                    }

                $sql_[$key]['author_id'] = $row['ac_user_id'];

                if (!isset($row['user_logged_mobile']))
                    $row['user_logged_mobile'] = '0';//bug: undefined

                if (!isset($row['user_last_visit']))
                    $row['user_last_visit'] = null;

                $sql_[$key]['online'] = Online($row['user_last_visit'], $row['user_logged_mobile']);

                //Выводим данные о действии
                $date = megaDate($row['action_time']);
                $sql_[$key]['date'] = megaDate($row['action_time']);
//                    $row['action_time'] = megaDate($row['action_time']);
                $sql_[$key]['date'] = $date;
//                    $tpl->set('{comment}', stripslashes($row['action_text']));
//                    $params['comment'] = stripslashes($row['action_text']);
                $sql_[$key]['action_text'] = stripslashes($row['action_text']);
                $params['news_id'] = $row['ac_id'];
                $params['action_type_updates'] = '';
                $params['action_type'] = '';
                $query[$key]['action_type'] = $row['action_type'];

//                    $expFriensList = explode('||', $row['action_text']);
//                    $action_cnt = 0;

//                    $comment = '';
/*
                //public
                if($row['action_type'] == 11){
                    $rowInfoUser = News::row_type11($row['ac_user_id'], 2);
                    $row['user_search_pref'] = $rowInfoUser['title'];

                    $sql_[$key]['author'] = $rowInfoUser['title'];
                    $sql_[$key]['link'] = 'public';

                    if($rowInfoUser['photo']){
                        $sql_[$key]['ava'] = '/uploads/groups/'.$row['ac_user_id'].'/50_'.$rowInfoUser['photo'];
                    }else{
                        $sql_[$key]['ava'] = '/images/no_ava_50.png';
                    }

                    //Выводим кол-во комментов, мне нравится, и список юзеров кто поставил лайки к записи если это не страница "ответов"
                    $rec_info_groups = News::rec_info_groups($row['obj_id']);

                    //КНопка Показать полностью..
                    $expBR = explode('<br />', $row['action_text']);
                    $textLength = count($expBR);
                    $strTXT = strlen($row['action_text']);
                    if($textLength > 9 OR $strTXT > 600)
                        $row['action_text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row['obj_id'].'">'.$row['action_text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row['obj_id'].' , this.id)" id="hide_wall_rec_lnk'.$row['obj_id'].'">Показать полностью..</div>';

                    //Прикрипленные файлы
                    if($rec_info_groups['attach']){
                        $attach_arr = explode('||', $rec_info_groups['attach']);
                        $cnt_attach = 1;
                        $cnt_attach_link = 1;
                        $jid = 0;
                        $attach_result = '';
                        //$attach_result .= '<div class=""></div>';//div.clear
                        $config = $params['config'];
                        $row_wall = null;
                        foreach($attach_arr as $attach_file){
                            $attach_type = explode('|', $attach_file);

                            if($rec_info_groups['public'])
                                $row['ac_user_id'] = $rec_info_groups['tell_uid'];

                            //Фото со стены сообщества
                            if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$row['ac_user_id']}/photos/c_{$attach_type[1]}")){
                                if($cnt_attach < 2)
                                    $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row['ac_user_id']}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['ac_user_id']}/photos/{$attach_type[1]}\"  alt=/"/" /></div>";
                                else
                                    $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['ac_user_id']}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row['ac_user_id']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=/"/"/>";

                                $cnt_attach++;

                                //Фото со стены юзера
                            }
                            elseif($attach_type[0] == 'photo_u'){
                                if($rec_info_groups['tell_uid']) $attauthor_user_id = $rec_info_groups['tell_uid'];
                                else $attauthor_user_id = $row['ac_user_id'];

                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\"  alt=\"\" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                    $cnt_attach++;
                                } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\"  alt=\"\" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row['obj_id']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";
                                    $cnt_attach++;
                                }
                                $resLinkTitle = '';
                            }
                            //Видео
                            elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                $for_cnt_attach_video = explode('video|', $rec_info_groups['attach']);
                                $cnt_attach_video = count($for_cnt_attach_video)-1;

                                if($cnt_attach_video == 1 AND preg_match('/(photo|photo_u)/i', $rec_info_groups['attach']) == false){

                                    $video_id = intval($attach_type[2]);

                                    $row_video = News::video_info($video_id);
                                    $row_video['title'] = stripslashes($row_video['title']);
                                    $row_video['video'] = stripslashes($row_video['video']);
                                    $row_video['video'] = strtr($row_video['video'], array('width="770"' => 'width="390"', 'height="420"' => 'height="310"'));

                                    $attach_result .= "<div class=\"cursor_pointer \" id=\"no_video_frame{$video_id}\" onClick=\"$('#'+this.id).hide();$('#video_frame{$video_id}').show();\">
                                        <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px\" width=\"390\" height=\"310\"  alt=/"/"/></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div><div class=\"video_inline_vititle\"></div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><b>{$row_video['title']}</b></a>";

                                } else {

                                    $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\"  alt=\"\" /></a></div>";

                                }

                                $resLinkTitle = '';

                            }
                            //Музыка
                            elseif($attach_type[0] == 'audio'){
                                $audioId = intval($attach_type[1]);
                                $audioInfo = News::audio_info($audioId);
                                if($audioInfo){
                                    $jid++;
                                    $attach_result .= '<div class="audioForSize'.$row['obj_id'].' player_mini_mbar_wall_all" id="audioForSize"><div class="audio_onetrack audio_wall_onemus"><div class="audio_playic cursor_pointer fl_l" onClick="music.newStartPlay(\''.$jid.'\', '.$row['obj_id'].')" id="icPlay_'.$row['obj_id'].$jid.'"></div><div id="music_'.$row['obj_id'].$jid.'" data="'.$audioInfo['url'].'" class="fl_l" style="margin-top:-1px"><a href="/?go=search&type=5&query='.$audioInfo['artist'].'&n=1" onClick="Page.Go(this.href); return false"><b>'.stripslashes($audioInfo['artist']).'</b></a> &ndash; '.stripslashes($audioInfo['title']).'</div><div id="play_time'.$row['obj_id'].$jid.'" class="color777 fl_r no_display" style="margin-top:2px;margin-right:5px">00:00</div><div class="player_mini_mbar fl_l no_display player_mini_mbar_wall_all" id="ppbarPro'.$row['obj_id'].$jid.'"></div></div></div>';
                                }

                                $resLinkTitle = '';

                            }
                            //Смайлик
                            elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';

                                $resLinkTitle = '';

                            }
                            //Если ссылка
                            elseif($attach_type['0'] == 'link' AND preg_match('/https:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1 AND stripos(str_replace('https://www.', 'https://', $attach_type[1]), $config['home_url']) === false){
//                                    $count_num = count($attach_type);
                                $domain_url_name = explode('/', $attach_type['1']);
                                $rdomain_url_name = str_replace('https://', '', $domain_url_name[2]);

                                $attach_type['3'] = stripslashes($attach_type['3']);
                                $attach_type['3'] = substr($attach_type['3'], 0, 200);

                                $attach_type['2'] = stripslashes($attach_type[2]);
                                $str_title = substr($attach_type['2'], 0, 55);

                                if(stripos($attach_type['4'], '/uploads/attach/') === false){
                                    $attach_type['4'] = '/images/no_ava_groups_100.gif';
                                    $no_img = false;
                                } else
                                    $no_img = true;

                                if(!$attach_type['3']) $attach_type['3'] = '';

                                if($no_img AND $attach_type['2']){
                                    if($rec_info_groups['tell_comm']) {
                                        $no_border_link = 'border:0';
                                    }else{
                                        $no_border_link = '';
                                    }

                                    $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type['1'].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class=""></div><div class="wall_show_block_link" style="'.$no_border_link.'"><a href="/away.php?url='.$attach_type['1'].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type['4'].'"  alt=\"\" /></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type['1'].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type['3'].'</div></div></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                } else if($attach_type['1'] AND $attach_type['2']){
                                    $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url=' .$attach_type['1'].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class=""></div>';

                                    $resLinkTitle = $attach_type['2'];
                                    $resLinkUrl = $attach_type['1'];
                                }

                                $cnt_attach_link++;

                            }
                            //Если документ
                            elseif($attach_type['0'] == 'doc'){

                                $doc_id = intval($attach_type['1']);

                                $row_doc = News::doc_info($doc_id);

                                if($row_doc){

                                    $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class=""><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row['obj_id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row['obj_id'].'">'.$row_doc['dname'].'</a></div></div></div><div class=""></div>';

                                    $cnt_attach++;
                                }

                            }
                            //Если опрос
                            elseif($attach_type['0'] == 'vote'){

                                $vote_id = intval($attach_type['1']);

                                $row_vote = News::video_info($vote_id);

                                if($vote_id){

                                    $checkMyVote = News::vote_info_check($vote_id, $user_id);

                                    $row_vote['title'] = stripslashes($row_vote['title']);

                                    if(!$row_wall['text'])
                                        $row_wall['text'] = $row_vote['title'];

                                    $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                    $max = $row_vote['answer_num'];

                                    $sql_answer = News::vote_info_answer($vote_id);
                                    $answer = array();
                                    foreach($sql_answer as $row_answer){

                                        $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                                    }

                                    $attach_result .= "<div class=\"\" style=\"height:10px\"></div><div id=\"result_vote_block{$vote_id}\"><div class=\"wall_vote_title\">{$row_vote['title']}</div>";

                                    for($ai = 0; $ai < sizeof($arr_answe_list); $ai++){

                                        if(!$checkMyVote['cnt']){

                                            $attach_result .= "<div class=\"wall_vote_oneanswe\" onClick=\"Votes.Send({$ai}, {$vote_id})\" id=\"wall_vote_oneanswe{$ai}\"><input type=\"radio\" name=\"answer\" /><span id=\"answer_load{$ai}\">{$arr_answe_list[$ai]}</span></div>";

                                        } else {

                                            $num = $answer[$ai]['cnt'];

                                            if(!$num ) $num = 0;
                                            if($max != 0) $proc = (100 * $num) / $max;
                                            else $proc = 0;
                                            $proc = round($proc, 2);

                                            $attach_result .= "<div class=\"wall_vote_oneanswe cursor_default\">
                                                    {$arr_answe_list[$ai]}<br />
                                                    <div class=\"wall_vote_proc fl_l\"><div class=\"wall_vote_proc_bg\" style=\"width:".intval($proc)."%\"></div><div style=\"margin-top:-16px\">{$num}</div></div>
                                                    <div class=\"fl_l\" style=\"margin-top:-1px\"><b>{$proc}%</b></div>
                                                    </div><div class=\"\"></div>";

                                        }

                                    }
                                    $titles = array('человек', 'человека', 'человек');//fave
                                    if($row_vote['answer_num']) $answer_num_text = Gramatic::declOfNum($row_vote['answer_num'], $titles);
                                    else $answer_num_text = 'человек';

                                    if($row_vote['answer_num'] <= 1) $answer_text2 = 'Проголосовал';
                                    else $answer_text2 = 'Проголосовало';

                                    $attach_result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"\" style=\"margin-top:10px\"></div></div>";

                                }

                            }
                            else
                                $attach_result .= '';
                        }

                        if($resLinkTitle AND $row['action_text'] == $resLinkUrl OR !$row['action_text'])
                            $row['action_text'] = $resLinkTitle.$attach_result;
                        else if($attach_result)
                            $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']).$attach_result;
                        else
                            $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);

                    } else
                        $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);

                    $resLinkTitle = '';

                    //Если это запись с "рассказать друзьям"
                    if($rec_info_groups['tell_uid']){
                        $server_time = \Sura\Libs\Tools::time();

                        if(date('Y-m-d', $rec_info_groups['tell_date']) == date('Y-m-d', $server_time))
                            $dateTell = langdate('сегодня в H:i', $rec_info_groups['tell_date']);
                        elseif(date('Y-m-d', $rec_info_groups['tell_date']) == date('Y-m-d', ($server_time-84600)))
                            $dateTell = langdate('вчера в H:i', $rec_info_groups['tell_date']);
                        else
                            $dateTell = langdate('j F Y в H:i', $rec_info_groups['tell_date']);


                        if($rec_info_groups['public']){
                            $rowUserTell = News::user_tell_info($rec_info_groups['tell_uid'], 2);
                            $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                            $tell_link = 'public';
                            if($rowUserTell['photo'])
                                $avaTell = '/uploads/groups/'.$rec_info_groups['tell_uid'].'/50_'.$rowUserTell['photo'];
                            else
                                $avaTell = '/images/no_ava_50.png';
                        } else {
                            $rowUserTell = News::user_tell_info($rec_info_groups['tell_uid'], 1);
                            $tell_link = 'u';
                            if($rowUserTell['user_photo'])
                                $avaTell = '/uploads/users/'.$rec_info_groups['tell_uid'].'/50_'.$rowUserTell['user_photo'];
                            else
                                $avaTell = '/images/no_ava_50.png';
                        }

                        if($rec_info_groups['tell_comm']) $border_tell_class = 'wall_repost_border'; else $border_tell_class = 'wall_repost_border3';

                        $row['action_text'] = <<<HTML
                            {$rec_info_groups['tell_comm']}
                            <div class="{$border_tell_class}">
                                <div class="wall_tell_info">
                                <div class="wall_tell_ava">
                                    <a href="/{$tell_link}{$rec_info_groups['tell_uid']}" onClick="Page.Go(this.href); return false">
                                        <img src="{$avaTell}" width="30"  alt="" />
                                    </a>
                                </div>
                                <div class="wall_tell_name">
                                    <a href="/{$tell_link}{$rec_info_groups['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a>
                                </div>
                                <div class="wall_tell_date">{$dateTell}</div>
                            </div>{$row['action_text']}
                                <div class=""></div>
                            </div>
                            HTML;
                    }

//                        $tpl->set('{comment}', stripslashes($row['action_text']));
                    $params['comment'] = stripslashes($row['action_text']);

                    //Если есть комменты к записи, то выполняем след. действия
                    if($rec_info_groups['fasts_num'] OR $rowInfoUser['comments'] == false)
                        $params['comments_link'] = true;
                    else {
                        $params['comments_link'] = false;
                    }

                    //Мне нравится
                    if(stripos($rec_info_groups['likes_users'], "u{$user_id}|") !== false){
//                            $tpl->set('{yes-like}', 'public_wall_like_yes');
//                            $tpl->set('{yes-like-color}', 'public_wall_like_yes_color');
//                            $tpl->set('{like-js-function}', 'groups.wall_remove_like('.$row['obj_id'].', '.$user_id.')');
                        $params['yes_like'] = 'public_wall_like_yes';
                        $params['yes_like_color'] = 'public_wall_like_yes_color';
                        $params['like_js_function'] = 'groups.wall_remove_like('.$row['obj_id'].', '.$user_id.')';
                    } else {
//                            $tpl->set('{yes-like}', '');
//                            $tpl->set('{yes-like-color}', '');
//                            $tpl->set('{like-js-function}', 'groups.wall_add_like('.$row['obj_id'].', '.$user_id.')');
                        $params['yes_like'] = '';
                        $params['yes_like_color'] = '';
                        $params['like_js_function'] = 'groups.wall_add_like('.$row['obj_id'].', '.$user_id.')';
                    }

                    if($rec_info_groups['likes_num']){
//                            $tpl->set('{likes}', $rec_info_groups['likes_num']);
                        $titles = array('человеку', 'людям', 'людям');//like
//                            $tpl->set('{likes-text}', '<span id="like_text_num'.$row['obj_id'].'">'.$rec_info_groups['likes_num'].'</span> '.Gramatic::declOfNum($rec_info_groups['likes_num'], $titles));
                        $params['likes'] = $rec_info_groups['likes_num'];
                        $params['likes_text'] = '<span id="like_text_num'.$row['obj_id'].'">'.$rec_info_groups['likes_num'].'</span> '.Gramatic::declOfNum($rec_info_groups['likes_num'], $titles);
                    } else {
//                            $tpl->set('{likes}', '');
//                            $tpl->set('{likes-text}', '<span id="like_text_num'.$row['obj_id'].'">0</span> человеку');
                        $params['likes'] = '';
                        $params['likes_text'] = '<span id="like_text_num'.$row['obj_id'].'">0</span> человеку';
                    }

                    //Выводим информцию о том кто смотрит страницу для себя
//                        $tpl->set('{viewer-id}', $user_id);
                    $params['viewer-id'] = $user_id;
                    if($user_info['user_photo']){
//                            $tpl->set('{viewer-ava}', '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
                        $sql_[$key]['viewer_ava'] = '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo'];
                    }
                    else{
//                            $tpl->set('{viewer-ava}', '/images/no_ava_50.png');
                        $sql_[$key]['viewer_ava'] = '/images/no_ava_50.png';
                    }

//                        $tpl->set('{rec-id}', $row['obj_id']);
                    $sql_[$key]['rec_id'] = $row['obj_id'];
//                        $tpl->set('[record]', '');
//                        $tpl->set('[/record]', '');
                    $sql_[$key]['record'] = true;
//                        $tpl->set('[wall]', '');
//                        $tpl->set('[/wall]', '');
                    $sql_[$key]['wall'] = true;
//                        $tpl->set('[groups]', '');
//                        $tpl->set('[/groups]', '');
                    $sql_[$key]['groups'] = true;
//                        $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                    $sql_[$key]['wall_func'] = false;
//                        $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                    $sql_[$key]['comment'] = false;
//                        $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                    $sql_[$key]['comment-form'] = false;
//                        $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                    $sql_[$key]['all_comm'] = false;
//                        $tpl->compile('content');

                    //Если есть комменты, то выводим и страница не "ответы"
                    if($rowInfoUser['comments']){

                        //Помещаем все комменты в id wall_fast_block_{id} это для JS
//                            $tpl->result['content'] .= '<div id="wall_fast_block_'.$row['obj_id'].'">';
                        if($rec_info_groups['fasts_num']){
                            if($rec_info_groups['fasts_num'] > 3)
                                $comments_limit = $rec_info_groups['fasts_num']-3;
                            else
                                $comments_limit = 0;

                            $sql_comments = News::comments($row['obj_id'], $comments_limit);

                            //Загружаем кнопку "Показать N запсии"
                            $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                            $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
//                                $tpl->set('{gram-record-all-comm}', Gramatic::declOfNum(($rec_info_groups['fasts_num']-3), $titles1).' '.($rec_info_groups['fasts_num']-3).' '.Gramatic::declOfNum(($rec_info_groups['fasts_num']-3), $titles2));
                            $params['gram_record_all_comm'] = Gramatic::declOfNum(($rec_info_groups['fasts_num']-3), $titles1).' '.($rec_info_groups['fasts_num']-3).' '.Gramatic::declOfNum(($rec_info_groups['fasts_num']-3), $titles2);
                            if($rec_info_groups['fasts_num'] < 4){
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                $params['all_comm'] = false;
                            }
                            else {
//                                    $tpl->set('{rec-id}', $row['obj_id']);
//                                    $tpl->set('[all-comm]', '');
//                                    $tpl->set('[/all-comm]', '');
                                $sql_[$key]['rec_id'] = $row['obj_id'];
                                $params['all_comm'] = true;
                            }
//                                $tpl->set('{author-id}', $row['ac_user_id']);
                            $params['author_id'] = $row['ac_user_id'];
//                                $tpl->set('[groups]', '');
//                                $tpl->set('[/groups]', '');
                            $params['groups'] = true;
//                                $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                            $sql_[$key]['wall_func'] = false;
//                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $params['record'] = false;
//                                $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                            $params['comment_form'] = false;
//                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                            $params['comment'] = false;
//                                $tpl->compile('content');

                            $config = $params['config'];

                            //Сообственно выводим комменты
                            foreach($sql_comments as $key => $row_comments){
//                                    $tpl->set('{name}', $row_comments['user_search_pref']);
                                $sql_comments[$key]['name'] = $row_comments['user_search_pref'];
                                if($row_comments['user_photo']){
//                                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
                                    $sql_comments[$key]['ava'] = $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo'];
                                }
                                else{
//                                        $tpl->set('{ava}', '/images/no_ava_50.png');
                                    $sql_comments[$key]['ava'] = '/images/no_ava_50.png';
                                }

//                                    $tpl->set('{rec-id}', $row['obj_id']);
                                $sql_comments[$key]['rec_id'] = $row['obj_id'];
//                                    $tpl->set('{comm-id}', $row_comments['id']);
                                $sql_comments[$key]['comm_id'] = $row_comments['id'];
//                                    $tpl->set('{user-id}', $row_comments['public_id']);
                                $sql_comments[$key]['user_id'] = $row_comments['public_id'];
//                                    $tpl->set('{public-id}', $row['ac_user_id']);
                                $sql_comments[$key]['public_id'] = $row['ac_user_id'];

                                $expBR2 = explode('<br />', $row_comments['text']);
                                $textLength2 = count($expBR2);
                                $strTXT2 = strlen($row_comments['text']);
                                if($textLength2 > 6 OR $strTXT2 > 470)
                                    $sql_comments[$key]['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                                //Обрабатываем ссылки
                                $sql_comments[$key]['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

//                                    $tpl->set('{text}', );
                                $sql_comments[$key]['text'] = stripslashes($row_comments['text']);
                                $date = megaDate($row_comments['add_date']);
//                                    $tpl->set('{date}', $date);
                                $sql_comments[$key]['date'] = $date;
                                if($user_id == $row_comments['public_id']){
//                                        $tpl->set('[owner]', '');
//                                        $tpl->set('[/owner]', '');
                                    $sql_comments[$key]['owner'] = true;
                                } else{
//                                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                                    $sql_comments[$key]['owner'] = false;
                                }

                                if($user_id == $row_comments['author_user_id'])

//                                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                                    $sql_comments[$key]['not_owner'] = false;
                                else {

//                                        $tpl->set('[not-owner]', '');
//                                        $tpl->set('[/not-owner]', '');
                                    $sql_comments[$key]['not_owner'] = false;
                                }

//                                    $tpl->set('[comment]', '');
//                                    $tpl->set('[/comment]', '');
                                $sql_comments[$key]['comment'] = true;
//                                    $tpl->set('[groups]', '');
//                                    $tpl->set('[/groups]', '');
                                $sql_comments[$key]['groups'] = true;
//                                    $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                                $sql_comments[$key]['wall_func'] = false;
//                                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                $sql_comments[$key]['record'] = false;
//                                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                                $sql_comments[$key]['comment_form'] = false;
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                $sql_comments[$key]['all_comm'] = false;
//                                    $tpl->compile('content');
                            }

                            //Загружаем форму ответа
//                                $tpl->set('{rec-id}', $row['obj_id']);
                            $sql_[$key]['rec_id'] = $row['obj_id'];
//                                $tpl->set('{author-id}', $row['ac_user_id']);
                            $sql_[$key]['author_id'] = $row['ac_user_id'];
//                                $tpl->set('[comment-form]', '');
//                                $tpl->set('[/comment-form]', '');
                            $sql_[$key]['comment_form'] = true;
//                                $tpl->set('[groups]', '');
//                                $tpl->set('[/groups]', '');
                            $sql_[$key]['groups'] = true;
//                                $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                            $sql_[$key]['wall_func'] = false;
//                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $sql_[$key]['record'] = false;
//                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                            $sql_[$key]['comment'] = false;
//                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                            $sql_[$key]['all_comm'] = false;
//                                $tpl->compile('content');
                        }
//                            $tpl->result['content'] .= '</div>';
                    }
//                        $tpl->result['content'] .= '</div></div>';
                    //ads
                }
                //user
                elseif($row['action_type'] == 1) {
                    $rowInfoUser = News::row_type11($row['ac_user_id'], 1);
                    $row['user_search_pref'] = $rowInfoUser['user_search_pref'];

                    $sql_[$key]['author'] = $rowInfoUser['user_search_pref'];
                    $row['user_last_visit'] = $rowInfoUser['user_last_visit'];
                    $row['user_logged_mobile'] = $rowInfoUser['user_logged_mobile'];
                    $row['user_photo'] = $rowInfoUser['user_photo'];
                    $row['user_sex'] = $rowInfoUser['user_sex'];
                    $row['user_privacy'] = $rowInfoUser['user_privacy'];
//                        $tpl->set('{link}', 'u');
                    $sql_[$key]['link'] = 'u';

                    if($row['user_photo']){
//                            $tpl->set('{ava}', );
                        $sql_[$key]['ava'] = '/uploads/users/'.$row['ac_user_id'].'/50_'.$row['user_photo'];
                    }
                    else{
//                            $tpl->set('{ava}', '/images/no_ava_50.png');
                        $sql_[$key]['ava'] = '/images/no_ava_50.png';
                    }


                    //Приватность
                    $user_privacy = xfieldsdataload($row['user_privacy']);
                    $check_friend = Tools::CheckFriends($row['ac_user_id']);

                    //Выводим кол-во комментов, мне нравится, и список юзеров кто поставил лайки к записи если это не страница "ответов"
                    $rec_info = News::rec_info($row['obj_id']);

                    //КНопка Показать полностью..
                    $expBR = explode('<br />', $row['action_text']);
                    $textLength = count($expBR);
                    $strTXT = strlen($row['action_text']);
                    if($textLength > 9 OR $strTXT > 600)
                        $row['action_text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row['obj_id'].'">'.$row['action_text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row['obj_id'].', this.id)" id="hide_wall_rec_lnk'.$row['obj_id'].'">Показать полностью..</div>';

                    //Прикрипленные файлы
                    if($rec_info['attach']){
                        $attach_arr = explode('||', $rec_info['attach']);
                        $cnt_attach = 1;
                        $cnt_attach_link = 1;
                        $jid = 0;
                        $attach_result = '';
                        $attach_result .= '<div class=""></div>';
                        $config = $params['config'];
                        $resLinkTitle = '';
                        $resLinkUrl = '';
                        $row_wall = null; //bug

                        foreach($attach_arr as $attach_file){
                            $attach_type = explode('|', $attach_file);

                            //Фото со стены сообщества
                            if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$rec_info['tell_uid']}/photos/c_{$attach_type[1]}")){
                                if($cnt_attach < 2)
                                    $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$rec_info['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$rec_info['tell_uid']}/photos/{$attach_type[1]}\"  alt=\"\" /></div>";
                                else
                                    $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$rec_info['tell_uid']}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$rec_info['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                $cnt_attach++;

                                $resLinkTitle = '';

                                //Фото со стены юзера
                            } elseif($attach_type[0] == 'photo_u'){
                                if($rec_info['tell_uid']) $attauthor_user_id = $rec_info['tell_uid'];
                                else $attauthor_user_id = $row['ac_user_id'];
                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\"  alt=\"\" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                    $cnt_attach++;
                                } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\"  alt=\"\" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                    $cnt_attach++;
                                }

                                $resLinkTitle = '';

                                //Видео
                            } elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                $for_cnt_attach_video = explode('video|', $rec_info['attach']);
                                $cnt_attach_video = count($for_cnt_attach_video)-1;

                                if($cnt_attach_video == 1 AND preg_match('/(photo|photo_u)/i', $rec_info['attach']) == false){

                                    $video_id = intval($attach_type[2]);

                                    $row_video = News::video_info($video_id);
                                    $row_video['title'] = stripslashes($row_video['title']);
                                    $row_video['video'] = stripslashes($row_video['video']);
                                    $row_video['video'] = strtr($row_video['video'], array('width="770"' => 'width="390"', 'height="420"' => 'height="310"'));

                                    $attach_result .= "<div class=\"cursor_pointer \" id=\"no_video_frame{$video_id}\" onClick=\"$('#'+this.id).hide();$('#video_frame{$video_id}').show();\">
                                        <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px\" width=\"390\" height=\"310\"  alt=\"\" /></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div><div class=\"video_inline_vititle\"></div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><b>{$row_video['title']}</b></a>";

                                } else {

                                    $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\"  alt=\"\" /></a></div>";

                                }

                                $resLinkTitle = '';

                                //Музыка
                            } elseif($attach_type[0] == 'audio'){
                                $audioId = intval($attach_type[1]);
                                $audioInfo = News::audio_info($audioId);
                                if($audioInfo){
                                    $jid++;
                                    $attach_result .= '<div class="audioForSize'.$row['obj_id'].' player_mini_mbar_wall_all" id="audioForSize"><div class="audio_onetrack audio_wall_onemus"><div class="audio_playic cursor_pointer fl_l" onClick="music.newStartPlay(\''.$jid.'\', '.$row['obj_id'].')" id="icPlay_'.$row['obj_id'].$jid.'"></div><div id="music_'.$row['obj_id'].$jid.'" data="'.$audioInfo['url'].'" class="fl_l" style="margin-top:-1px"><a href="/?go=search&type=5&query='.$audioInfo['artist'].'&n=1" onClick="Page.Go(this.href); return false"><b>'.stripslashes($audioInfo['artist']).'</b></a> &ndash; '.stripslashes($audioInfo['title']).'</div><div id="play_time'.$row['obj_id'].$jid.'" class="color777 fl_r no_display" style="margin-top:2px;margin-right:5px">00:00</div><div class="player_mini_mbar fl_l no_display player_mini_mbar_wall player_mini_mbar_wall_all" id="ppbarPro'.$row['obj_id'].$jid.'"></div></div></div>';
                                }

                                $resLinkTitle = '';

                                //Смайлик
                            } elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" />';

                                $resLinkTitle = '';
                                //Если ссылка
                            } elseif($attach_type[0] == 'link' AND preg_match('/https:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1 AND stripos(str_replace('https://www.', 'https://', $attach_type[1]), $config['home_url']) === false){
//                                    $count_num = count($attach_type);
                                $domain_url_name = explode('/', $attach_type[1]);
                                $rdomain_url_name = str_replace('https://', '', $domain_url_name[2]);

                                $attach_type[3] = stripslashes($attach_type[3]);
                                $attach_type[3] = substr($attach_type[3], 0, 200);

                                $attach_type[2] = stripslashes($attach_type[2]);
                                $str_title = substr($attach_type[2], 0, 55);

                                if(stripos($attach_type[4], '/uploads/attach/') === false){
                                    $attach_type[4] = '/images/no_ava_groups_100.gif';
                                    $no_img = false;
                                } else
                                    $no_img = true;

                                if(!$attach_type[3]) $attach_type[3] = '';

                                if($no_img AND $attach_type[2]){
                                    if($rec_info['tell_comm']) $no_border_link = 'border:0';

                                    $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class=""></div><div class="wall_show_block_link" style="'.$no_border_link.'"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'"  alt=""/></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                } else if($attach_type[1] AND $attach_type[2]){
                                    $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class=""></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                }

                                $cnt_attach_link++;

                                //Если документ
                            } elseif($attach_type[0] == 'doc'){

                                $doc_id = intval($attach_type[1]);

                                $row_doc = News::doc_info($doc_id);

                                if($row_doc){

                                    $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class=""><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row['obj_id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row['obj_id'].'">'.$row_doc['dname'].'</a></div></div></div><div class=""></div>';

                                    $cnt_attach++;
                                }

                                //Если опрос
                            } elseif($attach_type[0] == 'vote'){

                                $vote_id = intval($attach_type[1]);

                                $row_vote = News::video_info($vote_id);

                                if($vote_id){
                                    $checkMyVote = News::vote_info_check($vote_id, $user_id);

                                    $row_vote['title'] = stripslashes($row_vote['title']);

                                    if(!$row_wall['text'])
                                        $row_wall['text'] = $row_vote['title'];

                                    $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                    $max = $row_vote['answer_num'];

                                    $sql_answer = News::vote_info_answer($vote_id);
                                    $answer = array();
                                    foreach($sql_answer as $row_answer){

                                        $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                                    }

                                    $attach_result .= "<div class=\"\" style=\"height:10px\"></div><div id=\"result_vote_block{$vote_id}\"><div class=\"wall_vote_title\">{$row_vote['title']}</div>";

                                    for($ai = 0; $ai < sizeof($arr_answe_list); $ai++){

                                        if(!$checkMyVote['cnt']){

                                            $attach_result .= "<div class=\"wall_vote_oneanswe\" onClick=\"Votes.Send({$ai}, {$vote_id})\" id=\"wall_vote_oneanswe{$ai}\"><input type=\"radio\" name=\"answer\" /><span id=\"answer_load{$ai}\">{$arr_answe_list[$ai]}</span></div>";

                                        } else {

                                            $num = $answer[$ai]['cnt'];

                                            if(!$num ) $num = 0;
                                            if($max != 0) $proc = (100 * $num) / $max;
                                            else $proc = 0;
                                            $proc = round($proc, 2);

                                            $attach_result .= "<div class=\"wall_vote_oneanswe cursor_default\">
                                                    {$arr_answe_list[$ai]}<br />
                                                    <div class=\"wall_vote_proc fl_l\"><div class=\"wall_vote_proc_bg\" style=\"width:".intval($proc)."%\"></div><div style=\"margin-top:-16px\">{$num}</div></div>
                                                    <div class=\"fl_l\" style=\"margin-top:-1px\"><b>{$proc}%</b></div>
                                                    </div><div class=\"\"></div>";

                                        }

                                    }
                                    $titles = array('человек', 'человека', 'человек');//fave
                                    if($row_vote['answer_num']) $answer_num_text = Gramatic::declOfNum($row_vote['answer_num'], $titles);
                                    else $answer_num_text = 'человек';

                                    if($row_vote['answer_num'] <= 1) $answer_text2 = 'Проголосовал';
                                    else $answer_text2 = 'Проголосовало';

                                    $attach_result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"\" style=\"margin-top:10px\"></div></div>";

                                }

                            } else

                                $attach_result .= '';

                        }

                        if($resLinkTitle AND $row['action_text'] == $resLinkUrl OR !$row['action_text'])
                            $row['action_text'] = $resLinkTitle.$attach_result;
                        else if($attach_result)
                            $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']).$attach_result;
                        else
                            $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);
                    }
                    else{
                        $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);
                    }

                    $resLinkTitle = '';

                    //Если это запись с "рассказать друзьям"
                    if($rec_info['tell_uid']){
                        if($rec_info['public'])
                            $rowUserTell = News::user_tell_info($rec_info['tell_uid'], 2);
                        else
                            $rowUserTell = News::user_tell_info($rec_info['tell_uid'], 1);

                        $server_time = \Sura\Libs\Tools::time();

                        if(date('Y-m-d', $rec_info['tell_date']) == date('Y-m-d', $server_time))
                            $dateTell = langdate('сегодня в H:i', $rec_info['tell_date']);
                        elseif(date('Y-m-d', $rec_info['tell_date']) == date('Y-m-d', ($server_time-84600)))
                            $dateTell = langdate('вчера в H:i', $rec_info['tell_date']);
                        else
                            $dateTell = langdate('j F Y в H:i', $rec_info['tell_date']);

                        if($rec_info['public']){
                            $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                            $tell_link = 'public';
                            if($rowUserTell['photo'])
                                $avaTell = '/uploads/groups/'.$rec_info['tell_uid'].'/50_'.$rowUserTell['photo'];
                            else
                                $avaTell = '/images/no_ava_50.png';
                        } else {
                            $tell_link = 'u';
                            if($rowUserTell['user_photo'])
                                $avaTell = '/uploads/users/'.$rec_info['tell_uid'].'/50_'.$rowUserTell['user_photo'];
                            else
                                $avaTell = '/images/no_ava_50.png';
                        }

                        if($rec_info['tell_comm']) $border_tell_class = 'wall_repost_border'; else $border_tell_class = '';

                        $row['action_text'] = <<<HTML
                            {$rec_info['tell_comm']}
                            <div class="{$border_tell_class}">
                            <div class="wall_tell_info"><div class="wall_tell_ava"><a href="/{$tell_link}{$rec_info['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30"  alt=\"\" /></a></div><div class="wall_tell_name"><a href="/{$tell_link}{$rec_info['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class="wall_tell_date">{$dateTell}</div></div>{$row['action_text']}
                            <div class=""></div>
                            </div>
                            HTML;
                    }

//                        $tpl->set('{comment}', stripslashes($row['action_text']));
                    $params['comment'] = stripslashes($row['action_text']);

                    //Если есть комменты к записи, то выполняем след. действия
                    if($rec_info['fasts_num'])
                        $sql_[$key]['comments_link'] = false;
                    else {
                        $sql_[$key]['comments_link'] = true;
                    }

                    if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $row['ac_user_id']){
                        $sql_[$key]['comments_link'] = true;
                    } else{
                        $sql_[$key]['comments_link'] = false;
                    }

                    if($rec_info['type'])
                        $params['action_type_updates'] = $rec_info['type'];
                    else{
                        $params['action_type_updates'] = '';
                    }

                    //Мне нравится
                    if(stripos($rec_info['likes_users'], "u{$user_id}|") !== false){
                        $params['yes_like'] = 'public_wall_like_yes';
                        $params['yes_like_color'] = 'public_wall_like_yes_color';
                        $params['yes_js_function'] = 'groups.wall_remove_like('.$row['obj_id'].', '.$user_id.', \'uPages\')';
                    } else {
                        $params['yes_like'] = '';
                        $params['yes_like_color'] = '';
                        $params['yes_js_function'] = 'groups.wall_add_like('.$row['obj_id'].', '.$user_id.', \'uPages\')';
                    }

                    if($rec_info['likes_num']){
                        $params['likes'] = $rec_info['likes_num'];
                        $titles = array('человеку', 'людям', 'людям');//like
                        $params['likes_text'] = '<span id="like_text_num'.$row['obj_id'].'">'.$rec_info['likes_num'].'</span> '.Gramatic::declOfNum($rec_info['likes_num'], $titles);
                    } else {
                        $params['likes'] = '';
                        $params['likes_text'] = '<span id="like_text_num'.$row['obj_id'].'">0</span> человеку';
                    }

                    //Выводим информцию о том кто смотрит страницу для себя
                    $params['viewer_id'] = $user_id;
                    if($user_info['user_photo']){
                        $params['viewer_ava'] = '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo'];
                    }
                    else{
                        $params['viewer_ava'] = '/images/no_ava_50.png';
                    }

                    $sql_[$key]['rec_id'] = $row['obj_id'];
                    $sql_[$key]['record'] = true;
                    $sql_[$key]['wall'] = true;
                    $sql_[$key]['wall_func'] = true;
                    $sql_[$key]['groups'] = false;
                    $sql_[$key]['comment'] = false;
                    $sql_[$key]['comment_form'] = false;
                    $sql_[$key]['all_comm'] = false;

                    //Если есть комменты, то выводим и страница не "ответы"
                    if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $row['ac_user_id']){
                        //Помещаем все комменты в id wall_fast_block_{id} это для JS
//                            $tpl->result['content'] .= '<div id="wall_fast_block_'.$row['obj_id'].'">';
                        if($rec_info['fasts_num']){
                            if($rec_info['fasts_num'] > 3)
                                $comments_limit = $rec_info['fasts_num']-3;
                            else
                                $comments_limit = 0;

                            $sql_comments = News::comments($row['obj_id'], $comments_limit);

                            //Загружаем кнопку "Показать N запсии"
                            $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                            $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
                            $params['gram_record_all_comm'] = Gramatic::declOfNum(($rec_info['fasts_num']-3), $titles1).' '.($rec_info['fasts_num']-3).' '.Gramatic::declOfNum(($rec_info['fasts_num']-3), $titles2);
                            if($rec_info['fasts_num'] < 4){
                                $sql_[$key]['all_comm'] = false;
                            }
                            else {
                                $sql_[$key]['rec_id'] = $row['obj_id'];
                                $sql_[$key]['all_comm'] = true;
                            }
                            $params['author_id '] = $row['ac_user_id'];
                            $sql_[$key]['wall_func'] = true;
                            $sql_[$key]['groups'] = false;
//                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $sql_[$key]['record'] = false;
//                                $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                            $sql_[$key]['comment_form'] = false;
//                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                            $sql_[$key]['comment'] = false;
//                                $tpl->compile('content');
                            $config = $params['config'];

                            //Сообственно выводим комменты
                            foreach($sql_comments as $row_comments){
//                                    $tpl->set('{name}', $row_comments['user_search_pref']);
                                $params['name'] = $row_comments['user_search_pref'];
                                if($row_comments['user_photo']){
//                                        $tpl->set('{ava}', $config["home_url"].'uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo']);
                                    $params['ava'] = $config["home_url"].'uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo'];
                                }
                                else{
//                                        $tpl->set('{ava}', '/images/no_ava_50.png');
                                    $params['ava'] = '/images/no_ava_50.png';
                                }

//                                    $tpl->set('{rec-id}', $row['obj_id']);
                                $sql_comments[$key]['rec_id'] = $row['obj_id'];
//                                    $tpl->set('{comm-id}', $row_comments['id']);
                                $params['comm_id'] = $row_comments['id'];
//                                    $tpl->set('{user-id}', $row_comments['author_user_id']);
                                $params['user_id'] = $row_comments['author_user_id'];

                                $expBR2 = explode('<br />', $row_comments['text']);
                                $textLength2 = count($expBR2);
                                $strTXT2 = strlen($row_comments['text']);
                                if($textLength2 > 6 OR $strTXT2 > 470)
                                    $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                                //Обрабатываем ссылки
                                $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

//                                    $tpl->set('{text}', );
                                $params['text'] = stripslashes($row_comments['text']);
                                $date = megaDate($row_comments['add_date']);
//                                    $tpl->set('{date}', $date);
                                $params['date'] = $date;
                                if($user_id == $row_comments['author_user_id']){
//                                        $tpl->set('[owner]', '');
//                                        $tpl->set('[/owner]', '');
                                    $params['owner'] = true;
                                } else
//                                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                                    $params['owner'] = false;
                                if($user_id == $row_comments['author_user_id']){
//                                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                                    $params['not_owner'] = false;
                                }
                                else {
//                                        $tpl->set('[not-owner]', '');
//                                        $tpl->set('[/not-owner]', '');
                                    $params['not_owner'] = true;
                                }
//                                    $tpl->set('[comment]', '');
//                                    $tpl->set('[/comment]', '');
                                $params['comment'] = true;
//                                    $tpl->set('[wall-func]', '');
//                                    $tpl->set('[/wall-func]', '');
                                $sql_[$key]['wall_func'] = true;
//                                    $tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
                                $params['groups'] = false;
//                                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                $params['record'] = false;
//                                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                                $params['comment_form'] = false;
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                $params['all_comm'] = false;
//                                    $tpl->compile('content');
                            }

                            //Загружаем форму ответа
//                                $tpl->set('{rec-id}', $row['obj_id']);
                            $sql_[$key]['rec_id'] = $row['obj_id'];
//                                $tpl->set('{author-id}', $row['ac_user_id']);
                            $params['author_id'] = $row['ac_user_id'];
//                                $tpl->set('[comment-form]', '');
//                                $tpl->set('[/comment-form]', '');
                            $params['comment_form'] = true;
//                                $tpl->set('[wall-func]', '');
//                                $tpl->set('[/wall-func]', '');
                            $sql_[$key]['wall_func'] = true;
//                                $tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
                            $params['groups'] = false;
//                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $params['record'] = false;
//                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                            $params['comment'] = false;
//                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                            $params['all_comm'] = false;
//                                $tpl->compile('content');
                        }
                    }



                }
                else {
                    $rowInfoUser = News::row_type11($row['ac_user_id'], 1);
                    $row['user_search_pref'] = $rowInfoUser['user_search_pref'];
                    $row['user_last_visit'] = $rowInfoUser['user_last_visit'];
                    $row['user_logged_mobile'] = $rowInfoUser['user_logged_mobile'];
                    $row['user_photo'] = $rowInfoUser['user_photo'];
                    $row['user_sex'] = $rowInfoUser['user_sex'];
                    $row['user_privacy'] = $rowInfoUser['user_privacy'];
//                        $params['user'] =
//                        $tpl->set('{link}', 'u');
                    $params['link'] = 'u';

                    if($row['user_photo']){
//                            $tpl->set('{ava}', '/uploads/users/'.$row['ac_user_id'].'/50_'.$row['user_photo']);
                        $params['ava'] = '/uploads/users/'.$row['ac_user_id'].'/50_'.$row['user_photo'];
                    }
                    else{
//                            $tpl->set('{ava}', '/images/no_ava_50.png');
                        $params['ava'] = '/images/no_ava_50.png';
                    }

                    $sql_[$key]['record'] = true;
                    $sql_[$key]['comment'] = false;
                    $sql_[$key]['wall'] = false;
                    $sql_[$key]['comment_form'] = false;
                    $sql_[$key]['all_comm'] = false;
                    $sql_[$key]['comments_link'] = false;

//                        if($action_cnt){
//                            $tpl->compile('content');
//                        }
                }


            }

            */
//            $sql_ = Wall::build($sql_);
            $sql_ = Wall::build_news($sql_);
var_dump($sql_);
exit();
            return view('news.one_record', array('news' => $sql_));
        }
        return _e('');
    }

    /**
     * Вывод новостей
     *
     * @param $params
     * @return string
     * @throws Exception
     */
    public function feed($params): string
    {
        if(!isset($params['title'])) {
            $params['title'] = 'Новости' . ' | Sura';
        }

        $lang = $this->get_langs();
        $logged = $this->logged();
//        $logged = $params['user']['logged'];

        if ($logged){
            $request = (Request::getRequest()->getGlobal());
//            $user_info = $params['user']['user_info'];
//            $user_id = $params['user']['user_info']['user_id'];

            $user_info = $this->user_info();
            $user_id = $user_info['user_id'];
            $params['stories'] = Stories::all($user_id);
            $params['user_id'] = $user_id;

            //Сообщения
            if (isset($params['user_pm_num'])){
                $params['msg'] = $params['user_pm_num'];
            }else{
                $params['msg'] = '';
            }

            //Заявки в друзья
            $params['requests_link'] = $params['requests_link'] ?? '';
            $params['demands'] = $params['demands'] ?? '';

            //Отметки на фото
            if($user_info['user_new_mark_photos']){
                $params['my_id'] =  'newphotos';
            } else{
                $params['my_id'] =  $user_id;
                $params['new_photos'] = '';
            }

            //Приглашения в сообщества
            $params['new_groups_lnk'] = $params['new_groups_lnk'] ?? '/groups/';
            $params['new_groups'] = $params['new_groups'] ?? '';
            //Новости
            $params['new_news'] = $params['new_news'] ?? '';
            $params['news_link'] = $params['news_link'] ?? '';
            //Поддержка
            $params['support'] = $params['support'] ?? '';
            //UBM
            $params['new_ubm'] = $params['new_ubm'] ?? '';
            $params['gifts_link'] = $params['gifts_link'] ?? '/balance/';

            $sql_ = News::load_news($user_id, 0);

            $limit = 20;
            $count_all = 100;
/*
            $page = $request['page'] ?? 1;
            $page_num = $page ?? 1;
            $navi = new Navigation( "/" );
            $navi->tpl = "{page}/";
            $navi->spread = 4;
            $params['nav'] = $navi->build( $limit, $count_all, $page_num );
*/

            if($sql_){
/*
                foreach($sql_ as $key => $row){

                    //Выводим данные о том кто инсцинировал действие
//                    if($row['user_sex'] == 2){
//                        $sex_text = array(
//                            '1' => 'добавила',
//                            '2' => 'ответила',
//                            '3' => 'оценила',
//                            '4' => 'прокомментировала',
//                        );
//                    } else {
//                        $sex_text = array(
//                            '1' => 'добавил',
//                            '2' => 'ответил',
//                            '3' => 'оценил',
//                            '4' => 'прокомментировал',
//                        );
//                    }

                    $sql_[$key]['author_id'] = $row['ac_user_id'];

//                    if (!isset($row['user_logged_mobile']))
//                        $row['user_logged_mobile'] = '0';//bug: undefined
//
//                    if (!isset($row['user_last_visit']))
//                        $row['user_last_visit'] = null;

                    $sql_[$key]['online'] = Online($row['user_last_visit'], $row['user_logged_mobile']);

                    //Выводим данные о действии
                    $date = megaDate($row['action_time']);
                    $sql_[$key]['date'] = megaDate($row['action_time']);
//                    $row['action_time'] = megaDate($row['action_time']);
                    $sql_[$key]['date'] = $date;
//                    $tpl->set('{comment}', stripslashes($row['action_text']));
//                    $params['comment'] = stripslashes($row['action_text']);
                    $sql_[$key]['action_text'] = stripslashes($row['action_text']);
                    $params['news_id'] = $row['ac_id'];
                    $params['action_type_updates'] = '';
                    $params['action_type'] = '';

//                    $expFriensList = explode('||', $row['action_text']);
//                    $action_cnt = 0;

//                    $comment = '';

                    //public
                    if($row['action_type'] == 11){
                        $rowInfoUser = News::row_type11($row['ac_user_id'], 2);

                        $row['user_search_pref'] = $rowInfoUser['title'];

                        $sql_[$key]['author'] = $rowInfoUser['title'];
                        $sql_[$key]['link'] = 'public';

                        if($rowInfoUser['photo']){
                            $sql_[$key]['ava'] = '/uploads/groups/'.$row['ac_user_id'].'/50_'.$rowInfoUser['photo'];
                        }else{
                            $sql_[$key]['ava'] = '/images/no_ava_50.png';
                        }

                        //Выводим кол-во комментов, мне нравится, и список юзеров кто поставил лайки к записи если это не страница "ответов"
                        $rec_info_groups = News::rec_info_groups($row['obj_id']);


                        //КНопка Показать полностью..


                        $expBR = explode('<br />', $row['action_text']);
                        $textLength = count($expBR);
                        $strTXT = strlen($row['action_text']);
                        if($textLength > 9 OR $strTXT > 600) {
                            $row['action_text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row['obj_id'] . '">' . $row['action_text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row['obj_id'] . ' , this.id)" id="hide_wall_rec_lnk' . $row['obj_id'] . '">Показать полностью..</div>';
                        }


                        //Прикрипленные файлы
                        if(isset($rec_info_groups['attach']) ){
                            $attach_arr = explode('||', $rec_info_groups['attach']);
                            $cnt_attach = 1;
                            $cnt_attach_link = 1;
                            $jid = 0;
                            $attach_result = '';
                            //$attach_result .= '<div class=""></div>';//div.clear
                            $config = $params['config'];
                            $row_wall = null;
                            foreach($attach_arr as $attach_file){
                                $attach_type = explode('|', $attach_file);

                                if($rec_info_groups['public'])
                                    $row['ac_user_id'] = $rec_info_groups['tell_uid'];

                                //Фото со стены сообщества
                                if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$row['ac_user_id']}/photos/c_{$attach_type[1]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row['ac_user_id']}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['ac_user_id']}/photos/{$attach_type[1]}\"  alt=/"/" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['ac_user_id']}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row['ac_user_id']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=/"/"/>";

                                    $cnt_attach++;

                                    //Фото со стены юзера
                                }
                                elseif($attach_type[0] == 'photo_u'){
                                    if($rec_info_groups['tell_uid']) $attauthor_user_id = $rec_info_groups['tell_uid'];
                                    else $attauthor_user_id = $row['ac_user_id'];

                                    if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){
                                        if($cnt_attach < 2)
                                            $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\"  alt=\"\" /></div>";
                                        else
                                            $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                        $cnt_attach++;
                                    } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){
                                        if($cnt_attach < 2)
                                            $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\"  alt=\"\" /></div>";
                                        else
                                            $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row['obj_id']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";
                                        $cnt_attach++;
                                    }
                                    $resLinkTitle = '';
                                }
                                //Видео
                                elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                    $for_cnt_attach_video = explode('video|', $rec_info_groups['attach']);
                                    $cnt_attach_video = count($for_cnt_attach_video)-1;

                                    if($cnt_attach_video == 1 AND preg_match('/(photo|photo_u)/i', $rec_info_groups['attach']) == false){

                                        $video_id = intval($attach_type[2]);

                                        $row_video = News::video_info($video_id);
                                        $row_video['title'] = stripslashes($row_video['title']);
                                        $row_video['video'] = stripslashes($row_video['video']);
                                        $row_video['video'] = strtr($row_video['video'], array('width="770"' => 'width="390"', 'height="420"' => 'height="310"'));

                                        $attach_result .= "<div class=\"cursor_pointer \" id=\"no_video_frame{$video_id}\" onClick=\"$('#'+this.id).hide();$('#video_frame{$video_id}').show();\">
                                        <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px\" width=\"390\" height=\"310\"  alt=/"/"/></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div><div class=\"video_inline_vititle\"></div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><b>{$row_video['title']}</b></a>";

                                    } else {

                                        $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\"  alt=\"\" /></a></div>";

                                    }

                                    $resLinkTitle = '';

                                }
                                //Музыка
                                elseif($attach_type[0] == 'audio'){
                                    $audioId = intval($attach_type[1]);
                                    $audioInfo = News::audio_info($audioId);
                                    if($audioInfo){
                                        $jid++;
                                        $attach_result .= '<div class="audioForSize'.$row['obj_id'].' player_mini_mbar_wall_all" id="audioForSize"><div class="audio_onetrack audio_wall_onemus"><div class="audio_playic cursor_pointer fl_l" onClick="music.newStartPlay(\''.$jid.'\', '.$row['obj_id'].')" id="icPlay_'.$row['obj_id'].$jid.'"></div><div id="music_'.$row['obj_id'].$jid.'" data="'.$audioInfo['url'].'" class="fl_l" style="margin-top:-1px"><a href="/?go=search&type=5&query='.$audioInfo['artist'].'&n=1" onClick="Page.Go(this.href); return false"><b>'.stripslashes($audioInfo['artist']).'</b></a> &ndash; '.stripslashes($audioInfo['title']).'</div><div id="play_time'.$row['obj_id'].$jid.'" class="color777 fl_r no_display" style="margin-top:2px;margin-right:5px">00:00</div><div class="player_mini_mbar fl_l no_display player_mini_mbar_wall_all" id="ppbarPro'.$row['obj_id'].$jid.'"></div></div></div>';
                                    }

                                    $resLinkTitle = '';

                                }
                                //Смайлик
                                elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                    $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';

                                    $resLinkTitle = '';

                                }
                                //Если ссылка
                                elseif($attach_type['0'] == 'link' AND preg_match('/https:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1 AND stripos(str_replace('https://www.', 'https://', $attach_type[1]), $config['home_url']) === false){
//                                    $count_num = count($attach_type);
                                    $domain_url_name = explode('/', $attach_type['1']);
                                    $rdomain_url_name = str_replace('https://', '', $domain_url_name[2]);

                                    $attach_type['3'] = stripslashes($attach_type['3']);
                                    $attach_type['3'] = substr($attach_type['3'], 0, 200);

                                    $attach_type['2'] = stripslashes($attach_type[2]);
                                    $str_title = substr($attach_type['2'], 0, 55);

                                    if(stripos($attach_type['4'], '/uploads/attach/') === false){
                                        $attach_type['4'] = '/images/no_ava_groups_100.gif';
                                        $no_img = false;
                                    } else
                                        $no_img = true;

                                    if(!$attach_type['3']) $attach_type['3'] = '';

                                    if($no_img AND $attach_type['2']){
                                        if($rec_info_groups['tell_comm']) {
                                            $no_border_link = 'border:0';
                                        }else{
                                            $no_border_link = '';
                                        }

                                        $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type['1'].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class=""></div><div class="wall_show_block_link" style="'.$no_border_link.'"><a href="/away.php?url='.$attach_type['1'].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type['4'].'"  alt=\"\" /></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type['1'].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type['3'].'</div></div></div>';

                                        $resLinkTitle = $attach_type[2];
                                        $resLinkUrl = $attach_type[1];
                                    } else if($attach_type['1'] AND $attach_type['2']){
                                        $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url=' .$attach_type['1'].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class=""></div>';

                                        $resLinkTitle = $attach_type['2'];
                                        $resLinkUrl = $attach_type['1'];
                                    }

                                    $cnt_attach_link++;

                                }
                                //Если документ
                                elseif($attach_type['0'] == 'doc'){

                                    $doc_id = intval($attach_type['1']);

                                    $row_doc = News::doc_info($doc_id);

                                    if($row_doc){

                                        $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class=""><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row['obj_id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row['obj_id'].'">'.$row_doc['dname'].'</a></div></div></div><div class=""></div>';

                                        $cnt_attach++;
                                    }

                                }
                                //Если опрос
                                elseif($attach_type['0'] == 'vote'){

                                    $vote_id = intval($attach_type['1']);

                                    $row_vote = News::video_info($vote_id);

                                    if($vote_id){

                                        $checkMyVote = News::vote_info_check($vote_id, $user_id);

                                        $row_vote['title'] = stripslashes($row_vote['title']);

                                        if(!$row_wall['text'])
                                            $row_wall['text'] = $row_vote['title'];

                                        $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                        $max = $row_vote['answer_num'];

                                        $sql_answer = News::vote_info_answer($vote_id);
                                        $answer = array();
                                        foreach($sql_answer as $row_answer){

                                            $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                                        }

                                        $attach_result .= "<div class=\"\" style=\"height:10px\"></div><div id=\"result_vote_block{$vote_id}\"><div class=\"wall_vote_title\">{$row_vote['title']}</div>";

                                        for($ai = 0; $ai < sizeof($arr_answe_list); $ai++){

                                            if(!$checkMyVote['cnt']){

                                                $attach_result .= "<div class=\"wall_vote_oneanswe\" onClick=\"Votes.Send({$ai}, {$vote_id})\" id=\"wall_vote_oneanswe{$ai}\"><input type=\"radio\" name=\"answer\" /><span id=\"answer_load{$ai}\">{$arr_answe_list[$ai]}</span></div>";

                                            } else {

                                                $num = $answer[$ai]['cnt'];

                                                if(!$num ) $num = 0;
                                                if($max != 0) $proc = (100 * $num) / $max;
                                                else $proc = 0;
                                                $proc = round($proc, 2);

                                                $attach_result .= "<div class=\"wall_vote_oneanswe cursor_default\">
                                                    {$arr_answe_list[$ai]}<br />
                                                    <div class=\"wall_vote_proc fl_l\"><div class=\"wall_vote_proc_bg\" style=\"width:".intval($proc)."%\"></div><div style=\"margin-top:-16px\">{$num}</div></div>
                                                    <div class=\"fl_l\" style=\"margin-top:-1px\"><b>{$proc}%</b></div>
                                                    </div><div class=\"\"></div>";

                                            }

                                        }
                                        $titles = array('человек', 'человека', 'человек');//fave
                                        if($row_vote['answer_num']) $answer_num_text = Gramatic::declOfNum($row_vote['answer_num'], $titles);
                                        else $answer_num_text = 'человек';

                                        if($row_vote['answer_num'] <= 1) $answer_text2 = 'Проголосовал';
                                        else $answer_text2 = 'Проголосовало';

                                        $attach_result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"\" style=\"margin-top:10px\"></div></div>";

                                    }

                                }
                                else
                                    $attach_result .= '';
                            }

                            if($resLinkTitle AND $row['action_text'] == $resLinkUrl OR !$row['action_text'])
                                $row['action_text'] = $resLinkTitle.$attach_result;
                            else if($attach_result)
                                $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']).$attach_result;
                            else
                                $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);

                        } else {
                            $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);
                        }

                        $resLinkTitle = '';

                        //Если это запись с "рассказать друзьям"
                        if(isset($rec_info_groups['tell_uid']) ){

                            if ($rec_info_groups['tell_date'] > 0){
                                $dateTell = megaDate($rec_info_groups['tell_date']);
                            }else{
                                $dateTell = 'N/A';
                            }

                            if($rec_info_groups['public']){
                                $rowUserTell = News::user_tell_info($rec_info_groups['tell_uid'], 2);
                                $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                                $tell_link = 'public';
                                if($rowUserTell['photo']) {
                                    $avaTell = '/uploads/groups/' . $rec_info_groups['tell_uid'] . '/50_' . $rowUserTell['photo'];
                                }
                                else {
                                    $avaTell = '/images/no_ava_50.png';
                                }
                            } else {
                                $rowUserTell = News::user_tell_info($rec_info_groups['tell_uid'], 1);
                                $tell_link = 'u';
                                if($rowUserTell['user_photo'])
                                    $avaTell = '/uploads/users/'.$rec_info_groups['tell_uid'].'/50_'.$rowUserTell['user_photo'];
                                else
                                    $avaTell = '/images/no_ava_50.png';
                            }

                            if($rec_info_groups['tell_comm']) $border_tell_class = 'wall_repost_border'; else $border_tell_class = 'wall_repost_border3';

                            $row['action_text'] = <<<HTML
                            {$rec_info_groups['tell_comm']}
                            <div class="{$border_tell_class}">
                                <div class="wall_tell_info">
                                <div class="wall_tell_ava">
                                    <a href="/{$tell_link}{$rec_info_groups['tell_uid']}" onClick="Page.Go(this.href); return false">
                                        <img src="{$avaTell}" width="30"  alt="" />
                                    </a>
                                </div>
                                <div class="wall_tell_name">
                                    <a href="/{$tell_link}{$rec_info_groups['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a>
                                </div>
                                <div class="wall_tell_date">{$dateTell}</div>
                            </div>{$row['action_text']}
                                <div class=""></div>
                            </div>
                            HTML;
                        }


                        $params['comment'] = stripslashes($row['action_text']);

                        //Если есть комменты к записи, то выполняем след. действия
                        if($rec_info_groups['fasts_num'] OR $rowInfoUser['comments'] == false) {
                            $params['comments_link'] = true;
                        }
                        else {
                            $params['comments_link'] = false;
                        }



                        //Мне нравится
                        if(stripos($rec_info_groups['likes_users'], "u{$user_id}|") !== false){
//                            $tpl->set('{yes-like}', 'public_wall_like_yes');
//                            $tpl->set('{yes-like-color}', 'public_wall_like_yes_color');
//                            $tpl->set('{like-js-function}', 'groups.wall_remove_like('.$row['obj_id'].', '.$user_id.')');
                            $params['yes_like'] = 'public_wall_like_yes';
                            $params['yes_like_color'] = 'public_wall_like_yes_color';
                            $params['like_js_function'] = 'groups.wall_remove_like('.$row['obj_id'].', '.$user_id.')';
                        } else {
//                            $tpl->set('{yes-like}', '');
//                            $tpl->set('{yes-like-color}', '');
//                            $tpl->set('{like-js-function}', 'groups.wall_add_like('.$row['obj_id'].', '.$user_id.')');
                            $params['yes_like'] = '';
                            $params['yes_like_color'] = '';
                            $params['like_js_function'] = 'groups.wall_add_like('.$row['obj_id'].', '.$user_id.')';
                        }

                        if($rec_info_groups['likes_num']){
//                            $tpl->set('{likes}', $rec_info_groups['likes_num']);
                            $titles = array('человеку', 'людям', 'людям');//like
//                            $tpl->set('{likes-text}', '<span id="like_text_num'.$row['obj_id'].'">'.$rec_info_groups['likes_num'].'</span> '.Gramatic::declOfNum($rec_info_groups['likes_num'], $titles));
                            $params['likes'] = $rec_info_groups['likes_num'];
                            $params['likes_text'] = '<span id="like_text_num'.$row['obj_id'].'">'.$rec_info_groups['likes_num'].'</span> '.Gramatic::declOfNum($rec_info_groups['likes_num'], $titles);
                        } else {
//                            $tpl->set('{likes}', '');
//                            $tpl->set('{likes-text}', '<span id="like_text_num'.$row['obj_id'].'">0</span> человеку');
                            $params['likes'] = '';
                            $params['likes_text'] = '<span id="like_text_num'.$row['obj_id'].'">0</span> человеку';
                        }

                        //Выводим информцию о том кто смотрит страницу для себя
//                        $tpl->set('{viewer-id}', $user_id);
                        $params['viewer-id'] = $user_id;
                        if($user_info['user_photo']){
//                            $tpl->set('{viewer-ava}', '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
                            $sql_[$key]['viewer_ava'] = '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo'];
                        }
                        else{
//                            $tpl->set('{viewer-ava}', '/images/no_ava_50.png');
                            $sql_[$key]['viewer_ava'] = '/images/no_ava_50.png';
                        }

//                        $tpl->set('{rec-id}', $row['obj_id']);
                        $sql_[$key]['rec_id'] = $row['obj_id'];
//                        $tpl->set('[record]', '');
//                        $tpl->set('[/record]', '');
                        $sql_[$key]['record'] = true;
//                        $tpl->set('[wall]', '');
//                        $tpl->set('[/wall]', '');
                        $sql_[$key]['wall'] = true;
//                        $tpl->set('[groups]', '');
//                        $tpl->set('[/groups]', '');
                        $sql_[$key]['groups'] = true;
//                        $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                        $sql_[$key]['wall_func'] = false;
//                        $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                        $sql_[$key]['comment'] = false;
//                        $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                        $sql_[$key]['comment-form'] = false;
//                        $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                        $sql_[$key]['all_comm'] = false;
//                        $tpl->compile('content');

                        //Если есть комменты, то выводим и страница не "ответы"
                        if($rowInfoUser['comments']){

                            //Помещаем все комменты в id wall_fast_block_{id} это для JS
//                            $tpl->result['content'] .= '<div id="wall_fast_block_'.$row['obj_id'].'">';
                            if($rec_info_groups['fasts_num']){
                                if($rec_info_groups['fasts_num'] > 3) {
                                    $comments_limit = $rec_info_groups['fasts_num'] - 3;
                                }
                                else {
                                    $comments_limit = 0;
                                }

                                $sql_comments = News::comments($row['obj_id'], $comments_limit);

                                //Загружаем кнопку "Показать N записи"
                                $num = (int) $rec_info_groups['fasts_num']-3;
                                if ($num < 0){
                                    $num = 0;
                                }
                                $titles = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                                $prev = Gramatic::declOfNum($num, $titles);
                                $titles = array('комментарий', 'комментария', 'комментариев');//comments
                                $comments = Gramatic::declOfNum($num, $titles);
                                $params['gram_record_all_comm'] = $prev.' '.$num.' '.$comments;


                                if($rec_info_groups['fasts_num'] < 4){
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                    $params['all_comm'] = false;
                                }
                                else {
//                                    $tpl->set('{rec-id}', $row['obj_id']);
//                                    $tpl->set('[all-comm]', '');
//                                    $tpl->set('[/all-comm]', '');
                                    $sql_[$key]['rec_id'] = $row['obj_id'];
                                    $params['all_comm'] = true;
                                }
//                                $tpl->set('{author-id}', $row['ac_user_id']);
                                $params['author_id'] = $row['ac_user_id'];
//                                $tpl->set('[groups]', '');
//                                $tpl->set('[/groups]', '');
                                $params['groups'] = true;
//                                $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                                $sql_[$key]['wall_func'] = false;
//                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                $params['record'] = false;
//                                $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                                $params['comment_form'] = false;
//                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                                $params['comment'] = false;
//                                $tpl->compile('content');

                                $config = $params['config'];

                                //Собственно выводим комменты
                                foreach($sql_comments as $key => $row_comments){
//                                    $tpl->set('{name}', $row_comments['user_search_pref']);
                                    $sql_comments[$key]['name'] = $row_comments['user_search_pref'];
                                    if($row_comments['user_photo']){
//                                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
                                        $sql_comments[$key]['ava'] = $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo'];
                                    }
                                    else{
//                                        $tpl->set('{ava}', '/images/no_ava_50.png');
                                        $sql_comments[$key]['ava'] = '/images/no_ava_50.png';
                                    }

//                                    $tpl->set('{rec-id}', $row['obj_id']);
                                    $sql_comments[$key]['rec_id'] = $row['obj_id'];
//                                    $tpl->set('{comm-id}', $row_comments['id']);
                                    $sql_comments[$key]['comm_id'] = $row_comments['id'];
//                                    $tpl->set('{user-id}', $row_comments['public_id']);
                                    $sql_comments[$key]['user_id'] = $row_comments['public_id'];
//                                    $tpl->set('{public-id}', $row['ac_user_id']);
                                    $sql_comments[$key]['public_id'] = $row['ac_user_id'];

                                    $expBR2 = explode('<br />', $row_comments['text']);
                                    $textLength2 = count($expBR2);
                                    $strTXT2 = strlen($row_comments['text']);
                                    if($textLength2 > 6 OR $strTXT2 > 470)
                                        $sql_comments[$key]['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                                    //Обрабатываем ссылки
                                    $sql_comments[$key]['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

//                                    $tpl->set('{text}', );
                                    $sql_comments[$key]['text'] = stripslashes($row_comments['text']);
                                    $date = megaDate($row_comments['add_date']);
//                                    $tpl->set('{date}', $date);
                                    $sql_comments[$key]['date'] = $date;
                                    if($user_id == $row_comments['public_id']){
//                                        $tpl->set('[owner]', '');
//                                        $tpl->set('[/owner]', '');
                                        $sql_comments[$key]['owner'] = true;
                                    } else{
//                                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                                        $sql_comments[$key]['owner'] = false;
                                    }

                                    if($user_id == $row_comments['author_user_id'])

//                                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                                        $sql_comments[$key]['not_owner'] = false;
                                    else {

//                                        $tpl->set('[not-owner]', '');
//                                        $tpl->set('[/not-owner]', '');
                                        $sql_comments[$key]['not_owner'] = false;
                                    }

//                                    $tpl->set('[comment]', '');
//                                    $tpl->set('[/comment]', '');
                                    $sql_comments[$key]['comment'] = true;
//                                    $tpl->set('[groups]', '');
//                                    $tpl->set('[/groups]', '');
                                    $sql_comments[$key]['groups'] = true;
//                                    $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                                    $sql_comments[$key]['wall_func'] = false;
//                                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                    $sql_comments[$key]['record'] = false;
//                                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                                    $sql_comments[$key]['comment_form'] = false;
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                    $sql_comments[$key]['all_comm'] = false;
//                                    $tpl->compile('content');
                                }

                                //Загружаем форму ответа
//                                $tpl->set('{rec-id}', $row['obj_id']);
                                $sql_[$key]['rec_id'] = $row['obj_id'];
//                                $tpl->set('{author-id}', $row['ac_user_id']);
                                $sql_[$key]['author_id'] = $row['ac_user_id'];
//                                $tpl->set('[comment-form]', '');
//                                $tpl->set('[/comment-form]', '');
                                $sql_[$key]['comment_form'] = true;
//                                $tpl->set('[groups]', '');
//                                $tpl->set('[/groups]', '');
                                $sql_[$key]['groups'] = true;
//                                $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                                $sql_[$key]['wall_func'] = false;
//                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                $sql_[$key]['record'] = false;
//                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                                $sql_[$key]['comment'] = false;
//                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                $sql_[$key]['all_comm'] = false;
//                                $tpl->compile('content');
                            }
//                            $tpl->result['content'] .= '</div>';
                        }
//                        $tpl->result['content'] .= '</div></div>';
                        //ads
                    }
                    //user
                    elseif($row['action_type'] == 1) {
                        $rowInfoUser = News::row_type11($row['ac_user_id'], 1);
//                        var_dump($rowInfoUser);
//                        exit();

                        $row['user_search_pref'] = $rowInfoUser['user_search_pref'];

                        $sql_[$key]['author'] = $rowInfoUser['user_search_pref'];
                        $row['user_last_visit'] = $rowInfoUser['user_last_visit'];
                        $row['user_logged_mobile'] = $rowInfoUser['user_logged_mobile'];
                        $row['user_photo'] = $rowInfoUser['user_photo'];
                        $row['user_sex'] = $rowInfoUser['user_sex'];
                        $row['user_privacy'] = $rowInfoUser['user_privacy'];
//                        $tpl->set('{link}', 'u');
                        $sql_[$key]['link'] = 'u';

                        if($row['user_photo']){
//                            $tpl->set('{ava}', );
                            $sql_[$key]['ava'] = '/uploads/users/'.$row['ac_user_id'].'/50_'.$row['user_photo'];
                        }
                        else{
//                            $tpl->set('{ava}', '/images/no_ava_50.png');
                            $sql_[$key]['ava'] = '/images/no_ava_50.png';
                        }



                        //Приватность
                        $user_privacy = xfieldsdataload($row['user_privacy']);
                        $check_friend = Tools::CheckFriends($row['ac_user_id']);

                        //Выводим кол-во комментов, мне нравится, и список юзеров кто поставил лайки к записи если это не страница "ответов"
                        $rec_info = News::rec_info($row['obj_id']);

                        //КНопка Показать полностью..
                        $expBR = explode('<br />', $row['action_text']);
                        $textLength = count($expBR);
                        $strTXT = strlen($row['action_text']);
                        if($textLength > 9 OR $strTXT > 600)
                            $row['action_text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row['obj_id'].'">'.$row['action_text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row['obj_id'].', this.id)" id="hide_wall_rec_lnk'.$row['obj_id'].'">Показать полностью..</div>';

                        //Прикрипленные файлы
                        if($rec_info['attach']){
                            $attach_arr = explode('||', $rec_info['attach']);
                            $cnt_attach = 1;
                            $cnt_attach_link = 1;
                            $jid = 0;
                            $attach_result = '';
                            $attach_result .= '<div class=""></div>';
                            $config = $params['config'];
                            $resLinkTitle = '';
                            $resLinkUrl = '';
                            $row_wall = null; //bug

                            foreach($attach_arr as $attach_file){
                                $attach_type = explode('|', $attach_file);

                                //Фото со стены сообщества
                                if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$rec_info['tell_uid']}/photos/c_{$attach_type[1]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$rec_info['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$rec_info['tell_uid']}/photos/{$attach_type[1]}\"  alt=\"\" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$rec_info['tell_uid']}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$rec_info['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                    $cnt_attach++;

                                    $resLinkTitle = '';

                                    //Фото со стены юзера
                                } elseif($attach_type[0] == 'photo_u'){
                                    if($rec_info['tell_uid']) $attauthor_user_id = $rec_info['tell_uid'];
                                    else $attauthor_user_id = $row['ac_user_id'];
                                    if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){
                                        if($cnt_attach < 2)
                                            $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\"  alt=\"\" /></div>";
                                        else
                                            $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                        $cnt_attach++;
                                    } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){
                                        if($cnt_attach < 2)
                                            $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\"  alt=\"\" /></div>";
                                        else
                                            $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                        $cnt_attach++;
                                    }

                                    $resLinkTitle = '';

                                    //Видео
                                } elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                    $for_cnt_attach_video = explode('video|', $rec_info['attach']);
                                    $cnt_attach_video = count($for_cnt_attach_video)-1;

                                    if($cnt_attach_video == 1 AND preg_match('/(photo|photo_u)/i', $rec_info['attach']) == false){

                                        $video_id = intval($attach_type[2]);

                                        $row_video = News::video_info($video_id);
                                        $row_video['title'] = stripslashes($row_video['title']);
                                        $row_video['video'] = stripslashes($row_video['video']);
                                        $row_video['video'] = strtr($row_video['video'], array('width="770"' => 'width="390"', 'height="420"' => 'height="310"'));

                                        $attach_result .= "<div class=\"cursor_pointer \" id=\"no_video_frame{$video_id}\" onClick=\"$('#'+this.id).hide();$('#video_frame{$video_id}').show();\">
                                        <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px\" width=\"390\" height=\"310\"  alt=\"\" /></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div><div class=\"video_inline_vititle\"></div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><b>{$row_video['title']}</b></a>";

                                    } else {

                                        $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\"  alt=\"\" /></a></div>";

                                    }

                                    $resLinkTitle = '';

                                    //Музыка
                                } elseif($attach_type[0] == 'audio'){
                                    $audioId = intval($attach_type[1]);
                                    $audioInfo = News::audio_info($audioId);
                                    if($audioInfo){
                                        $jid++;
                                        $attach_result .= '<div class="audioForSize'.$row['obj_id'].' player_mini_mbar_wall_all" id="audioForSize"><div class="audio_onetrack audio_wall_onemus"><div class="audio_playic cursor_pointer fl_l" onClick="music.newStartPlay(\''.$jid.'\', '.$row['obj_id'].')" id="icPlay_'.$row['obj_id'].$jid.'"></div><div id="music_'.$row['obj_id'].$jid.'" data="'.$audioInfo['url'].'" class="fl_l" style="margin-top:-1px"><a href="/?go=search&type=5&query='.$audioInfo['artist'].'&n=1" onClick="Page.Go(this.href); return false"><b>'.stripslashes($audioInfo['artist']).'</b></a> &ndash; '.stripslashes($audioInfo['title']).'</div><div id="play_time'.$row['obj_id'].$jid.'" class="color777 fl_r no_display" style="margin-top:2px;margin-right:5px">00:00</div><div class="player_mini_mbar fl_l no_display player_mini_mbar_wall player_mini_mbar_wall_all" id="ppbarPro'.$row['obj_id'].$jid.'"></div></div></div>';
                                    }

                                    $resLinkTitle = '';

                                    //Смайлик
                                } elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                    $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" />';

                                    $resLinkTitle = '';
                                    //Если ссылка
                                } elseif($attach_type[0] == 'link' AND preg_match('/https:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1 AND stripos(str_replace('https://www.', 'https://', $attach_type[1]), $config['home_url']) === false){
//                                    $count_num = count($attach_type);
                                    $domain_url_name = explode('/', $attach_type[1]);
                                    $rdomain_url_name = str_replace('https://', '', $domain_url_name[2]);

                                    $attach_type[3] = stripslashes($attach_type[3]);
                                    $attach_type[3] = substr($attach_type[3], 0, 200);

                                    $attach_type[2] = stripslashes($attach_type[2]);
                                    $str_title = substr($attach_type[2], 0, 55);

                                    if(stripos($attach_type[4], '/uploads/attach/') === false){
                                        $attach_type[4] = '/images/no_ava_groups_100.gif';
                                        $no_img = false;
                                    } else
                                        $no_img = true;

                                    if(!$attach_type[3]) $attach_type[3] = '';

                                    if($no_img AND $attach_type[2]){
                                        if($rec_info['tell_comm']) $no_border_link = 'border:0';

                                        $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class=""></div><div class="wall_show_block_link" style="'.$no_border_link.'"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'"  alt=""/></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

                                        $resLinkTitle = $attach_type[2];
                                        $resLinkUrl = $attach_type[1];
                                    } else if($attach_type[1] AND $attach_type[2]){
                                        $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class=""></div>';

                                        $resLinkTitle = $attach_type[2];
                                        $resLinkUrl = $attach_type[1];
                                    }

                                    $cnt_attach_link++;

                                    //Если документ
                                } elseif($attach_type[0] == 'doc'){

                                    $doc_id = (int)$attach_type[1];

                                    $row_doc = News::doc_info($doc_id);

                                    if($row_doc){

                                        $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class=""><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row['obj_id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row['obj_id'].'">'.$row_doc['dname'].'</a></div></div></div><div class=""></div>';

                                        $cnt_attach++;
                                    }

                                    //Если опрос
                                } elseif($attach_type[0] == 'vote'){

                                    $vote_id = (int)$attach_type[1];

                                    $row_vote = News::video_info($vote_id);

                                    if($vote_id){
                                        $checkMyVote = News::vote_info_check($vote_id, $user_id);

                                        $row_vote['title'] = stripslashes($row_vote['title']);

                                        if(!$row_wall['text'])
                                            $row_wall['text'] = $row_vote['title'];

                                        $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                        $max = $row_vote['answer_num'];

                                        $sql_answer = News::vote_info_answer($vote_id);
                                        $answer = array();
                                        foreach($sql_answer as $row_answer){

                                            $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                                        }

                                        $attach_result .= "<div class=\"\" style=\"height:10px\"></div><div id=\"result_vote_block{$vote_id}\"><div class=\"wall_vote_title\">{$row_vote['title']}</div>";

                                        $aiMax = count($arr_answe_list);
                                        for($ai = 0; $ai < $aiMax; $ai++){

                                            if(!$checkMyVote['cnt']){

                                                $attach_result .= "<div class=\"wall_vote_oneanswe\" onClick=\"Votes.Send({$ai}, {$vote_id})\" id=\"wall_vote_oneanswe{$ai}\"><input type=\"radio\" name=\"answer\" /><span id=\"answer_load{$ai}\">{$arr_answe_list[$ai]}</span></div>";

                                            } else {

                                                $num = $answer[$ai]['cnt'];

                                                if(!$num ) $num = 0;
                                                if($max != 0) $proc = (100 * $num) / $max;
                                                else $proc = 0;
                                                $proc = round($proc, 2);

                                                $attach_result .= "<div class=\"wall_vote_oneanswe cursor_default\">
                                                    {$arr_answe_list[$ai]}<br />
                                                    <div class=\"wall_vote_proc fl_l\"><div class=\"wall_vote_proc_bg\" style=\"width:".intval($proc)."%\"></div><div style=\"margin-top:-16px\">{$num}</div></div>
                                                    <div class=\"fl_l\" style=\"margin-top:-1px\"><b>{$proc}%</b></div>
                                                    </div><div class=\"\"></div>";

                                            }

                                        }
                                        $titles = array('человек', 'человека', 'человек');//fave
                                        if($row_vote['answer_num']) $answer_num_text = Gramatic::declOfNum($row_vote['answer_num'], $titles);
                                        else $answer_num_text = 'человек';

                                        if($row_vote['answer_num'] <= 1) $answer_text2 = 'Проголосовал';
                                        else $answer_text2 = 'Проголосовало';

                                        $attach_result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"\" style=\"margin-top:10px\"></div></div>";

                                    }

                                } else

                                    $attach_result .= '';

                            }

                            if($resLinkTitle AND $row['action_text'] == $resLinkUrl OR !$row['action_text'])
                                $row['action_text'] = $resLinkTitle.$attach_result;
                            else if($attach_result)
                                $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']).$attach_result;
                            else
                                $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);
                        }
                        else{
                            $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);
                        }


                        $resLinkTitle = '';

                        //Если это запись с "рассказать друзьям"
                        if($rec_info['tell_uid']){
                            if($rec_info['public'])
                                $rowUserTell = News::user_tell_info($rec_info['tell_uid'], 2);
                            else
                                $rowUserTell = News::user_tell_info($rec_info['tell_uid'], 1);

                            $server_time = \Sura\Libs\Tools::time();

                            if(date('Y-m-d', $rec_info['tell_date']) == date('Y-m-d', $server_time))
                                $dateTell = langdate('сегодня в H:i', $rec_info['tell_date']);
                            elseif(date('Y-m-d', $rec_info['tell_date']) == date('Y-m-d', ($server_time-84600)))
                                $dateTell = langdate('вчера в H:i', $rec_info['tell_date']);
                            else
                                $dateTell = langdate('j F Y в H:i', $rec_info['tell_date']);

                            if($rec_info['public']){
                                $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                                $tell_link = 'public';
                                if($rowUserTell['photo'])
                                    $avaTell = '/uploads/groups/'.$rec_info['tell_uid'].'/50_'.$rowUserTell['photo'];
                                else
                                    $avaTell = '/images/no_ava_50.png';
                            } else {
                                $tell_link = 'u';
                                if($rowUserTell['user_photo'])
                                    $avaTell = '/uploads/users/'.$rec_info['tell_uid'].'/50_'.$rowUserTell['user_photo'];
                                else
                                    $avaTell = '/images/no_ava_50.png';
                            }

                            if($rec_info['tell_comm']) $border_tell_class = 'wall_repost_border'; else $border_tell_class = '';

                            $row['action_text'] = <<<HTML
                            {$rec_info['tell_comm']}
                            <div class="{$border_tell_class}">
                            <div class="wall_tell_info"><div class="wall_tell_ava"><a href="/{$tell_link}{$rec_info['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30"  alt=\"\" /></a></div><div class="wall_tell_name"><a href="/{$tell_link}{$rec_info['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class="wall_tell_date">{$dateTell}</div></div>{$row['action_text']}
                            <div class=""></div>
                            </div>
                            HTML;
                        }

//                        $tpl->set('{comment}', stripslashes($row['action_text']));
                        $params['comment'] = stripslashes($row['action_text']);

                        //Если есть комменты к записи, то выполняем след. действия
                        if($rec_info['fasts_num'])
                            $sql_[$key]['comments_link'] = false;
                        else {
                            $sql_[$key]['comments_link'] = true;
                        }

                        if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $row['ac_user_id']){
                            $sql_[$key]['comments_link'] = true;
                        } else{
                            $sql_[$key]['comments_link'] = false;
                        }

                        if($rec_info['type'])
                            $params['action_type_updates'] = $rec_info['type'];
                        else{
                            $params['action_type_updates'] = '';
                        }

                        //Мне нравится
                        if(stripos($rec_info['likes_users'], "u{$user_id}|") !== false){
                            $params['yes_like'] = 'public_wall_like_yes';
                            $params['yes_like_color'] = 'public_wall_like_yes_color';
                            $params['yes_js_function'] = 'groups.wall_remove_like('.$row['obj_id'].', '.$user_id.', \'uPages\')';
                        } else {
                            $params['yes_like'] = '';
                            $params['yes_like_color'] = '';
                            $params['yes_js_function'] = 'groups.wall_add_like('.$row['obj_id'].', '.$user_id.', \'uPages\')';
                        }

                        if($rec_info['likes_num']){
                            $params['likes'] = $rec_info['likes_num'];
                            $titles = array('человеку', 'людям', 'людям');//like
                            $params['likes_text'] = '<span id="like_text_num'.$row['obj_id'].'">'.$rec_info['likes_num'].'</span> '.Gramatic::declOfNum($rec_info['likes_num'], $titles);
                        } else {
                            $params['likes'] = '';
                            $params['likes_text'] = '<span id="like_text_num'.$row['obj_id'].'">0</span> человеку';
                        }

                        //Выводим информцию о том кто смотрит страницу для себя
                        $params['viewer_id'] = $user_id;
                        if($user_info['user_photo']){
                            $params['viewer_ava'] = '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo'];
                        }
                        else{
                            $params['viewer_ava'] = '/images/no_ava_50.png';
                        }

                        $sql_[$key]['rec_id'] = $row['obj_id'];
                        $sql_[$key]['record'] = true;
                        $sql_[$key]['wall'] = true;
                        $sql_[$key]['wall_func'] = true;
                        $sql_[$key]['groups'] = false;
                        $sql_[$key]['comment'] = false;
                        $sql_[$key]['comment_form'] = false;
                        $sql_[$key]['all_comm'] = false;


                        //Если есть комменты, то выводим и страница не "ответы"
                        if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $row['ac_user_id']){
                            //Помещаем все комменты в id wall_fast_block_{id} это для JS
//                            $tpl->result['content'] .= '<div id="wall_fast_block_'.$row['obj_id'].'">';


                            if($rec_info['fasts_num']){
                                if($rec_info['fasts_num'] > 3) {
                                    $comments_limit = $rec_info['fasts_num'] - 3;
                                }
                                else {
                                    $comments_limit = 0;
                                }


                                $sql_comments = News::comments($row['obj_id'], $comments_limit);

                                //Загружаем кнопку "Показать N запсии"
                                $num = (int) $rec_info['fasts_num']-3;
                                if ($num < 0){
                                    $num = 0;
                                }
                                $titles = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                                $prev = Gramatic::declOfNum($num, $titles);
                                $titles = array('комментарий', 'комментария', 'комментариев');//comments
                                $comments = Gramatic::declOfNum($num, $titles);
                                $params['gram_record_all_comm'] = $prev.' '.$num.' '.$comments;

                                if($rec_info['fasts_num'] < 4){
                                    $sql_[$key]['all_comm'] = false;
                                }
                                else {
                                    $sql_[$key]['rec_id'] = $row['obj_id'];
                                    $sql_[$key]['all_comm'] = true;
                                }
                                $params['author_id '] = $row['ac_user_id'];
                                $sql_[$key]['wall_func'] = true;
                                $sql_[$key]['groups'] = false;
//                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                $sql_[$key]['record'] = false;
//                                $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                                $sql_[$key]['comment_form'] = false;
//                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                                $sql_[$key]['comment'] = false;
//                                $tpl->compile('content');
                                $config = $params['config'];

                                //Сообственно выводим комменты
                                foreach($sql_comments as $row_comments){
//                                    $tpl->set('{name}', $row_comments['user_search_pref']);
                                    $params['name'] = $row_comments['user_search_pref'];
                                    if($row_comments['user_photo']){
//                                        $tpl->set('{ava}', $config["home_url"].'uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo']);
                                        $params['ava'] = $config["home_url"].'uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo'];
                                    }
                                    else{
//                                        $tpl->set('{ava}', '/images/no_ava_50.png');
                                        $params['ava'] = '/images/no_ava_50.png';
                                    }

//                                    $tpl->set('{rec-id}', $row['obj_id']);
                                    $sql_comments[$key]['rec_id'] = $row['obj_id'];
//                                    $tpl->set('{comm-id}', $row_comments['id']);
                                    $params['comm_id'] = $row_comments['id'];
//                                    $tpl->set('{user-id}', $row_comments['author_user_id']);
                                    $params['user_id'] = $row_comments['author_user_id'];

                                    $expBR2 = explode('<br />', $row_comments['text']);
                                    $textLength2 = count($expBR2);
                                    $strTXT2 = strlen($row_comments['text']);
                                    if($textLength2 > 6 OR $strTXT2 > 470)
                                        $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                                    //Обрабатываем ссылки
                                    $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

//                                    $tpl->set('{text}', );
                                    $params['text'] = stripslashes($row_comments['text']);
                                    $date = megaDate($row_comments['add_date']);
//                                    $tpl->set('{date}', $date);
                                    $params['date'] = $date;
                                    if($user_id == $row_comments['author_user_id']){
//                                        $tpl->set('[owner]', '');
//                                        $tpl->set('[/owner]', '');
                                        $params['owner'] = true;
                                    } else
//                                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                                    $params['owner'] = false;
                                    if($user_id == $row_comments['author_user_id']){
//                                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                                        $params['not_owner'] = false;
                                    }
                                    else {
//                                        $tpl->set('[not-owner]', '');
//                                        $tpl->set('[/not-owner]', '');
                                        $params['not_owner'] = true;
                                    }
//                                    $tpl->set('[comment]', '');
//                                    $tpl->set('[/comment]', '');
                                    $params['comment'] = true;
//                                    $tpl->set('[wall-func]', '');
//                                    $tpl->set('[/wall-func]', '');
                                    $sql_[$key]['wall_func'] = true;
//                                    $tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
                                    $params['groups'] = false;
//                                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                    $params['record'] = false;
//                                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                                    $params['comment_form'] = false;
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                    $params['all_comm'] = false;
//                                    $tpl->compile('content');
                                }

                                //Загружаем форму ответа
//                                $tpl->set('{rec-id}', $row['obj_id']);
                                $sql_[$key]['rec_id'] = $row['obj_id'];
//                                $tpl->set('{author-id}', $row['ac_user_id']);
                                $params['author_id'] = $row['ac_user_id'];
//                                $tpl->set('[comment-form]', '');
//                                $tpl->set('[/comment-form]', '');
                                $params['comment_form'] = true;
//                                $tpl->set('[wall-func]', '');
//                                $tpl->set('[/wall-func]', '');
                                $sql_[$key]['wall_func'] = true;
//                                $tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
                                $params['groups'] = false;
//                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                $params['record'] = false;
//                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                                $params['comment'] = false;
//                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                $params['all_comm'] = false;
//                                $tpl->compile('content');
                            }
                        }



                    }

                    else {
                        $rowInfoUser = News::row_type11($row['ac_user_id'], 1);

                        $row['user_search_pref'] = $rowInfoUser['user_search_pref'];
                        $row['user_last_visit'] = $rowInfoUser['user_last_visit'];
                        $row['user_logged_mobile'] = $rowInfoUser['user_logged_mobile'];
                        $row['user_photo'] = $rowInfoUser['user_photo'];
                        $row['user_sex'] = $rowInfoUser['user_sex'];
                        $row['user_privacy'] = $rowInfoUser['user_privacy'];
//                        $params['user'] =
//                        $tpl->set('{link}', 'u');
                        $params['link'] = 'u';

                        if($row['user_photo']){
//                            $tpl->set('{ava}', '/uploads/users/'.$row['ac_user_id'].'/50_'.$row['user_photo']);
                            $params['ava'] = '/uploads/users/'.$row['ac_user_id'].'/50_'.$row['user_photo'];
                        }
                        else{
//                            $tpl->set('{ava}', '/images/no_ava_50.png');
                            $params['ava'] = '/images/no_ava_50.png';
                        }

                        $sql_[$key]['record'] = true;
                        $sql_[$key]['comment'] = false;
                        $sql_[$key]['wall'] = false;
                        $sql_[$key]['comment_form'] = false;
                        $sql_[$key]['all_comm'] = false;
                        $sql_[$key]['comments_link'] = false;

//                        if($action_cnt){
//                            $tpl->compile('content');
//                        }
                    }


                }
*/

//                $params['news'] = $sql_;
                $params['news'] = Wall::build_news($sql_);

                //Выводи низ, если новостей больше 20
//                if($c > 19 AND !$_POST['page_cnt']){
//                    $tpl->load_template('news/head.tpl');
//                    $tpl->set('{type}', $type);
//                    $params['type'] = $type;
//                    $tpl->set('[bottom]', '');
//                    $tpl->set('[/bottom]', '');
                    $params['bottom'] = true;
//                    $tpl->set_block("'\\[news\\](.*?)\\[/news\\]'si","");
//                    $params['news'] = false;
//                    $tpl->compile('content');
//                }

                return view('news.news', $params);
            }

            $params['title'] = 'no news'.$lang['no_infooo'];
            /*
                         *                 $no_news = 'no_news';
                                        $params['title'] = 'no_news'.$lang['no_infooo'];
                                        $params['info'] = $no_news;
                                        return view('info.info', $params);
                        */
            $params['news'] = $sql_;
            return view('news.news', $params);
        }
        $params['title'] = 'no_news '.$lang['no_infooo'];
        $params['info'] = $lang['not_logged'];
        return view('info.info', $params);
    }
}