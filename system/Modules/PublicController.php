<?php
/* 
	Appointment: Загрузка городов
	File: loadcity.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Modules;

use System\Classes\Public_wall;
use System\Libs\Gramatic;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;
use System\Libs\Validation;

class PublicController extends Module{

    public function index($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];

        $config = include __DIR__.'/../data/config.php';

        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            $user_id = $user_info['user_id'];
            //$pid = intval($_GET['pid']);


            $path = explode('/', $_SERVER['REQUEST_URI']);
            $pid = str_replace('public', '', $path);
            $pid = intval($pid['1']);

            $mobile_speedbar = 'Сообщество';

            if(preg_match("/^[a-zA-Z0-9_-]+$/", $_GET['get_adres'])) $get_adres = $db->safesql($_GET['get_adres']);

            $sql_where = "id = '".$pid."'";

            if($pid){
                $get_adres = '';
                $sql_where = "id = '".$pid."'";
            }
            if($get_adres){
                $pid = '';
                $sql_where = "adres = '".$get_adres."'";
            } else

                echo $get_adres;

            //Если страница вывзана через "к предыдущим записям"
            $limit_select = 10;
            if($_POST['page_cnt'] > 0)
                $page_cnt = intval($_POST['page_cnt'])*$limit_select;
            else
                $page_cnt = 0;

            if($page_cnt){
                $row = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$pid}'");
                $row['id'] = $pid;
            } else
                $row = $db->super_query("SELECT id, title, descr, traf, ulist, photo, date, admin, feedback, comments, real_admin, rec_num, del, ban, adres, audio_num, forum_num, discussion, status_text, web, videos_num, cover, cover_pos FROM `".PREFIX."_communities` WHERE ".$sql_where."");

            if($row['del'] == 1){
                $user_speedbar = 'Страница удалена';
                msgbox('', '<br /><br />Сообщество удалено администрацией.<br /><br /><br />', 'info_2');
            } elseif($row['ban'] == 1){
                $user_speedbar = 'Страница заблокирована';
                msgbox('', '<br /><br />Сообщество заблокировано администрацией.<br /><br /><br />', 'info_2');
            } elseif($row){
                $metatags['title'] = stripslashes($row['title']);
                $user_speedbar = $lang['public_spbar'];

                if(stripos($row['admin'], "u{$user_id}|") !== false)
                    $public_admin = true;
                else
                    $public_admin = false;

                //Стена
                //Если страница вывзана через "к предыдущим записям"
                if($page_cnt)
                    Tools::NoAjaxQuery();

                // include __DIR__.'/../Classes/Public_wall.php';

                //$wall = new Public_wall();
                //$row = $wall->query();
                $query = $db->super_query("SELECT tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, fixed, tb2.title, photo, comments, adres FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = '{$row['id']}' AND tb1.public_id = tb2.id AND fast_comm_id = 0 ORDER by `fixed` DESC, `add_date` DESC LIMIT {$page_cnt}, {$limit_select}", 1);
                //$tpl = $wall->template('groups/record.tpl', $tpl);
                $tpl->load_template('groups/record.tpl');

                $server_time = intval($_SERVER['REQUEST_TIME']);

                //Если страница вывзана через "к предыдущим записям"
                if($page_cnt){
                    $compile = 'content';
                    //$tpl = $wall->compile('content', $tpl);
                }else{
                    $compile = 'wall';
                    //$tpl = $wall->compile('wall', $tpl);
                }

                $user_id = $user_info['user_id'];

                //$this->template;

                foreach($query as $row_wall){
                    $tpl->set('{rec-id}', $row_wall['id']);

                    //Закрепить запись
                    if($row_wall['fixed']){

                        $tpl->set('{styles-fasten}', 'style="opacity:1"');
                        $tpl->set('{fasten-text}', 'Закрепленная запись');
                        $tpl->set('{function-fasten}', 'wall_unfasten');

                    } else {

                        $tpl->set('{styles-fasten}', '');
                        $tpl->set('{fasten-text}', 'Закрепить запись');
                        $tpl->set('{function-fasten}', 'wall_fasten');

                    }

                    //КНопка Показать полностью..
                    $expBR = explode('<br />', $row_wall['text']);
                    $textLength = count($expBR);
                    $strTXT = strlen($row_wall['text']);
                    if($textLength > 9 OR $strTXT > 600)
                        $row_wall['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_wall['id'].'">'.$row_wall['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_wall['id'].', this.id)" id="hide_wall_rec_lnk'.$row_wall['id'].'">Показать полностью..</div>';

                    //Прикрипленные файлы
                    if($row_wall['attach']){
                        $attach_arr = explode('||', $row_wall['attach']);
                        $cnt_attach = 1;
                        $cnt_attach_link = 1;
                        //$jid = 0;
                        $attach_result = '';
                        $attach_result .= '<div class="clear"></div>';
                        foreach($attach_arr as $attach_file){
                            $attach_type = explode('|', $attach_file);

                            //Фото со стены сообщества
                            if($row_wall['tell_uid'])
                                $globParId = $row_wall['tell_uid'];
                            else
                                $globParId = $row_wall['public_id'];

                            if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$globParId}/photos/c_{$attach_type[1]}")){
                                if($cnt_attach < 2)
                                    $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$globParId}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$globParId}/photos/{$attach_type[1]}\" align=\"left\" /></div>";
                                else
                                    $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$globParId}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$globParId}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" />";

                                $cnt_attach++;

                                $resLinkTitle = '';

                                //Фото со стены юзера
                            } elseif($attach_type[0] == 'photo_u'){
                                $attauthor_user_id = $row_wall['tell_uid'];

                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\" align=\"left\" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" />";

                                    $cnt_attach++;
                                } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\" align=\"left\" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" />";

                                    $cnt_attach++;
                                }

                                $resLinkTitle = '';

                                //Видео
                            } elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                $for_cnt_attach_video = explode('video|', $row_wall['attach']);
                                $cnt_attach_video = count($for_cnt_attach_video)-1;

                                if($cnt_attach_video == 1 AND preg_match('/(photo|photo_u)/i', $row_wall['attach']) == false){

                                    $video_id = intval($attach_type[2]);

                                    $row_video = $db->super_query("SELECT video, title FROM `".PREFIX."_videos` WHERE id = '{$video_id}'", false, "wall/video{$video_id}");
                                    $row_video['title'] = stripslashes($row_video['title']);
                                    $row_video['video'] = stripslashes($row_video['video']);
                                    $row_video['video'] = strtr($row_video['video'], array('width="770"' => 'width="390"', 'height="420"' => 'height="310"'));

                                    $attach_result .= "<div class=\"cursor_pointer clear\" id=\"no_video_frame{$video_id}\" onClick=\"$('#'+this.id).hide();$('#video_frame{$video_id}').show();\">
							        <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px\" width=\"390\" height=\"310\" /></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div><div class=\"video_inline_vititle\"></div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><b>{$row_video['title']}</b></a>";

                                } else {

                                    $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";

                                }

                                $resLinkTitle = '';

                                //Музыка
                            } elseif($attach_type[0] == 'audio'){
                                $data = explode('_', $attach_type[1]);
                                $audioId = intval($data[0]);
                                $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM
						        `".PREFIX."_audio` WHERE id = '{$audioId}'");
                                if($row_audio){
                                    $stime = gmdate("i:s", $row_audio['duration']);
                                    if(!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                                    if(!$row_audio['title']) $row_audio['title'] = 'Без названия';
                                    $plname = 'wall';
                                    if($row_audio['oid'] != $user_info['user_id']) $q_s = <<<HTML
                                    <div class="audioSettingsBut"><li class="icon-plus-6"
                                    onClick="gSearch.addAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}')"
                                    onmouseover="showTooltip(this, {text: 'Добавить в мой список', shift: [6,5,0]});"
                                    id="no_play"></li><div class="clear"></div></div>
                                    HTML;
                                    else $q_s = '';
                                    $qauido = "<div class=\"audioPage audioElem search search_item\"
                                    id=\"audio_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"
                                    onclick=\"playNewAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}', event);\"><div
                                    class=\"area\"><table cellspacing=\"0\" cellpadding=\"0\"
                                    width=\"100%\"><tbody><tr><td><div class=\"audioPlayBut new_play_btn\"><div
                                    class=\"bl\"><div class=\"figure\"></div></div></div><input type=\"hidden\"
                                    value=\"{$row_audio['url']},{$row_audio['duration']},page\"
                                    id=\"audio_url_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"></td><td
                                    class=\"info\"><div class=\"audioNames\" style=\"width: 275px;\"><b class=\"author\"
                                    onclick=\"Page.Go('/?go=search&query=&type=5&q='+this.innerHTML);\"
                                    id=\"artist\">{$row_audio['artist']}</b> – <span class=\"name\"
                                    id=\"name\">{$row_audio['title']}</span> <div class=\"clear\"></div></div><div
                                    class=\"audioElTime\"
                                    id=\"audio_time_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\">{$stime}</div>{$q_s}</td
                                    ></tr></tbody></table><div id=\"player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"
                                    class=\"audioPlayer player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\" border=\"0\"
                                    cellpadding=\"0\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tbody><tr><td
                                    style=\"width: 100%;\"><div class=\"progressBar fl_l\" style=\"width: 100%;\"
                                    onclick=\"cancelEvent(event);\" onmousedown=\"audio_player.progressDown(event, this);\"
                                    id=\"no_play\" onmousemove=\"audio_player.playerPrMove(event, this)\"
                                    onmouseout=\"audio_player.playerPrOut()\"><div class=\"audioTimesAP\"
                                    id=\"main_timeView\"><div class=\"audioTAP_strlka\">100%</div></div><div
                                    class=\"audioBGProgress\"></div><div class=\"audioLoadProgress\"></div><div
                                    class=\"audioPlayProgress\" id=\"playerPlayLine\"><div
                                    class=\"audioSlider\"></div></div></div></td><td><div class=\"audioVolumeBar fl_l ml-2\"
                                    onclick=\"cancelEvent(event);\" onmousedown=\"audio_player.volumeDown(event, this);\"
                                    id=\"no_play\"><div class=\"audioTimesAP\"><div
                                    class=\"audioTAP_strlka\">100%</div></div><div class=\"audioBGProgress\"></div><div
                                    class=\"audioPlayProgress\" id=\"playerVolumeBar\"><div
                                    class=\"audioSlider\"></div></div></div> </td></tr></tbody></table></div></div></div>";
                                    $attach_result .= $qauido;
                                }
                                $resLinkTitle = '';
                                //Смайлик
                            } elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';

                                $resLinkTitle = '';

                                //Если ссылка
                            } elseif($attach_type[0] == 'link' AND preg_match('/http:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1 AND stripos(str_replace('http://www.', 'http://', $attach_type[1]), $config['home_url']) === false){
                                $count_num = count($attach_type);
                                $domain_url_name = explode('/', $attach_type[1]);
                                $rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);

                                $attach_type[3] = stripslashes($attach_type[3]);
                                $attach_type[3] = iconv_substr($attach_type[3], 0, 200, 'utf-8');

                                $attach_type[2] = stripslashes($attach_type[2]);
                                $str_title = iconv_substr($attach_type[2], 0, 55, 'utf-8');

                                if(stripos($attach_type[4], '/uploads/attach/') === false){
                                    $attach_type[4] = '/images/no_ava_groups_100.gif';
                                    $no_img = false;
                                } else
                                    $no_img = true;

                                if(!$attach_type[3]) $attach_type[3] = '';

                                if($no_img AND $attach_type[2]){
                                    if($row_wall['tell_comm']) $no_border_link = 'border:0px';

                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="'.$no_border_link.'"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'" /></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                } else if($attach_type[1] AND $attach_type[2]){
                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class="clear"></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                }

                                $cnt_attach_link++;

                                //Если документ
                            } elseif($attach_type[0] == 'doc'){

                                $doc_id = intval($attach_type[1]);

                                $row_doc = $db->super_query("SELECT dname, dsize FROM `".PREFIX."_doc` WHERE did = '{$doc_id}'", false, "wall/doc{$doc_id}");

                                if($row_doc){

                                    $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row_wall['id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row_wall['id'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                    $cnt_attach++;
                                }

                                //Если опрос
                            } elseif($attach_type[0] == 'vote'){

                                $vote_id = intval($attach_type[1]);

                                $row_vote = $db->super_query("SELECT title, answers, answer_num FROM `".PREFIX."_votes` WHERE id = '{$vote_id}'", false, "votes/vote_{$vote_id}");

                                if($vote_id){

                                    $checkMyVote = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$vote_id}'", false, "votes/check{$user_id}_{$vote_id}");

                                    $row_vote['title'] = stripslashes($row_vote['title']);

                                    if(!$row_wall['text'])
                                        $row_wall['text'] = $row_vote['title'];

                                    $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                    $max = $row_vote['answer_num'];

                                    $sql_answer = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `".PREFIX."_votes_result` WHERE vote_id = '{$vote_id}' GROUP BY answer", 1, "votes/vote_answer_cnt_{$vote_id}");
                                    $answer = array();
                                    foreach($sql_answer as $row_answer){

                                        $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                                    }

                                    $attach_result .= "<div class=\"clear\" style=\"height:10px\"></div><div id=\"result_vote_block{$vote_id}\"><div class=\"wall_vote_title\">{$row_vote['title']}</div>";

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
									</div><div class=\"clear\"></div>";

                                        }

                                    }
                                    $titles = array('человек', 'человека', 'человек');//fave
                                    if($row_vote['answer_num']) $answer_num_text = Gramatic::declOfNum($row_vote['answer_num'], $titles);
                                    else $answer_num_text = 'человек';

                                    if($row_vote['answer_num'] <= 1) $answer_text2 = 'Проголосовал';
                                    else $answer_text2 = 'Проголосовало';

                                    $attach_result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"clear\" style=\"margin-top:10px\"></div></div>";

                                }

                            } else

                                $attach_result .= '';

                        }

                        if($resLinkTitle AND $row_wall['text'] == $resLinkUrl OR !$row_wall['text'])
                            $row_wall['text'] = $resLinkTitle.$attach_result;
                        else if($attach_result)
                            $row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_wall['text']).$attach_result;
                        else
                            $row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_wall['text']);
                    } else
                        $row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_wall['text']);

                    $resLinkTitle = '';

                    //Если это запись с "рассказать друзьям"
                    if($row_wall['tell_uid']){
                        if($row_wall['public'])
                            $rowUserTell = $db->super_query("SELECT title, photo FROM `".PREFIX."_communities` WHERE id = '{$row_wall['tell_uid']}'", false, "wall/group{$row_wall['tell_uid']}");
                        else
                            $rowUserTell = $db->super_query("SELECT user_search_pref, user_photo FROM `".PREFIX."_users` WHERE user_id = '{$row_wall['tell_uid']}'");

                        if(date('Y-m-d', $row_wall['tell_date']) == date('Y-m-d', $server_time))
                            $dateTell = langdate('сегодня в H:i', $row_wall['tell_date']);
                        elseif(date('Y-m-d', $row_wall['tell_date']) == date('Y-m-d', ($server_time-84600)))
                            $dateTell = langdate('вчера в H:i', $row_wall['tell_date']);
                        else
                            $dateTell = langdate('j F Y в H:i', $row_wall['tell_date']);

                        if($row_wall['public']){
                            $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                            $tell_link = 'public';
                            if($rowUserTell['photo'])
                                $avaTell = '/uploads/groups/'.$row_wall['tell_uid'].'/50_'.$rowUserTell['photo'];
                            else
                                $avaTell = '/images/no_ava_50.png';
                        } else {
                            $tell_link = 'u';
                            if($rowUserTell['user_photo'])
                                $avaTell = '/uploads/users/'.$row_wall['tell_uid'].'/50_'.$rowUserTell['user_photo'];
                            else
                                $avaTell = '/images/no_ava_50.png';
                        }

                        if($row_wall['tell_comm']) $border_tell_class = 'wall_repost_border'; else $border_tell_class = 'wall_repost_border2';

                        $row_wall['text'] = <<<HTML
                        {$row_wall['tell_comm']}
                        <div class="{$border_tell_class}">
                        <div class="wall_tell_info"><div class="wall_tell_ava"><a href="/{$tell_link}{$row_wall['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30" /></a></div><div class="wall_tell_name"><a href="/{$tell_link}{$row_wall['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class="wall_tell_date">{$dateTell}</div></div>{$row_wall['text']}
                        <div class="clear"></div>
                        </div>
                        HTML;
                    }

                    $tpl->set('{text}', stripslashes($row_wall['text']));
                    $tpl->set('{name}', $row_wall['title']);

                    $tpl->set('{user-id}', $row_wall['public_id']);
                    if($row_wall['adres']) $tpl->set('{adres-id}', $row_wall['adres']);
                    else $tpl->set('{adres-id}', 'public'.$row_wall['public_id']);

                    $date = megaDate(strtotime($row_wall['add_date']));
                    $tpl->set('{date}', $date);

                    if($row_wall['photo'])
                        $tpl->set('{ava}', '/uploads/groups/'.$row_wall['public_id'].'/50_'.$row_wall['photo']);
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');

                    //Мне нравится
                    if(stripos($row_wall['likes_users'], "u{$user_id}|") !== false){
                        $tpl->set('{yes-like}', 'public_wall_like_yes');
                        $tpl->set('{yes-like-color}', 'public_wall_like_yes_color');
                        $tpl->set('{like-js-function}', 'groups.wall_remove_like('.$row_wall['id'].', '.$user_id.')');
                    } else {
                        $tpl->set('{yes-like}', '');
                        $tpl->set('{yes-like-color}', '');
                        $tpl->set('{like-js-function}', 'groups.wall_add_like('.$row_wall['id'].', '.$user_id.')');
                    }

                    if($row_wall['likes_num']){
                        $tpl->set('{likes}', $row_wall['likes_num']);
                        $tpl->set('{likes-text}', '<span id="like_text_num'.$row_wall['id'].'">'.$row_wall['likes_num'].'</span> '.Gramatic::declOfNum($row_wall['likes_num'], 'like'));
                    } else {
                        $tpl->set('{likes}', '');
                        $tpl->set('{likes-text}', '<span id="like_text_num'.$row_wall['id'].'">0</span> человеку');
                    }

                    //Выводим информцию о том кто смотрит страницу для себя
                    $tpl->set('{viewer-id}', $user_id);
                    if($user_info['user_photo'])
                        $tpl->set('{viewer-ava}', '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
                    else
                        $tpl->set('{viewer-ava}', '/images/no_ava_50.png');

                    //Админ
                    if($public_admin){
                        $tpl->set('[owner]', '');
                        $tpl->set('[/owner]', '');
                    } else
                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                    //Если есть комменты к записи, то выполняем след. действия / Приватность
                    if($row_wall['fasts_num'])
                        $tpl->set_block("'\\[comments-link\\](.*?)\\[/comments-link\\]'si","");
                    else {
                        $tpl->set('[comments-link]', '');
                        $tpl->set('[/comments-link]', '');
                    }

                    $tpl->set('{public-id}', $row['id']);

                    //Приватность комментирования записей
                    if($row_wall['comments'] OR $public_admin){
                        $tpl->set('[privacy-comment]', '');
                        $tpl->set('[/privacy-comment]', '');
                    } else
                        $tpl->set_block("'\\[privacy-comment\\](.*?)\\[/privacy-comment\\]'si","");

                    $tpl->set('[record]', '');
                    $tpl->set('[/record]', '');
                    $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");

                    $tpl->compile($compile);

                    //Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
                    if($row_wall['comments'] OR $public_admin){
                        if($row_wall['fasts_num']){

                            //Помещаем все комменты в id wall_fast_block_{id} это для JS
                            $tpl->result[$compile] .= '<div id="wall_fast_block_'.$row_wall['id'].'" class="public_wall_rec_comments">';

                            if($row_wall['fasts_num'] > 3)
                                $comments_limit = $row_wall['fasts_num']-3;
                            else
                                $comments_limit = 0;

                            $sql_comments = $db->super_query("SELECT tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$row_wall['id']}' ORDER by `add_date` ASC LIMIT {$comments_limit}, 3", 1);

                            //Загружаем кнопку "Показать N запсии"
                            $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                            $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
                            $tpl->set('{gram-record-all-comm}', Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles1).' '.($row_wall['fasts_num']-3).' '.Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles2));
                            if($row_wall['fasts_num'] < 4)
                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                            else {
                                $tpl->set('{rec-id}', $row_wall['id']);
                                $tpl->set('[all-comm]', '');
                                $tpl->set('[/all-comm]', '');
                            }
                            $tpl->set('{public-id}', $row['id']);
                            $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                            $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                            $tpl->compile($compile);

                            //Сообственно выводим комменты
                            foreach($sql_comments as $row_comments){
                                $tpl->set('{public-id}', $row['id']);
                                $tpl->set('{name}', $row_comments['user_search_pref']);
                                if($row_comments['user_photo'])
                                    $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
                                else
                                    $tpl->set('{ava}', '/images/no_ava_50.png');

                                $tpl->set('{rec-id}', $row_wall['id']);
                                $tpl->set('{comm-id}', $row_comments['id']);
                                $tpl->set('{user-id}', $row_comments['public_id']);

                                $expBR2 = explode('<br />', $row_comments['text']);
                                $textLength2 = count($expBR2);
                                $strTXT2 = strlen($row_comments['text']);
                                if($textLength2 > 6 OR $strTXT2 > 470)
                                    $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                                //Обрабатываем ссылки
                                $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comments['text']);

                                $tpl->set('{text}', stripslashes($row_comments['text']));

                                $date = megaDate(strtotime($row_comments['add_date']));
                                $tpl->set('{date}', $date);
                                if($public_admin OR $user_id == $row_comments['public_id']){
                                    $tpl->set('[owner]', '');
                                    $tpl->set('[/owner]', '');
                                } else
                                    $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                                if($user_id == $row_comments['public_id'])

                                    $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");

                                else {

                                    $tpl->set('[not-owner]', '');
                                    $tpl->set('[/not-owner]', '');

                                }

                                $tpl->set('[comment]', '');
                                $tpl->set('[/comment]', '');
                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                $tpl->compile($compile);
                            }

                            //Загружаем форму ответа
                            $tpl->set('{rec-id}', $row_wall['id']);
                            $tpl->set('{user-id}', $row_wall['public_id']);
                            $tpl->set('[comment-form]', '');
                            $tpl->set('[/comment-form]', '');
                            $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                            $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                            $tpl->compile($compile);

                            //Закрываем блок для JS
                            $tpl->result[$compile] .= '</div>';
                        }
                    }
                }

                //Если страница вывзана через "к предыдущим записям"
                if($page_cnt){
                    Tools::AjaxTpl($tpl);
                    exit;
                }

                $tpl->load_template('public/main.tpl');

                $tpl->set('{title}', stripslashes($row['title']));

                $config = include __DIR__.'/data/config.php';

                if($row['photo']){

                    //FOR MOBILE VERSION 1.0
                    if($config['temp'] == 'mobile')
                        $row['photo'] = '50_'.$row['photo'];

                    $tpl->set('{photo}', "/uploads/groups/{$row['id']}/{$row['photo']}");
                    $tpl->set('{display-ava}', '');
                } else {

                    //FOR MOBILE VERSION 1.0
                    if($config['temp'] == 'mobile')

                        $tpl->set('{photo}', "/images/no_ava_50.png");

                    else

                        $tpl->set('{photo}', "/images/no_ava.gif");

                    $tpl->set('{display-ava}', 'no_display');
                }

                if($row['descr'])
                    $tpl->set('{descr-css}', '');
                else
                    $tpl->set('{descr-css}', 'no_display');

                $tpl->set('{edit-descr}', Validation::myBrRn(stripslashes($row['descr'])));

                //КНопка Показать полностью..
                $expBR = explode('<br />', $row['descr']);
                $textLength = count($expBR);
                $strTXT = strlen($row['descr']);
                if($textLength > 9 OR $strTXT > 600)
                    $row['descr'] = '<div class="wall_strlen" id="hide_wall_rec'.$row['id'].'">'.$row['descr'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row['id'].', this.id)" id="hide_wall_rec_lnk'.$row['id'].'">Показать полностью..</div>';

                $tpl->set('{descr}', stripslashes($row['descr']));

                $titles = array('подписчик', 'подписчика', 'подписчиков');//subscribers
                $tpl->set('{num}', '<span id="traf">'.$row['traf'].'</span> '.Gramatic::declOfNum($row['traf'], $titles));
                if($row['traf']){
                    $titles = array('человек', 'человека', 'человек');//subscribers2
                    $tpl->set('{num-2}', '<a href="/public'.$row['id'].'" onClick="groups.all_people(\''.$row['id'].'\'); return false">'.$row['traf'].' '.Gramatic::declOfNum($row['traf'], 'subscribers2').'</a>');
                    $tpl->set('{no-users}', '');
                } else {
                    $tpl->set('{num-2}', '<span class="color777">Вы будете первым.</span>');
                    $tpl->set('{no-users}', 'no_display');
                }

                //Права админа
                if($public_admin){
                    $tpl->set('[admin]', '');
                    $tpl->set('[/admin]', '');
                    $tpl->set_block("'\\[not-admin\\](.*?)\\[/not-admin\\]'si","");
                } else {
                    $tpl->set('[not-admin]', '');
                    $tpl->set('[/not-admin]', '');
                    $tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");
                }

                //Проверка подписан юзер или нет
                if(stripos($row['ulist'], "|{$user_id}|") !== false)
                    $tpl->set('{yes}', 'no_display');
                else
                    $tpl->set('{no}', 'no_display');

                //Контакты
                if($row['feedback']){
                    $tpl->set('[yes]', '');
                    $tpl->set('[/yes]', '');
                    $tpl->set_block("'\\[no\\](.*?)\\[/no\\]'si","");
                    $titles = array('контакт', 'контакта', 'контактов');//feedback
                    $tpl->set('{num-feedback}', '<span id="fnumu">'.$row['feedback'].'</span> '.Gramatic::declOfNum($row['feedback'], $titles));
                    $sql_feedbackusers = $db->super_query("SELECT tb1.fuser_id, office, tb2.user_search_pref, user_photo FROM `".PREFIX."_communities_feedback` tb1, `".PREFIX."_users` tb2 WHERE tb1.cid = '{$row['id']}' AND tb1.fuser_id = tb2.user_id ORDER by `fdate` ASC LIMIT 0, 5", 1);
                    $feedback_users = '';
                    foreach($sql_feedbackusers as $row_feedbackusers){
                        if($row_feedbackusers['user_photo']) $ava = "/uploads/users/{$row_feedbackusers['fuser_id']}/50_{$row_feedbackusers['user_photo']}";
                        else $ava = "/images/no_ava_50.png";
                        $row_feedbackusers['office'] = stripslashes($row_feedbackusers['office']);
                        $feedback_users .= "<div class=\"onesubscription onesubscriptio2n\" id=\"fb{$row_feedbackusers['fuser_id']}\"><a href=\"/u{$row_feedbackusers['fuser_id']}\" onClick=\"Page.Go(this.href); return false\"><img src=\"{$ava}\" alt=\"\" /><div class=\"onesubscriptiontitle\">{$row_feedbackusers['user_search_pref']}</div></a><div class=\"nesubscriptstatus\">{$row_feedbackusers['office']}</div></div>";
                    }
                    $tpl->set('{feedback-users}', $feedback_users);
                    $tpl->set('[feedback]', '');
                    $tpl->set('[/feedback]', '');
                } else {
                    $tpl->set('[no]', '');
                    $tpl->set('[/no]', '');
                    $tpl->set_block("'\\[yes\\](.*?)\\[/yes\\]'si","");
                    $tpl->set('{feedback-users}', '');
                    if($public_admin){
                        $tpl->set('[feedback]', '');
                        $tpl->set('[/feedback]', '');
                    } else
                        $tpl->set_block("'\\[feedback\\](.*?)\\[/feedback\\]'si","");
                }

                //Выводим подписчиков
                $sql_users = $db->super_query("SELECT tb1.user_id, tb2.user_name, user_lastname, user_photo FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.friend_id = '{$row['id']}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2 ORDER by rand() LIMIT 0, 6", 1);
                foreach($sql_users as $row_users){
                    if($row_users['user_photo']) $ava = "/uploads/users/{$row_users['user_id']}/50_{$row_users['user_photo']}";
                    else $ava = "/images/no_ava_50.png";
                    $users .= "<div class=\"onefriend oneusers\" id=\"subUser{$row_users['user_id']}\"><a href=\"/u{$row_users['user_id']}\" onClick=\"Page.Go(this.href); return false\"><img src=\"{$ava}\"  style=\"margin-bottom:3px\" /></a><a href=\"/u{$row_users['user_id']}\" onClick=\"Page.Go(this.href); return false\">{$row_users['user_name']}<br /><span>{$row_users['user_lastname']}</span></a></div>";
                }
                $tpl->set('{users}', $users);

                $tpl->set('{id}', $row['id']);

                $date = megaDate(strtotime($row['date']), 1, 1);
                $tpl->set('{date}', $date);

                //Комментарии включены
                if($row['comments'])
                    $tpl->set('{settings-comments}', 'comments');
                else
                    $tpl->set('{settings-comments}', 'none');

                //Выводим админов при ред. страницы
                if($public_admin){
                    $admins_arr = str_replace('|', '', explode('u', $row['admin']));
                    foreach($admins_arr as $admin_id){
                        if($admin_id){
                            $row_admin = $db->super_query("SELECT user_search_pref, user_photo FROM `".PREFIX."_users` WHERE user_id = '{$admin_id}'");
                            if($row_admin['user_photo']) $ava_admin = "/uploads/users/{$admin_id}/50_{$row_admin['user_photo']}";
                            else $ava_admin = "/images/no_ava_50.png";
                            if($admin_id != $row['real_admin']) $admin_del_href = "<a href=\"/\" onClick=\"groups.deladmin('{$row['id']}', '{$admin_id}'); return false\"><small>Удалить</small></a>";
                            $adminO .= "<div class=\"public_oneadmin\" id=\"admin{$admin_id}\"><a href=\"/u{$admin_id}\" onClick=\"Page.Go(this.href); return false\"><img src=\"{$ava_admin}\" align=\"left\" width=\"32\" /></a><a href=\"/u{$admin_id}\" onClick=\"Page.Go(this.href); return false\">{$row_admin['user_search_pref']}</a><br />{$admin_del_href}</div>";
                        }
                    }

                    $tpl->set('{admins}', $adminO);
                }

                $tpl->set('{records}', $tpl->result['wall']);

                //Стена
                if($row['rec_num'] > 10)
                    $tpl->set('{wall-page-display}', '');
                else
                    $tpl->set('{wall-page-display}', 'no_display');
                $titles = array('запись', 'записи', 'записей');//rec
                if($row['rec_num'])
                    $tpl->set('{rec-num}', '<b id="rec_num">'.$row['rec_num'].'</b> '.Gramatic::declOfNum($row['rec_num'], $titles));
                else {
                    $tpl->set('{rec-num}', '<b id="rec_num">Нет записей</b>');
                    if($public_admin)
                        $tpl->set('{records}', '<div class="wall_none" style="border-top:0px">Новостей пока нет.</div>');
                    else
                        $tpl->set('{records}', '<div class="wall_none">Новостей пока нет.</div>');
                }

                //Выводим информцию о том кто смотрит страницу для себя
                $tpl->set('{viewer-id}', $user_id);

                if(!$row['adres']) $row['adres'] = 'public'.$row['id'];
                $tpl->set('{adres}', $row['adres']);

                //Аудиозаписи
                if($row['audio_num']){
                    $sql_audios = $db->super_query("SELECT url, artist, name FROM `".PREFIX."_communities_audio` WHERE public_id = '{$row['id']}' ORDER by `adate` DESC LIMIT 0, 3", 1, "groups/audio{$row['id']}");
                    $jid = 0;
                    $audios = '';
                    foreach($sql_audios as $row_audios){
                        $jid++;

                        $row_audios['artist'] = stripslashes($row_audios['artist']);
                        $row_audios['name'] = stripslashes($row_audios['name']);

                        $audios .= "<div class=\"audio_onetrack\"><div class=\"audio_playic cursor_pointer fl_l\" onClick=\"music.newStartPlay('{$jid}')\" id=\"icPlay_{$jid}\"></div><span id=\"music_{$jid}\" data=\"{$row_audios['url']}\"><a href=\"/?go=search&query={$row_audios['artist']}&type=5\" onClick=\"Page.Go(this.href); return false\"><b><span id=\"artis{aid}\">{$row_audios['artist']}</span></b></a> &ndash; <span id=\"name{aid}\">{$row_audios['name']}</span></span><div id=\"play_time{$jid}\" class=\"color777 fl_r no_display\" style=\"margin-top:2px;margin-right:5px\"></div> <div class=\"clear\"></div><div class=\"player_mini_mbar fl_l no_display\" id=\"ppbarPro{$jid}\" style=\"width:178px\"></div> </div>";

                    }

                    $tpl->set('{audios}', $audios);
                    $tpl->set('{audio-num}', $row['audio_num']);
                    $tpl->set('[audios]', '');
                    $tpl->set('[/audios]', '');
                    $tpl->set('[yesaudio]', '');
                    $tpl->set('[/yesaudio]', '');
                    $tpl->set_block("'\\[noaudio\\](.*?)\\[/noaudio\\]'si","");

                } else {

                    $tpl->set('{audios}', '');
                    $tpl->set('[noaudio]', '');
                    $tpl->set('[/noaudio]', '');
                    $tpl->set_block("'\\[yesaudio\\](.*?)\\[/yesaudio\\]'si","");

                    if($public_admin){
                        $tpl->set('[audios]', '');
                        $tpl->set('[/audios]', '');
                    } else
                        $tpl->set_block("'\\[audios\\](.*?)\\[/audios\\]'si","");

                }

                //Обсуждения
                if($row['discussion']){

                    $tpl->set('{settings-discussion}', 'discussion');
                    $tpl->set('[discussion]', '');
                    $tpl->set('[/discussion]', '');

                } else {

                    $tpl->set('{settings-discussion}', 'none');
                    $tpl->set_block("'\\[discussion\\](.*?)\\[/discussion\\]'si","");

                }

                if(!$row['forum_num']) $row['forum_num'] = '';
                $tpl->set('{forum-num}', $row['forum_num']);

                if($row['forum_num'] AND $row['discussion']){

                    $sql_forum = $db->super_query("SELECT fid, title, lastuser_id, lastdate, msg_num FROM `".PREFIX."_communities_forum` WHERE public_id = '{$row['id']}' ORDER by `fixed` DESC, `lastdate` DESC, `fdate` DESC LIMIT 0, 5", 1, "groups_forum/forum{$row['id']}");

                    $thems = '';

                    foreach($sql_forum as $row_forum){

                        $row_last_user = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '{$row_forum['lastuser_id']}'");
                        $last_userX = explode(' ', $row_last_user['user_search_pref']);
                        $row_last_user['user_search_pref'] = gramatikName($last_userX[0]).' '.gramatikName($last_userX[1]);

                        $row_forum['title'] = stripslashes($row_forum['title']);

                        $titles = array('сообщение', 'сообщения', 'сообщений');//msg
                        $msg_num = $row_forum['msg_num'].' '.Gramatic::declOfNum($row_forum['msg_num'], $titles);

                        $last_date = megaDate($row_forum['lastdate']);

                        $thems .= "<div class=\"forum_bg\"><div class=\"forum_title cursor_pointer\" onClick=\"Page.Go('/forum{$row['id']}?act=view&id={$row_forum['fid']}'); return false\">{$row_forum['title']}</div><div class=\"forum_bottom\">{$msg_num}. Последнее от <a href=\"/u{$row_forum['lastuser_id']}\" onClick=\"Page.Go(this.href); return false\">{$row_last_user['user_search_pref']}</a>, {$last_date}</div></div>";

                    }

                    $tpl->set('{thems}', $thems);

                } else
                    $tpl->set('{thems}', '<div class="wall_none">В сообществе ещё нет тем.</div>');

                //Статус
                $tpl->set('{status-text}', stripslashes($row['status_text']));

                if($row['status_text']){

                    $tpl->set('[status]', '');
                    $tpl->set('[/status]', '');
                    $tpl->set_block("'\\[no-status\\](.*?)\\[/no-status\\]'si","");

                } else {

                    $tpl->set_block("'\\[status\\](.*?)\\[/status\\]'si","");
                    $tpl->set('[no-status]', '');
                    $tpl->set('[/no-status]', '');

                }

                $tpl->set('{web}', $row['web']);

                if($row['web']){

                    $tpl->set('[web]', '');
                    $tpl->set('[/web]', '');

                } else

                    $tpl->set_block("'\\[web\\](.*?)\\[/web\\]'si","");

                //Видеозаписи
                if($row['videos_num']){

                    $sql_videos = $db->super_query("SELECT id, title, photo, add_date, comm_num, owner_user_id FROM `".PREFIX."_videos` WHERE public_id = '{$row['id']}' ORDER by `add_date` DESC LIMIT 0, 2", 1, "groups/video{$row['id']}");

                    $videos = '';

                    foreach($sql_videos as $row_video){

                        $row_video['title'] = stripslashes($row_video['title']);
                        $date_video = megaDate(strtotime($row_video['add_date']));
                        $titles = array('комментарий', 'комментария', 'комментариев');//comments
                        $comm_num = $row_video['comm_num'].' '.Gramatic::declOfNum($row_video['comm_num'], $titles);

                        $videos .= "
                            <div class=\"profile_one_video\"><a href=\"/video{$row_video['owner_user_id']}_{$row_video['id']}\" onClick=\"videos.show({$row_video['id']}, this.href, '/{$row['adres']}'); return false\"><img src=\"{$row_video['photo']}\" alt=\"\" width=\"185\" /></a><div class=\"video_profile_title\"><a href=\"/video{$row_video['owner_user_id']}_{$row_video['id']}\" onClick=\"videos.show({$row_video['id']}, this.href, '/{$row['adres']}'); return false\">{$row_video['title']}</a></div><div class=\"nesubscriptstatus\">{$date_video} - <a href=\"/video{$row_video['owner_user_id']}_{$row_video['id']}\" onClick=\"videos.show({$row_video['id']}, this.href, '/{$row['adres']}'); return false\">{$comm_num}</a></div></div>
				        ";

                    }

                    $tpl->set('{videos}', $videos);
                    $tpl->set('{videos-num}', $row['videos_num']);
                    $tpl->set('[videos]', '');
                    $tpl->set('[/videos]', '');
                    $tpl->set('[yesvideo]', '');
                    $tpl->set('[/yesvideo]', '');
                    $tpl->set_block("'\\[novideo\\](.*?)\\[/novideo\\]'si","");

                } else {

                    $tpl->set('{videos}', '');
                    $tpl->set('[novideo]', '');
                    $tpl->set('[/novideo]', '');
                    $tpl->set_block("'\\[yesvideo\\](.*?)\\[/yesvideo\\]'si","");

                    if($public_admin){

                        $tpl->set('[videos]', '');
                        $tpl->set('[/videos]', '');

                    } else
                        $tpl->set_block("'\\[videos\\](.*?)\\[/videos\\]'si","");

                }

                //Обложка
                if($row['photo']){

                    $avaImgIsinfo = getimagesize(ROOT_DIR."/uploads/groups/{$row['id']}/{$row['photo']}");

                    if($avaImgIsinfo[1] < 200){

                        $rForme = 230 - $avaImgIsinfo[1];

                        $ava_marg_top = 'style="margin-top:-'.$rForme.'px"';

                    }

                    $tpl->set('{cover-param-7}', $ava_marg_top);

                } else
                    $tpl->set('{cover-param-7}', "");

                if($row['cover']){

                    $imgIsinfo = getimagesize(ROOT_DIR."/uploads/groups/{$row['id']}/{$row['cover']}");

                    $tpl->set('{cover}', "/uploads/groups/{$row['id']}/{$row['cover']}");
                    $tpl->set('{cover-height}', $imgIsinfo[1]);
                    $tpl->set('{cover-param}', '');
                    $tpl->set('{cover-param-2}', 'no_display');
                    $tpl->set('{cover-param-3}', 'style="position:absolute;z-index:2;display:block;margin-left:397px"');
                    $tpl->set('{cover-param-4}', 'style="cursor:default"');
                    $tpl->set('{cover-param-5}', 'style="top:-'.$row['cover_pos'].'px;position:relative"');
                    $tpl->set('{cover-pos}', $row['cover_pos']);

                    $tpl->set('[cover]', '');
                    $tpl->set('[/cover]', '');

                } else {

                    $tpl->set('{cover}', "");
                    $tpl->set('{cover-param}', 'no_display');
                    $tpl->set('{cover-param-2}', '');
                    $tpl->set('{cover-param-3}', '');
                    $tpl->set('{cover-param-4}', '');
                    $tpl->set('{cover-param-5}', '');
                    $tpl->set('{cover-pos}', '');
                    $tpl->set_block("'\\[cover\\](.*?)\\[/cover\\]'si","");

                }

                //Записываем в статистику "Уникальные посетители"
                $stat_date = date('Y-m-d', $server_time);
                $stat_x_date = date('Y-m', $server_time);
                $stat_date = strtotime($stat_date);
                $stat_x_date = strtotime($stat_x_date);

                $check_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_stats` WHERE gid = '{$row['id']}' AND date = '{$stat_date}'");
                $check_user_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_stats_log` WHERE gid = '{$row['id']}' AND user_id = '{$user_info['user_id']}' AND date = '{$stat_date}' AND act = '1'");

                if(!$check_user_stat['cnt']){

                    if($check_stat['cnt']){

                        $db->query("UPDATE `".PREFIX."_communities_stats` SET cnt = cnt + 1 WHERE gid = '{$row['id']}' AND date = '{$stat_date}'");

                    } else {

                        $db->query("INSERT INTO `".PREFIX."_communities_stats` SET gid = '{$row['id']}', date = '{$stat_date}', cnt = '1', date_x = '{$stat_x_date}'");

                    }

                    $db->query("INSERT INTO `".PREFIX."_communities_stats_log` SET user_id = '{$user_info['user_id']}', date = '{$stat_date}', gid = '{$row['id']}', act = '1'");

                }

                //Записываем в статистику "Просмотры"
                $db->query("UPDATE `".PREFIX."_communities_stats` SET hits = hits + 1 WHERE gid = '{$row['id']}' AND date = '{$stat_date}'");

                $tpl->compile('content');
            } else {
                $user_speedbar = $lang['no_infooo'];
                msgbox('', $lang['no_upage'], 'info');
            }

            $tpl->clear();
            $db->free();
        } else {
            $user_speedbar = $lang['no_infooo'];
            msgbox('', $lang['not_logged'], 'info');
        }

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}