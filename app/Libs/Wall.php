<?php

namespace App\Libs;


use App\Models\News;
use App\Models\Profile;
use Sura\Libs\Db;
use Sura\Libs\Gramatic;
use Sura\Libs\Registry;
use Sura\Libs\Settings;
use Sura\Libs\Tools;

class Wall
{
    /**
     * @param array $query
     * @return array
     */
    public static function build(array $query) : array
    {
        $db = Db::getDB();
        $user_info = Registry::get('user_info');
        $user_id = $user_info['user_id'];
        $config = Settings::loadsettings();
//        $server_time = Tools::time();

        foreach($query as $key => $row_wall){

            $query[$key]['record'] = true;
            $query[$key]['comment'] = false;
            $query[$key]['comment_form'] = false;
            $query[$key]['all_comm'] = false;

            /**
             * Определяем юзер или сообщество
             * 1 - юзер
             * 2 - сообщество
             */
            if (isset($row_wall['author_user_id']) AND isset($row_wall['action_type']) == false){
                $query[$key]['action_type'] = $action_type = 1;
            }elseif(isset($row_wall['action_type']) == false){
                $query[$key]['action_type'] = $action_type = 2;
            }
            if (($row_wall['ac_id'])){
                $row_wall['id'] = $row_wall['ac_id'];
                $row_wall['text'] = $row_wall['action_text'];
                if ($row_wall['type'] == 11 || $row_wall['action_type'] == 11){
                    $row_wall['action_type'] = 2;
                    $row_wall['type'] = 2;
                    $query[$key]['action_type'] = $action_type = 2;
                    $row_wall['public_id'] = $row_wall['ac_user_id'];
                }
//                if ($row_wall['type'] == 1 || $row_wall['action_type'] == 1){
//                    $row_wall['action_type'] = 1;
//                    $row_wall['type'] = 1;
//                    $query[$key]['action_type'] = $action_type = 1;
////                    $row_wall['public_id'] = $row_wall['ac_user_id'];
//                }





            }


            /** id record */
            $query[$key]['rec_id'] = $row_wall['id'];

            /** address */
            if ($query[$key]['action_type'] == 1) {
                $query[$key]['address'] = 'u'.$row_wall['author_user_id'];
            }else{
                $query[$key]['address'] = 'public'.$row_wall['public_id'];
            }

            /** Закрепить запись */
            if($row_wall['fixed']){
                $query[$key]['styles_fasten'] = 'style="opacity:1"';
                $query[$key]['fasten_text'] ='Закрепленная запись';
                $query[$key]['function_fasten'] ='wall_unfasten';
            } else {
                $query[$key]['styles_fasten'] = true;
                $query[$key]['fasten_text'] ='Закрепить запись';
                $query[$key]['function_fasten'] ='wall_fasten';
            }

            /** КНопка Показать полностью.. $expBR */
//            $expBR = explode('<br />', $row_wall['text']);
//            $textLength = count($expBR);
            $textLength = substr_count($row_wall['text'], '<br />');
            $strTXT = strlen($row_wall['text']);
            if($textLength > 9 OR $strTXT > 600) {
                $row_wall['text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row_wall['id'] . '">' . $row_wall['text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row_wall['id'] . ', this.id)" id="hide_wall_rec_lnk' . $row_wall['id'] . '">Показать полностью..</div>';
            }

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
                    if($row_wall['tell_uid']) {
                        $globParId = $row_wall['tell_uid'];
                    }
                    else {
                        $globParId = $row_wall['public_id'];
                    }

                    if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$globParId}/photos/c_{$attach_type[1]}")){
                        if($cnt_attach < 2) {
                            $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$globParId}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$globParId}/photos/{$attach_type[1]}\" align=\"left\" /></div>";
                        }
                        else {
                            $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$globParId}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$globParId}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" />";
                        }

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

                            $video_id = (int)$attach_type[2];

                            $row_video = $db->super_query("SELECT video, title FROM `videos` WHERE id = '{$video_id}'", false, "wall/video{$video_id}");
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
                        $audioId = (int)$data[0];
                        $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM
						        `audio` WHERE id = '{$audioId}'");
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

                        $doc_id = (int)$attach_type[1];

                        $row_doc = $db->super_query("SELECT dname, dsize FROM `doc` WHERE did = '{$doc_id}'", false, "wall/doc{$doc_id}");

                        if($row_doc){

                            $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row_wall['id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row_wall['id'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                            $cnt_attach++;
                        }

                        //Если опрос
                    } elseif($attach_type[0] == 'vote'){

                        $vote_id = (int)$attach_type[1];

                        $row_vote = $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$vote_id}'", false, "votes/vote_{$vote_id}");

                        if($vote_id){

                            $checkMyVote = $db->super_query("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$vote_id}'", false, "votes/check{$user_id}_{$vote_id}");

                            $row_vote['title'] = stripslashes($row_vote['title']);

                            if(!$row_wall['text'])
                                $row_wall['text'] = $row_vote['title'];

                            $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                            $max = $row_vote['answer_num'];

                            $sql_answer = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$vote_id}' GROUP BY answer", 1, "votes/vote_answer_cnt_{$vote_id}");
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
            } else {
                $row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_wall['text']);
            }

            $resLinkTitle = '';

            //Если это запись с "рассказать друзьям"
            if($row_wall['tell_uid']){
                $Profile = new Profile;
                if($row_wall['public']) {
                    $rowUserTell = $Profile->user_tell_info($row_wall['tell_uid'], 2);
                }
                else {
                    $rowUserTell = $Profile->user_tell_info($row_wall['tell_uid'], 1);
                }

                if (is_int($row_wall['tell_date'])){
                    $dateTell = megaDate($row_wall['tell_date']);
                }else{
                    $dateTell = 'N/A';
                }

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

                if($row_wall['tell_comm']) {
                    $border_tell_class = 'wall_repost_border';
                } else {
                    $border_tell_class = 'wall_repost_border2';
                }

                $row_wall['text'] = <<<HTML
                        {$row_wall['tell_comm']}
                        <div class="{$border_tell_class}">
                        <div class="wall_tell_info">
                        <div class="wall_tell_ava">
                        <a href="/{$tell_link}{$row_wall['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30"  alt=""/></a></div>
                        <div class="wall_tell_name"><a href="/{$tell_link}{$row_wall['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div>
                        <div class="wall_tell_date">{$dateTell}</div></div>{$row_wall['text']}
                        <div class="clear"></div>
                        </div>
                        HTML;
            }

            //Выводим информцию о том кто смотрит страницу для себя
            $query[$key]['viewer_id'] =$user_id;
            if($user_info['user_photo']){
                $query[$key]['viewer_ava'] = '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo'];
            }
            else{
                $query[$key]['viewer_ava'] ='/images/no_ava_50.png';
            }

            if($row_wall['type']){
                $query[$key]['type'] = $row_wall['type'];
            }else{
                $query[$key]['type'] = '';
            }

            if ($action_type == 1){
                $query[$key]['text'] = stripslashes($row_wall['text']);
                $query[$key]['name'] = $row_wall['user_search_pref'];
                $query[$key]['user_id'] = $row_wall['author_user_id'];
                if(isset($row_wall['ac_user_id'])){
                    $query[$key]['user_id'] = $row_wall['id'];
                }
                $query[$key]['online'] = Tools::Online($row_wall['user_last_visit']);

                if($row_wall['user_photo']){
                    $query[$key]['ava'] = '/uploads/users/'.$row_wall['author_user_id'].'/50_'.$row_wall['user_photo'];
                }
                else{
                    $query[$key]['ava'] = '/images/no_ava_50.png';
                }

                if($row_wall['adres']) {
                    $query[$key]['adres_id'] = $row_wall['adres'];
                }
                else{
                    $query[$key]['adres_id'] ='u'.$row_wall['author_user_id'];
                }


                //Тег Owner означает показ записей только для владельца страницы или для того кто оставил запись
                if($user_id == $row_wall['author_user_id']){
                    $query[$key]['owner'] = true;
                } else{
                    $query[$key]['owner'] = false;
                }

                //fixme
                $id = null;

                //Показа кнопки "Рассказать др" только если это записи владельца стр.
                if($row_wall['author_user_id'] == $id AND $user_id != $id){
                    $query[$key]['author_user_id'] = true;
                } else{
                    $query[$key]['author_user_id'] = false;
                }

                //Если есть комменты к записи, то выполняем след. действия / Приватность
                if($row_wall['fasts_num']){
                    $query[$key]['if_comments'] = false;
                } else {
                    $query[$key]['if_comments'] = true;
                }

                $row = $db->super_query("SELECT user_privacy FROM `users` WHERE user_id = '{$row_wall['author_user_id']}'");
                if ($row['user_privacy']){
                    $user_privacy = xfieldsdataload($row['user_privacy']);
                }else{
                    $user_privacy = array();
                }

                $CheckFriends = Tools::CheckFriends($row_wall['author_user_id']);

                //Приватность комментирования записей
                if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $CheckFriends OR $user_id == $id){
                    $query[$key]['privacy_comment'] = true;
                } else{
                    $query[$key]['privacy_comment'] = false;
                }

                //Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
                if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $CheckFriends OR $user_id == $id){
                    if($row_wall['fasts_num']){

                        if($row_wall['fasts_num'] > 3) {
                            $comments_limit = $row_wall['fasts_num'] - 3;
                        }
                        else {
                            $comments_limit = 0;
                        }
                        $Profile = new Profile;

                        $sql_comments = $Profile->comments($row_wall['id'], $comments_limit);

                        //Загружаем кнопку "Показать N запсии"
//                        $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
//                        $titles2 = array('комментарий', 'комментария', 'комментариев');//comments

//                        $query[$key]['gram_record_all_comm'] = Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles1).' '.($row_wall['fasts_num']-3).' '.Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles2);
//                        $query[$key]['gram_record_all_comm'] = '';


                        /** @var  $num - BUGFIX */
                        $num = (int) $row_wall['fasts_num']-3;
                        if ($num < 0){
                            $num = 0;
                        }
                        $titles = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                        $prev = Gramatic::declOfNum($num, $titles);

                        $titles = array('комментарий', 'комментария', 'комментариев');//comments
                        $comments = Gramatic::declOfNum($num, $titles);

                        $params['gram_record_all_comm'] = $prev.' '.$num.' '.$comments;

                        if($row_wall['fasts_num'] < 4){
                            $query[$key]['all_comm_block'] = false;
                        }else {
                            $query[$key]['rec_id'] = $row_wall['id'];
                        }
                        $query[$key]['author_id'] = $id;

                        $query[$key]['record_block'] = false;
                        $query[$key]['comment_form_block'] = false;
                        $query[$key]['comment_block'] = false;

                        //Собственно выводим комменты
                        foreach($sql_comments as $key2 => $row_comments){
                            $sql_comments[$key2]['name'] = $row_comments['user_search_pref'];
                            if($row_comments['user_photo']){
                                $sql_comments[$key2]['ava'] = '/uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo'];
                            }else{
                                $sql_comments[$key2]['ava'] = '/images/no_ava_50.png';
                            }

                            $sql_comments[$key2]['rec_id'] = $row_wall['id'];
                            $sql_comments[$key2]['comm_id'] = $row_comments['id'];
                            $sql_comments[$key2]['user_id'] = $row_comments['author_user_id'];

//                            $expBR2 = explode('<br />', $row_comments['text']);
                            $textLength2 = substr_count($row_comments['text'], '<br />');
                            $strTXT2 = strlen($row_comments['text']);
                            if($textLength2 > 6 OR $strTXT2 > 470) {
                                $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row_comments['id'] . '" style="max-height:102px"">' . $row_comments['text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row_comments['id'] . ', this.id)" id="hide_wall_rec_lnk' . $row_comments['id'] . '">Показать полностью..</div>';
                            }

                            //Обрабатываем ссылки
                            $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

                            $sql_comments[$key2]['text'] = stripslashes($row_comments['text']);

                            $sql_comments[$key2]['date'] = megaDate($row_comments['add_date']);
                            if($user_id == $row_comments['author_user_id'] || $user_id == $id){
                                $sql_comments[$key2]['owner'] = true;
                            } else{
                                $sql_comments[$key2]['owner'] = false;
                            }

                            if($user_id == $row_comments['author_user_id']){
                                $sql_comments[$key2]['not_owner'] = false;
                            }else {
                                $sql_comments[$key2]['not_owner_block'] = true;
                            }

                            $query[$key]['comment'] = true;
                            $sql_comments[$key2]['record_block'] = false;
                            $sql_comments[$key2]['comment_form_block'] = false;
                            $sql_comments[$key2]['all_comm_block'] = false;
                        }

                        $query[$key]['comments'] = $sql_comments;

                        //Загружаем форму ответа
//                        $query[$key]['rec_id'] = $row_wall['id'];
//                        $query[$key]['author_id'] = $id;
//                        $query[$key]['comment_form_block'] = true;
//                        $query[$key]['record'] = false;
//                        $query[$key]['comment'] = false;
//                        $query[$key]['all_comm'] = false;
                    }
                }


            }
            /** $action_type == 2 */
            else{
                $query[$key]['text'] = stripslashes($row_wall['text']);
                $query[$key]['name'] = $row_wall['title'];

                $query[$key]['user_id'] = $row_wall['public_id'];
                if(isset($row_wall['ac_user_id'])){
                    $query[$key]['user_id'] = $row_wall['id'];
                }
                $query[$key]['online'] = '';

                if($row_wall['photo']){
                    $query[$key]['ava'] ='/uploads/groups/'.$row_wall['public_id'].'/50_'.$row_wall['photo'];
                }
                else{
                    $query[$key]['ava'] ='/images/no_ava_50.png';
                }

                if($row_wall['adres']) {
                    $query[$key]['adres_id'] = $row_wall['adres'];
                }
                else{
                    $query[$key]['adres_id'] ='public'.$row_wall['public_id'];
                }
                $query[$key]['public_id'] = $row_wall['public_id'];

                $row = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$row_wall['public_id']}'");
                if(stripos($row['admin'], "u{$user_id}|") !== false) {
                    $public_admin = true;
                }
                else {
                    $public_admin = false;
                }

                //Админ
                if($public_admin){
                    $query[$key]['owner'] = true;
                } else{
                    $query[$key]['owner'] = false;
                }

                //FIXME update
                //Показа кнопки "Рассказать др" только если это записи владельца стр.
                if($row_wall['author_user_id'] == $id AND $user_id != $id){
                    $query[$key]['author_user_id'] = true;
                } else{
                    $query[$key]['author_user_id'] = false;
                }

                //Если есть комменты к записи, то выполняем след. действия / Приватность
                if($row_wall['fasts_num']){
                    $query[$key]['if_comments'] = false;
                }
                else {
                    $query[$key]['if_comments'] = true;
                }


                //Приватность комментирования записей
                if($row_wall['comments'] OR $public_admin){
                    $query[$key]['privacy_comment'] = true;
                } else{
                    $query[$key]['privacy_comment'] = false;
                }

                //Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
                if($row_wall['comments'] OR $public_admin){
                    if($row_wall['fasts_num']){

                        //Помещаем все комменты в id wall_fast_block_{id} это для JS
//                            $tpl->result[$compile] .= '<div id="wall_fast_block_'.$row_wall['id'].'" class="public_wall_rec_comments">';

                        if($row_wall['fasts_num'] > 3) {
                            $comments_limit = $row_wall['fasts_num'] - 3;
                        }
                        else {
                            $comments_limit = 0;
                        }

                        $sql_comments = $db->super_query("SELECT tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `communities_wall` tb1, `users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$row_wall['id']}' ORDER by `add_date` ASC LIMIT {$comments_limit}, 3", true);

                        //Загружаем кнопку "Показать N запсии"
                        $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                        $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
                        $query[$key]['gram_record_all_comm'] = Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles1).' '.($row_wall['fasts_num']-3).' '.Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles2);
                        if($row_wall['fasts_num'] < 4){
                            $query[$key]['all_comm'] = false;
                        }
                        else {
                            $query[$key]['rec_id'] =$row_wall['id'];
                            $query[$key]['all_comm'] = true;
                        }
                        $query[$key]['public_id'] =$row['id'];
                        $query[$key]['record'] = false;
                        $query[$key]['comment_form'] = false;
                        $query[$key]['comment'] = false;

                        //Собственно выводим комменты
                        foreach($sql_comments as $key2 => $row_comments){
                            $sql_comments[$key2]['public_id'] =$row['id'];
                            $sql_comments[$key2]['name'] = $row_comments['user_search_pref'];
                            if($row_comments['user_photo']){
                                $sql_comments[$key2]['ava'] =$config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo'];
                            }
                            else{
                                $sql_comments[$key2]['ava'] ='/images/no_ava_50.png';
                            }

                            $sql_comments[$key2]['rec_id'] =$row_wall['id'];
                            $sql_comments[$key2]['comm_id'] =$row_comments['id'];
                            $sql_comments[$key2]['user_id'] = $row_comments['public_id'];

//                        $expBR2 = explode('<br />', $row_comments['text']);
//                        $textLength2 = count($expBR2);
                            $textLength2 = substr_count($row_comments['text'], '<br />');
                            $strTXT2 = strlen($row_comments['text']);
                            if($textLength2 > 6 OR $strTXT2 > 470) {
                                $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row_comments['id'] . '" style="max-height:102px"">' . $row_comments['text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row_comments['id'] . ', this.id)" id="hide_wall_rec_lnk' . $row_comments['id'] . '">Показать полностью..</div>';
                            }

                            //Обрабатываем ссылки
                            $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comments['text']);

                            $sql_comments[$key2]['text'] =stripslashes($row_comments['text']);

                            $date = megaDate(strtotime($row_comments['add_date']));
                            $sql_comments[$key2]['date'] =$date;
                            if($public_admin OR $user_id == $row_comments['public_id']){
                                $sql_comments[$key2]['owner'] = true;
                            } else{
                                $sql_comments[$key2]['owner'] = false;
                            }

                            if($user_id == $row_comments['public_id']){
                                $sql_comments[$key2]['not_owner'] = false;
                            }
                            else {
                                $sql_comments[$key2]['not_owner'] = true;
                            }

                            $query[$key]['comment'] = true;
                            $query[$key]['record'] = false;
                            $query[$key]['comment_form'] = false;
                            $query[$key]['all_comm'] = false;
                        }

                        $query[$key]['comments'] = $sql_comments;

                        //Загружаем форму ответа
//                        $query[$key]['rec_id'] = $row_wall['id'];
//                        $query[$key]['user_id'] = $row_wall['public_id'];
//                        $query[$key]['comment_form'] = true;
//                        $query[$key]['record'] = false;
////                        $query[$key]['comment'] = false;
//                        $query[$key]['all_comm'] = false;

                    }
                }
            }

//            $date = megaDate(strtotime($row_wall['add_date']));
//            $query[$key]['date'] = megaDate(strtotime($row_wall['add_date']));

            if(isset($row_wall['ac_user_id'])){
                $row_wall['add_date'] = $row_wall['action_time'];
            }
            $query[$key]['date'] = megaDate($row_wall['add_date']);


            //Мне нравится
            if(stripos($row_wall['likes_users'], "u{$user_id}|") !== false){
                $query[$key]['yes_like'] ='public_wall_like_yes';
                $query[$key]['yes_like_color'] ='public_wall_like_yes_color';
                $query[$key]['like_js_function'] ='groups.wall_remove_like('.$row_wall['id'].', '.$user_id.', '.$action_type.')';
            }
            else {
                $query[$key]['yes_like'] = '';
                $query[$key]['yes_like_color'] = '';
                $query[$key]['like_js_function'] ='groups.wall_add_like('.$row_wall['id'].', '.$user_id.', '.$action_type.')';
            }

            if($row_wall['likes_num']){
                $query[$key]['likes'] = $row_wall['likes_num'];
                $titles = array('человеку', 'людям', 'людям');//like
                $query[$key]['likes_text'] = '<span id="like_text_num'.$row_wall['id'].'">'.$row_wall['likes_num'].'</span> '.Gramatic::declOfNum($row_wall['likes_num'], $titles);
            }
            else {
                $query[$key]['likes'] = '1';
                $query[$key]['likes_text'] ='<span id="like_text_num'.$row_wall['id'].'">0</span> человеку';
            }
        }

        return $query;
    }

    /**
     * @param array $query
     * @return array
     */
    public static function build_news(array $query) : array
    {
        $sql_ = $query;
        $user_info = $user_info = Registry::get('user_info');
        $user_id = $user_info['user_id'];

        foreach($sql_ as $key => $row){

            $sql_[$key]['user_id'] = $user_id;

            if (($row['ac_id'])){
                $row['id'] = $row['ac_id'];
                $row['text'] = $row['action_text'];
                if ($row['action_type'] == 11){
                    $row['action_type'] = 2;
                    $row['type'] = 2;
                    $sql_[$key]['action_type'] = $action_type = 2;
                    $row['public_id'] = $row['ac_user_id'];
                }elseif ($row['action_type'] == 1){
                    $row['action_type'] = 1;
                    $row['type'] = 1;
                    $sql_[$key]['action_type'] = $action_type = 1;
//                    $row_wall['public_id'] = $row_wall['ac_user_id'];
                }
            }

//            if ($row['action_type'] != 2){
//                var_dump($row['action_type']);
////                exit();
//            }

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
            $sql_[$key]['date'] = $date;
            $sql_[$key]['action_text'] = stripslashes($row['action_text']);
            $params['news_id'] = $row['ac_id'];
            $params['action_type_updates'] = '';

            //public
            if($row['action_type'] == 2){
                $rowInfoUser = News::row_type11($row['ac_user_id'], 2);
                $sql_[$key]['name'] = $rowInfoUser['title'];
                $sql_[$key]['id'] = $row['ac_id'];
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
                        /** @var  $num - BUGFIX */
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
                        foreach($sql_comments as $key2 => $row_comments){
//                                    $tpl->set('{name}', $row_comments['user_search_pref']);
                            $sql_comments[$key2]['name'] = $row_comments['user_search_pref'];
                            if($row_comments['user_photo']){
//                                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
                                $sql_comments[$key2]['ava'] = $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo'];
                            }
                            else{
//                                        $tpl->set('{ava}', '/images/no_ava_50.png');
                                $sql_comments[$key2]['ava'] = '/images/no_ava_50.png';
                            }

//                                    $tpl->set('{rec-id}', $row['obj_id']);
                            $sql_comments[$key2]['rec_id'] = $row['obj_id'];
//                                    $tpl->set('{comm-id}', $row_comments['id']);
                            $sql_comments[$key2]['comm_id'] = $row_comments['id'];
//                                    $tpl->set('{user-id}', $row_comments['public_id']);
                            $sql_comments[$key2]['user_id'] = $row_comments['public_id'];
//                                    $tpl->set('{public-id}', $row['ac_user_id']);
                            $sql_comments[$key2]['public_id'] = $row['ac_user_id'];

                            $expBR2 = explode('<br />', $row_comments['text']);
                            $textLength2 = count($expBR2);
                            $strTXT2 = strlen($row_comments['text']);
                            if($textLength2 > 6 OR $strTXT2 > 470)
                                $sql_comments[$key2]['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                            //Обрабатываем ссылки
                            $sql_comments[$key2]['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

//                                    $tpl->set('{text}', );
                            $sql_comments[$key2]['text'] = stripslashes($row_comments['text']);
                            $date = megaDate($row_comments['add_date']);
//                                    $tpl->set('{date}', $date);
                            $sql_comments[$key2]['date'] = $date;
                            if($user_id == $row_comments['public_id']){
//                                        $tpl->set('[owner]', '');
//                                        $tpl->set('[/owner]', '');
                                $sql_comments[$key2]['owner'] = true;
                            } else{
//                                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                                $sql_comments[$key2]['owner'] = false;
                            }

                            if($user_id == $row_comments['author_user_id'])

//                                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                                $sql_comments[$key2]['not_owner'] = false;
                            else {

//                                        $tpl->set('[not-owner]', '');
//                                        $tpl->set('[/not-owner]', '');
                                $sql_comments[$key2]['not_owner'] = false;
                            }

//                                    $tpl->set('[comment]', '');
//                                    $tpl->set('[/comment]', '');
                            $sql_comments[$key2]['comment'] = true;
//                                    $tpl->set('[groups]', '');
//                                    $tpl->set('[/groups]', '');
                            $sql_comments[$key2]['groups'] = true;
//                                    $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                            $sql_comments[$key2]['wall_func'] = false;
//                                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $sql_comments[$key2]['record'] = false;
//                                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                            $sql_comments[$key2]['comment_form'] = false;
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                            $sql_comments[$key2]['all_comm'] = false;
//                                    $tpl->compile('content');
                        }

                        $sql_[$key]['comments'] = $sql_comments;

                        //Загружаем форму ответа
//                                $tpl->set('{rec-id}', $row['obj_id']);
//                        $sql_[$key]['rec_id'] = $row['obj_id'];
////                                $tpl->set('{author-id}', $row['ac_user_id']);
//                        $sql_[$key]['author_id'] = $row['ac_user_id'];
////                                $tpl->set('[comment-form]', '');
////                                $tpl->set('[/comment-form]', '');
//                        $sql_[$key]['comment_form'] = true;
////                                $tpl->set('[groups]', '');
////                                $tpl->set('[/groups]', '');
//                        $sql_[$key]['groups'] = true;
////                                $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
//                        $sql_[$key]['wall_func'] = false;
////                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
//                        $sql_[$key]['record'] = false;
////                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
//                        $sql_[$key]['comment'] = false;
////                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
//                        $sql_[$key]['all_comm'] = false;
//                                $tpl->compile('content');
                    }
//                            $tpl->result['content'] .= '</div>';
                }
//                        $tpl->result['content'] .= '</div></div>';
                //ads
            }
            //user
//            elseif($row['action_type'] == 1 || $sql_[$key]['action_type'] = 1) {
            elseif($row['action_type'] == 1) {
                $rowInfoUser = News::row_type11($row['ac_user_id'], 1);

                $sql_[$key]['name'] = $rowInfoUser['user_search_pref'];
                $sql_[$key]['id'] = $row['ac_id'];

                $row['user_search_pref'] = $rowInfoUser['user_search_pref'];

                $sql_[$key]['author'] = $rowInfoUser['user_search_pref'];
                $row['user_last_visit'] = $rowInfoUser['user_last_visit'];
                $row['user_logged_mobile'] = $rowInfoUser['user_logged_mobile'];
                $row['user_photo'] = $rowInfoUser['user_photo'];
                $row['user_sex'] = $rowInfoUser['user_sex'];
                $row['user_privacy'] = $rowInfoUser['user_privacy'];
                $sql_[$key]['link'] = 'u';
                if($row['user_photo']){
                    $sql_[$key]['ava'] = '/uploads/users/'.$row['ac_user_id'].'/50_'.$row['user_photo'];
                }
                else{
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
                if($textLength > 9 OR $strTXT > 600) {
                    $row['action_text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row['obj_id'] . '">' . $row['action_text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row['obj_id'] . ', this.id)" id="hide_wall_rec_lnk' . $row['obj_id'] . '">Показать полностью..</div>';
                }

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
                    if($rec_info['public']) {
                        $rowUserTell = News::user_tell_info($rec_info['tell_uid'], 2);
                    }
                    else {
                        $rowUserTell = News::user_tell_info($rec_info['tell_uid'], 1);
                    }

//                    $server_time = \Sura\Libs\Tools::time();

                    if (is_int($rec_info['tell_date'])){
                        $dateTell = megaDate($rec_info['tell_date']);
                    }else{
                        $dateTell = 'N/A';
                    }

                    if($rec_info['public']){
                        $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                        $tell_link = 'public';
                        if($rowUserTell['photo']) {
                            $avaTell = '/uploads/groups/' . $rec_info['tell_uid'] . '/50_' . $rowUserTell['photo'];
                        }
                        else {
                            $avaTell = '/images/no_ava_50.png';
                        }
                    } else {
                        $tell_link = 'u';
                        if($rowUserTell['user_photo']) {
                            $avaTell = '/uploads/users/' . $rec_info['tell_uid'] . '/50_' . $rowUserTell['user_photo'];
                        }
                        else {
                            $avaTell = '/images/no_ava_50.png';
                        }
                    }

                    if($rec_info['tell_comm']) {
                        $border_tell_class = 'wall_repost_border';
                    } else {
                        $border_tell_class = '';
                    }

                    $row['action_text'] = <<<HTML
                            {$rec_info['tell_comm']}
                            <div class="{$border_tell_class}">
                            <div class="wall_tell_info"><div class="wall_tell_ava"><a href="/{$tell_link}{$rec_info['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30"  alt=\"\" /></a></div><div class="wall_tell_name"><a href="/{$tell_link}{$rec_info['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class="wall_tell_date">{$dateTell}</div></div>{$row['action_text']}
                            <div class=""></div>
                            </div>
                            HTML;
                }

                $params['comment'] = stripslashes($row['action_text']);

                //Если есть комменты к записи, то выполняем след. действия
                if($rec_info['fasts_num']) {
                    $sql_[$key]['comments_link'] = false;
                }
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
                        /** @var  $num - BUGFIX */
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
//                        $params['author_id '] = $row['ac_user_id'];
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

                        //Собственно выводим комменты
                        foreach($sql_comments as $key2 => $row_comments){
//                                    $tpl->set('{name}', $row_comments['user_search_pref']);
                            $params['name'] = $row_comments['user_search_pref'];
                            if($row_comments['user_photo']){
                                $sql_comments[$key2]['ava'] = $config["home_url"].'uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo'];
                            }
                            else{
                                $sql_comments[$key2]['ava'] = '/images/no_ava_50.png';
                            }
                            $sql_comments[$key2]['rec_id'] = $row['obj_id'];
                            $sql_comments[$key2]['comm_id'] = $row_comments['id'];
                            $sql_comments[$key2]['user_id'] = $row_comments['author_user_id'];

                            $expBR2 = explode('<br />', $row_comments['text']);
                            $textLength2 = count($expBR2);
                            $strTXT2 = strlen($row_comments['text']);
                            if($textLength2 > 6 OR $strTXT2 > 470) {
                                $sql_comments[$key2]['text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row_comments['id'] . '" style="max-height:102px"">' . $row_comments['text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row_comments['id'] . ', this.id)" id="hide_wall_rec_lnk' . $row_comments['id'] . '">Показать полностью..</div>';
                            }

                            //Обрабатываем ссылки
                            $sql_comments[$key2]['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $sql_comments[$key2]['text']);

                            $sql_comments[$key2]['text'] = stripslashes($row_comments['text']);
//                            $sql_comments[$key2] = ;
                            $sql_comments[$key2]['date'] = megaDate($row_comments['add_date']);
                            if($user_id == $row_comments['author_user_id']){
                                $sql_comments[$key2]['owner'] = true;
                            } else {
                                $sql_comments[$key2]['owner'] = false;
                            }
                            if($user_id == $row_comments['author_user_id']){
                                $sql_comments[$key2]['not_owner'] = false;
                            }
                            else {
                                $sql_comments[$key2]['not_owner'] = true;
                            }
//                                    $tpl->set('[comment]', '');
//                                    $tpl->set('[/comment]', '');
                            $sql_[$key]['comment'] = true;
//                                    $tpl->set('[wall-func]', '');
//                                    $tpl->set('[/wall-func]', '');
                            $sql_[$key]['wall_func'] = true;
//                                    $tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
                            $sql_[$key]['groups'] = false;
//                                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $sql_[$key]['record'] = false;
//                                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                            $sql_[$key]['comment_form'] = false;
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                            $sql_[$key]['all_comm'] = false;
//                                    $tpl->compile('content');
                        }

                        $sql_[$key]['comments'] = $sql_comments;
//
//                        //Загружаем форму ответа
////                                $tpl->set('{rec-id}', $row['obj_id']);
//                        $sql_[$key]['rec_id'] = $row['obj_id'];
////                                $tpl->set('{author-id}', $row['ac_user_id']);
//                        $sql_[$key]['author_id'] = $row['ac_user_id'];
////                                $tpl->set('[comment-form]', '');
////                                $tpl->set('[/comment-form]', '');
//                        $sql_[$key]['comment_form'] = true;
////                                $tpl->set('[wall-func]', '');
////                                $tpl->set('[/wall-func]', '');
//                        $sql_[$key]['wall_func'] = true;
////                                $tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
//                        $sql_[$key]['groups'] = false;
////                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
//                        $sql_[$key]['record'] = false;
////                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
//                        $sql_[$key]['comment'] = false;
////                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
//                        $sql_[$key]['all_comm'] = false;
//                                $tpl->compile('content');
                    }
                }
            }

            else{
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
        return $sql_;
    }
}