<?php
declare(strict_types=1);

namespace App\Libs;


use App\Models\News;
use App\Models\Profile;
use Sura\Time\Date;
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
     * @throws \Throwable
     */
    public static function build(array $query): array
    {
        $db = Db::getDB();

        $user_info = Registry::get('user_info');
        $user_id = $user_info['user_id'];
        $config = Settings::load();
        foreach ($query as $key => $row) {

            $query[$key]['record'] = true;
            $query[$key]['comment'] = false;
            $query[$key]['comment_form'] = false;
            $query[$key]['all_comm'] = false;

            /**
             * Определяем юзер или сообщество
             * 1 - юзер
             * 2 - сообщество
             */
            if (isset($row['author_user_id']) and isset($row['action_type']) == false) {
                $query[$key]['action_type'] = $action_type = 1;
            } elseif (isset($row['action_type']) == false) {
                $query[$key]['action_type'] = $action_type = 2;
            }

            if (!empty($row['ac_id'])) {
                $row['id'] = $row['ac_id'];
                $row['text'] = $row['action_text'];
                if ($row['type'] == 11 || $row['action_type'] == 11) {
                    $row['action_type'] = 2;
                    $row['type'] = 2;
                    $query[$key]['action_type'] = $action_type = 2;
                    $row['public_id'] = $row['ac_user_id'];
                }
//                if ($row['type'] == 1 || $row['action_type'] == 1){
//                    $row['action_type'] = 1;
//                    $row['type'] = 1;
//                    $query[$key]['action_type'] = $action_type = 1;
////                    $row['public_id'] = $row['ac_user_id'];
//                }
            }

            /** id record */
            $query[$key]['rec_id'] = $row['id'];

            /** address */
            if ($query[$key]['action_type'] == 1) {
                $query[$key]['address'] = 'u' . $row['author_user_id'];
            } else {
                $query[$key]['address'] = 'public' . $row['public_id'];
            }

            /** Закрепить запись */
            if (!empty($row['fixed'])) {
                $query[$key]['styles_fasten'] = 'style="opacity:1"';
                $query[$key]['fasten_text'] = 'Закрепленная запись';
                $query[$key]['function_fasten'] = 'wall_unfasten';
            } else {
                $query[$key]['styles_fasten'] = true;
                $query[$key]['fasten_text'] = 'Закрепить запись';
                $query[$key]['function_fasten'] = 'wall_fasten';
            }

            /** КНопка Показать полностью.. $expBR */ //            $expBR = explode('<br />', $row['text']);
//            $textLength = count($expBR);
            $haystack = (string)$row['text'];
            $textLength = substr_count($haystack, '<br />');
            $strTXT = strlen($haystack);
            if ($textLength > 9 or $strTXT > 600) {
                $row['text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row['id'] . '">' . $row['text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row['id'] . ', this.id)" id="hide_wall_rec_lnk' . $row['id'] . '">Показать полностью..</div>';
            }

            //Прикрипленные файлы
            if ($row['attach']) {
                $attach_arr = explode('||', $row['attach']);
                $cnt_attach = 1;
                $cnt_attach_link = 1;
                //$jid = 0;
                $attach_result = '';
                $attach_result .= '<div class="clear"></div>';
                foreach ($attach_arr as $attach_file) {
                    $attach_type = explode('|', $attach_file);

                    //Фото со стены сообщества
                    if ($row['tell_uid']) {
                        $globParId = $row['tell_uid'];
                    } else {
                        if (!empty($row['public_id'])) {
                            $globParId = $row['public_id'];
                        } else {
                            $row['public_id'] = 0;
                        }
                    }

                    if ($attach_type[0] == 'photo' and file_exists(__DIR__ . "/../../public/uploads/groups/{$globParId}/photos/c_{$attach_type[1]}")) {
                        if ($cnt_attach < 2) {
                            $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['id']}\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$globParId}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$globParId}/photos/{$attach_type[1]}\" align=\"left\" /></div>";
                        } else {
                            $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$globParId}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$globParId}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";
                        }

                        $cnt_attach++;

                        $resLinkTitle = '';

                        //Фото со стены юзера
                    } elseif ($attach_type[0] == 'photo_u') {
                        $attauthor_user_id = $row['tell_uid'];

                        if ($attach_type[1] == 'attach' and file_exists(__DIR__ . "/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")) {
                            if ($cnt_attach < 2) $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['id']}\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\" align=\"left\" /></div>"; else
                                $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                            $cnt_attach++;
                        } elseif (file_exists(__DIR__ . "/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")) {
                            if ($cnt_attach < 2) $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['id']}\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\" align=\"left\" /></div>"; else
                                $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$row['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                            $cnt_attach++;
                        }

                        $resLinkTitle = '';

                        //Видео
                    } elseif ($attach_type[0] == 'video' and file_exists(__DIR__ . "/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")) {

                        $for_cnt_attach_video = explode('video|', $row['attach']);
                        $cnt_attach_video = count($for_cnt_attach_video) - 1;

                        if ($cnt_attach_video == 1 and preg_match('/(photo|photo_u)/i', $row['attach']) == false) {

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
                    } elseif ($attach_type[0] == 'audio') {
                        $data = explode('_', $attach_type[1]);
                        $audioId = (int)$data[0];
                        $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM
						        `audio` WHERE id = '{$audioId}'");
                        if ($row_audio) {
                            $stime = gmdate("i:s", (int)$row_audio['duration']);
                            if (!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                            if (!$row_audio['title']) $row_audio['title'] = 'Без названия';
                            $plname = 'wall';
                            if ($row_audio['oid'] != $user_info['user_id']) $q_s = <<<HTML
                                    <div class="audioSettingsBut"><li class="icon-plus-6"
                                    onClick="gSearch.addAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}')"
                                    onmouseover="showTooltip(this, {text: 'Добавить в мой список', shift: [6,5,0]});"
                                    id="no_play"></li><div class="clear"></div></div>
                                    HTML; else $q_s = '';
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
                    } elseif ($attach_type[0] == 'smile' and file_exists(__DIR__ . "/../../public/uploads/smiles/{$attach_type[1]}")) {
                        $attach_result .= '<img src=\"/uploads/smiles/' . $attach_type[1] . '\" style="margin-right:5px" />';

                        $resLinkTitle = '';

                        //Если ссылка
                    } elseif ($attach_type[0] == 'link' and preg_match('/http:\/\/(.*?)+$/i', $attach_type[1]) and $cnt_attach_link == 1 and stripos(str_replace('http://www.', 'http://', $attach_type[1]), $config['home_url']) === false) {
                        $count_num = count($attach_type);
                        $domain_url_name = explode('/', $attach_type[1]);
                        $rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);

                        $attach_type[3] = stripslashes($attach_type[3]);
                        $attach_type[3] = iconv_substr($attach_type[3], 0, 200, 'utf-8');

                        $attach_type[2] = stripslashes($attach_type[2]);
                        $str_title = iconv_substr($attach_type[2], 0, 55, 'utf-8');

                        if (stripos($attach_type[4], '/uploads/attach/') === false) {
                            $attach_type[4] = '/images/no_ava_groups_100.gif';
                            $no_img = false;
                        } else
                            $no_img = true;

                        if (!$attach_type[3]) $attach_type[3] = '';

                        if ($no_img and $attach_type[2]) {
                            if ($row['tell_comm']) $no_border_link = 'border:0px';

                            $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url=' . $attach_type[1] . '" target="_blank">' . $rdomain_url_name . '</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="' . $no_border_link . '"><a href="/away.php?url=' . $attach_type[1] . '" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="' . $attach_type[4] . '" /></div></a><div class="attatch_link_title"><a href="/away.php?url=' . $attach_type[1] . '" target="_blank">' . $str_title . '</a></div><div style="max-height:50px;overflow:hidden">' . $attach_type[3] . '</div></div></div>';

                            $resLinkTitle = $attach_type[2];
                            $resLinkUrl = $attach_type[1];
                        } elseif ($attach_type[1] and $attach_type[2]) {
                            $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url=' . $attach_type[1] . '" target="_blank">' . $rdomain_url_name . '</a></div></div></div><div class="clear"></div>';

                            $resLinkTitle = $attach_type[2];
                            $resLinkUrl = $attach_type[1];
                        }

                        $cnt_attach_link++;

                        //Если документ
                    } elseif ($attach_type[0] == 'doc') {

                        $doc_id = (int)$attach_type[1];

                        $row_doc = $db->super_query("SELECT dname, dsize FROM `doc` WHERE did = '{$doc_id}'", false, "wall/doc{$doc_id}");

                        if ($row_doc) {

                            $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did=' . $doc_id . '" target="_blank" onMouseOver="myhtml.title(\'' . $doc_id . $cnt_attach . $row['id'] . '\', \'<b>Размер файла: ' . $row_doc['dsize'] . '</b>\', \'doc_\')" id="doc_' . $doc_id . $cnt_attach . $row['id'] . '">' . $row_doc['dname'] . '</a></div></div></div><div class="clear"></div>';

                            $cnt_attach++;
                        }

                        //Если опрос
                    } elseif ($attach_type[0] == 'vote') {

                        $vote_id = (int)$attach_type[1];

                        $row_vote = $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$vote_id}'", false, "votes/vote_{$vote_id}");

                        if ($vote_id) {

                            $checkMyVote = $db->super_query("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$vote_id}'", false, "votes/check{$user_id}_{$vote_id}");

                            $row_vote['title'] = stripslashes($row_vote['title']);

                            if (!$row['text']) $row['text'] = $row_vote['title'];

                            $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                            $max = $row_vote['answer_num'];

                            $sql_answer = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$vote_id}' GROUP BY answer", 1, "votes/vote_answer_cnt_{$vote_id}");
                            $answer = array();
                            foreach ($sql_answer as $row_answer) {

                                $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                            }

                            $attach_result .= "<div class=\"clear\" style=\"height:10px\"></div><div id=\"result_vote_block{$vote_id}\"><div class=\"wall_vote_title\">{$row_vote['title']}</div>";

                            for ($ai = 0; $ai < sizeof($arr_answe_list); $ai++) {

                                if (!$checkMyVote['cnt']) {

                                    $attach_result .= "<div class=\"wall_vote_oneanswe\" onClick=\"Votes.Send({$ai}, {$vote_id})\" id=\"wall_vote_oneanswe{$ai}\"><input type=\"radio\" name=\"answer\" /><span id=\"answer_load{$ai}\">{$arr_answe_list[$ai]}</span></div>";

                                } else {

                                    $num = $answer[$ai]['cnt'];

                                    if (!$num) $num = 0;
                                    if ($max != 0) $proc = (100 * $num) / $max; else $proc = 0;
                                    $proc = round($proc, 2);

                                    $attach_result .= "<div class=\"wall_vote_oneanswe cursor_default\">
									{$arr_answe_list[$ai]}<br />
									<div class=\"wall_vote_proc fl_l\"><div class=\"wall_vote_proc_bg\" style=\"width:" . intval($proc) . "%\"></div><div style=\"margin-top:-16px\">{$num}</div></div>
									<div class=\"fl_l\" style=\"margin-top:-1px\"><b>{$proc}%</b></div>
									</div><div class=\"clear\"></div>";

                                }

                            }
                            $titles = array('человек', 'человека', 'человек');//fave
                            if ($row_vote['answer_num']) $answer_num_text = Gramatic::declOfNum($row_vote['answer_num'], $titles); else $answer_num_text = 'человек';

                            if ($row_vote['answer_num'] <= 1) $answer_text2 = 'Проголосовал'; else $answer_text2 = 'Проголосовало';

                            $attach_result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"clear\" style=\"margin-top:10px\"></div></div>";

                        }

                    } else

                        $attach_result .= '';

                }

                if ($resLinkTitle and $row['text'] == $resLinkUrl or !$row['text']) $row['text'] = $resLinkTitle . $attach_result; elseif ($attach_result) $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']) . $attach_result;
                else
                    $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']);
            } else {

                $subject = (string)$row['text'];
                $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $subject);
            }

            $resLinkTitle = '';

            //Если это запись с "рассказать друзьям"
            if ($row['tell_uid']) {
                $Profile = new Profile;
                if ($row['public']) {
                    $tell_uid = (int)$row['tell_uid'];
                    $rowUserTell = $Profile->user_tell_info($tell_uid, 2);
                } else {
                    $tell_uid = (int)$row['tell_uid'];
                    $rowUserTell = $Profile->user_tell_info($tell_uid, 1);
                }

                if (is_int($row['tell_date'])) {
                    $dateTell = \Sura\Time\Date::megaDate((int)$row['tell_date']);
                } else {
//                    $dateTell = 'N/A';
                    $dateTell = \Sura\Time\Date::megaDate((int)$row['tell_date']);
                }

                if ($row['public']) {
                    $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                    $tell_link = 'public';
                    if ($rowUserTell['photo']) $avaTell = '/uploads/groups/' . $row['tell_uid'] . '/50_' . $rowUserTell['photo']; else
                        $avaTell = '/images/no_ava_50.png';
                } else {
                    $tell_link = 'u';
                    if ($rowUserTell['user_photo']) $avaTell = '/uploads/users/' . $row['tell_uid'] . '/50_' . $rowUserTell['user_photo']; else
                        $avaTell = '/images/no_ava_50.png';
                }

                if ($row['tell_comm']) {
                    $border_tell_class = 'wall_repost_border';
                } else {
                    $border_tell_class = 'wall_repost_border2';
                }

                $row['text'] = <<<HTML
                        {$row['tell_comm']}
                        <div class="{$border_tell_class}">
                        <div class="wall_tell_info">
                        <div class="wall_tell_ava">
                        <a href="/{$tell_link}{$row['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30"  alt=""/></a></div>
                        <div class="wall_tell_name"><a href="/{$tell_link}{$row['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div>
                        <div class="wall_tell_date">{$dateTell}</div></div>{$row['text']}
                        <div class="clear"></div>
                        </div>
                        HTML;
            }

            //Выводим информацию о том кто смотрит страницу для себя
            $query[$key]['viewer_id'] = $user_id;
            if ($user_info['user_photo']) {
                $query[$key]['viewer_ava'] = '/uploads/users/' . $user_id . '/50_' . $user_info['user_photo'];
            } else {
                $query[$key]['viewer_ava'] = '/images/no_ava_50.png';
            }

            if (isset($row['type']) and $row['type']) {
                $query[$key]['type'] = $row['type'];
            } else {
                $query[$key]['type'] = '';
            }

            $query[$key]['privacy_comment'] = false;

            $query[$key]['date'] = \Sura\Time\Date::megaDate((int)$row['add_date']);

            if ($action_type == 1) {
                $query[$key]['text'] = stripslashes($row['text']);
                $query[$key]['name'] = $row['user_search_pref'];
                $query[$key]['user_id'] = $row['author_user_id'];
                $query[$key]['link'] = 'u';

                if (isset($row['ac_user_id'])) {
                    $query[$key]['user_id'] = $row['id'];
                }
                $query[$key]['online'] = \App\Libs\Profile::Online($row['user_last_visit']);

                if ($row['user_photo']) {
                    $query[$key]['ava'] = '/uploads/users/' . $row['author_user_id'] . '/50_' . $row['user_photo'];
                } else {
                    $query[$key]['ava'] = '/images/no_ava_50.png';
                }

                if (!empty($row['adres'])) {
                    $query[$key]['adres_id'] = $row['adres'];
                } else {
                    $query[$key]['adres_id'] = 'u' . $row['author_user_id'];
                }
                if (!isset($row['for_user_id'])) {
                    $query[$key]['for_user_id'] = 0;
                } else {
                    $query[$key]['for_user_id'] = $row['for_user_id'];
                }

                //Тег Owner означает показ записей только для владельца страницы или для того кто оставил запись
                if ($user_id == $row['author_user_id'] || $user_id == $row['for_user_id']) {
                    $query[$key]['owner'] = true;
                } else {
                    $query[$key]['owner'] = false;
                }

                // fixme
                $id = null;

                //Показа кнопки "Рассказать др" только если это записи владельца стр.
                if ($row['author_user_id'] == $id and $user_id != $id) {
                    $query[$key]['author_user_id'] = true;
                } else {
                    $query[$key]['author_user_id'] = false;
                }

                //Если есть комменты к записи, то выполняем след. действия / Приватность
                if ($row['fasts_num']) {
                    $query[$key]['if_comments'] = false;
                } else {
                    $query[$key]['if_comments'] = true;
                }

                $row = $db->super_query("SELECT user_privacy FROM `users` WHERE user_id = '{$row['author_user_id']}'");
                if ($row['user_privacy']) {
                    $user_privacy = xfieldsdataload($row['user_privacy']);
                } else {
                    $user_privacy = array();
                }

                if (isset($row['author_user_id'])) {
                    $CheckFriends = (new Friends)->CheckFriends((int)$row['author_user_id']);
                } else {
                    $CheckFriends = false;
                }

                //Приватность комментирования записей
                if ($user_privacy['val_wall3'] == 1 or $user_privacy['val_wall3'] == 2 and $CheckFriends or $user_id == $id) {
                    $query[$key]['privacy_comment'] = true;
                } else {
                    $query[$key]['privacy_comment'] = false;
                }

                //Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
                if ($user_privacy['val_wall3'] == 1 or $user_privacy['val_wall3'] == 2 and $CheckFriends or $user_id == $id) {
                    if (isset($row['fasts_num'])) {

                        if ($row['fasts_num'] > 3) {
                            $comments_limit = $row['fasts_num'] - 3;
                        } else {
                            $comments_limit = 0;
                        }
                        $Profile = new Profile;

                        $sql_comments = $Profile->comments((int)$row['id'], $comments_limit);

                        //Загружаем кнопку "Показать N запсии"
//                        $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
//                        $titles2 = array('комментарий', 'комментария', 'комментариев');//comments

//                        $query[$key]['gram_record_all_comm'] = Gramatic::declOfNum(($row['fasts_num']-3), $titles1).' '.($row['fasts_num']-3).' '.Gramatic::declOfNum(($row['fasts_num']-3), $titles2);
//                        $query[$key]['gram_record_all_comm'] = '';


                        /** @var  $num - BUGFIX */
                        $num = (int)$row['fasts_num'] - 3;
                        if ($num < 0) {
                            $num = 0;
                        }
                        $titles = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                        $prev = Gramatic::declOfNum($num, $titles);

                        $titles = array('комментарий', 'комментария', 'комментариев');//comments
                        $comments = Gramatic::declOfNum($num, $titles);

                        $sql_[$key]['gram_record_all_comm'] = $prev . ' ' . $num . ' ' . $comments;

                        if ($row['fasts_num'] < 4) {
                            $query[$key]['all_comm_block'] = false;
                        } else {
                            $query[$key]['rec_id'] = $row['id'];
                        }
                        $query[$key]['author_id'] = $id;

                        $query[$key]['record_block'] = false;
                        $query[$key]['comment_form_block'] = false;
                        $query[$key]['comment_block'] = false;

                        //Собственно выводим комменты
                        foreach ($sql_comments as $key2 => $row_comments) {
                            $sql_comments[$key2]['name'] = $row_comments['user_search_pref'];
                            if ($row_comments['user_photo']) {
                                $sql_comments[$key2]['ava'] = '/uploads/users/' . $row_comments['author_user_id'] . '/50_' . $row_comments['user_photo'];
                            } else {
                                $sql_comments[$key2]['ava'] = '/images/no_ava_50.png';
                            }

                            $sql_comments[$key2]['rec_id'] = $row['id'];
                            $sql_comments[$key2]['comm_id'] = $row_comments['id'];
                            $sql_comments[$key2]['user_id'] = $row_comments['author_user_id'];

//                            $expBR2 = explode('<br />', $row_comments['text']);
                            $textLength2 = substr_count($row_comments['text'], '<br />');
                            $strTXT2 = strlen($row_comments['text']);
                            if ($textLength2 > 6 or $strTXT2 > 470) {
                                $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row_comments['id'] . '" style="max-height:102px"">' . $row_comments['text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row_comments['id'] . ', this.id)" id="hide_wall_rec_lnk' . $row_comments['id'] . '">Показать полностью..</div>';
                            }

                            //Обрабатываем ссылки
                            $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

                            $sql_comments[$key2]['text'] = stripslashes($row_comments['text']);

                            $sql_comments[$key2]['date'] = \Sura\Time\Date::megaDate((int)$row_comments['add_date']);

                            if ($user_id == $row_comments['author_user_id'] || $user_id == $id) {
                                $sql_comments[$key2]['owner'] = true;
                            } else {
                                $sql_comments[$key2]['owner'] = false;
                            }

                            if ($user_id == $row_comments['author_user_id']) {
                                $sql_comments[$key2]['not_owner'] = false;
                            } else {
                                $sql_comments[$key2]['not_owner_block'] = true;
                            }

                            $query[$key]['comment'] = true;
                            $sql_comments[$key2]['record_block'] = false;
                            $sql_comments[$key2]['comment_form_block'] = false;
                            $sql_comments[$key2]['all_comm_block'] = false;
                        }

                        $query[$key]['comments'] = $sql_comments;

                        //Загружаем форму ответа
//                        $query[$key]['rec_id'] = $row['id'];
//                        $query[$key]['author_id'] = $id;
//                        $query[$key]['comment_form_block'] = true;
//                        $query[$key]['record'] = false;
//                        $query[$key]['comment'] = false;
//                        $query[$key]['all_comm'] = false;
                    }
                }


            } /** $action_type == 2 */ else {
                $query[$key]['text'] = stripslashes($row['text']);
                $query[$key]['name'] = $row['title'];

                $query[$key]['user_id'] = $row['public_id'];
                if (isset($row['ac_user_id'])) {
                    $query[$key]['user_id'] = $row['id'];
                }
                $query[$key]['online'] = '';

                if ($row['photo']) {
                    $query[$key]['ava'] = '/uploads/groups/' . $row['public_id'] . '/50_' . $row['photo'];
                } else {
                    $query[$key]['ava'] = '/images/no_ava_50.png';
                }

                if ($row['adres']) {
                    $query[$key]['adres_id'] = $row['adres'];
                } else {
                    $query[$key]['adres_id'] = 'public' . $row['public_id'];
                }
                $query[$key]['public_id'] = $row['public_id'];

                $row = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$row['public_id']}'");
                //Админ
                if (stripos($row['admin'], "u{$user_id}|") !== false) {
//                    $public_admin = true;
                    $query[$key]['owner'] = true;
                } else {
//                    $public_admin = false;
                    $query[$key]['owner'] = false;
                }

                //Приватность комментирования записей
                if ($row['comments'] or $query[$key]['owner']) {
                    $query[$key]['privacy_comment'] = true;
                } else {
                    $query[$key]['privacy_comment'] = false;
                }

                //FIXME update
                //Показа кнопки "Рассказать др" только если это записи владельца стр.
                if ($row['author_user_id'] == $id and $user_id != $id) {
                    $query[$key]['author_user_id'] = true;
                } else {
                    $query[$key]['author_user_id'] = false;
                }

                //Если есть комменты к записи, то выполняем след. действия / Приватность
                if ($row['fasts_num']) {
                    $query[$key]['if_comments'] = false;
                } else {
                    $query[$key]['if_comments'] = true;
                }

                //Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
                if ($row['comments'] or $query[$key]['owner']) {
                    if ($row['fasts_num']) {

                        //Помещаем все комменты в id wall_fast_block_{id} это для JS
//                            $tpl->result[$compile] .= '<div id="wall_fast_block_'.$row['id'].'" class="public_wall_rec_comments">';

                        if ($row['fasts_num'] > 3) {
                            $comments_limit = $row['fasts_num'] - 3;
                        } else {
                            $comments_limit = 0;
                        }

                        $sql_comments = $db->super_query("SELECT tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `communities_wall` tb1, `users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$row['id']}' ORDER by `add_date` ASC LIMIT {$comments_limit}, 3", true);

                        //Загружаем кнопку "Показать N запсии"
                        $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                        $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
                        $query[$key]['gram_record_all_comm'] = Gramatic::declOfNum(($row['fasts_num'] - 3), $titles1) . ' ' . ($row['fasts_num'] - 3) . ' ' . Gramatic::declOfNum(($row['fasts_num'] - 3), $titles2);
                        if ($row['fasts_num'] < 4) {
                            $query[$key]['all_comm'] = false;
                        } else {
                            $query[$key]['rec_id'] = $row['id'];
                            $query[$key]['all_comm'] = true;
                        }
                        $query[$key]['public_id'] = $row['id'];
                        $query[$key]['record'] = false;
                        $query[$key]['comment_form'] = false;
                        $query[$key]['comment'] = false;

                        //Собственно выводим комменты
                        foreach ($sql_comments as $key2 => $row_comments) {
                            $sql_comments[$key2]['public_id'] = $row['id'];
                            $sql_comments[$key2]['name'] = $row_comments['user_search_pref'];
                            if ($row_comments['user_photo']) {
                                $sql_comments[$key2]['ava'] = $config['home_url'] . 'uploads/users/' . $row_comments['public_id'] . '/50_' . $row_comments['user_photo'];
                            } else {
                                $sql_comments[$key2]['ava'] = '/images/no_ava_50.png';
                            }

                            $sql_comments[$key2]['rec_id'] = $row['id'];
                            $sql_comments[$key2]['comm_id'] = $row_comments['id'];
                            $sql_comments[$key2]['user_id'] = $row_comments['public_id'];

//                        $expBR2 = explode('<br />', $row_comments['text']);
//                        $textLength2 = count($expBR2);
                            $textLength2 = substr_count($row_comments['text'], '<br />');
                            $strTXT2 = strlen($row_comments['text']);
                            if ($textLength2 > 6 or $strTXT2 > 470) {
                                $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row_comments['id'] . '" style="max-height:102px"">' . $row_comments['text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row_comments['id'] . ', this.id)" id="hide_wall_rec_lnk' . $row_comments['id'] . '">Показать полностью..</div>';
                            }

                            //Обрабатываем ссылки
                            $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comments['text']);

                            $sql_comments[$key2]['text'] = stripslashes($row_comments['text']);

                            $date = \Sura\Time\Date::megaDate((int)$row_comments['add_date']);

                            $sql_comments[$key2]['date'] = $date;
                            if ($query[$key]['owner'] or $user_id == $row_comments['public_id']) {
                                $sql_comments[$key2]['owner'] = true;
                            } else {
                                $sql_comments[$key2]['owner'] = false;
                            }

                            if ($user_id == $row_comments['public_id']) {
                                $sql_comments[$key2]['not_owner'] = false;
                            } else {
                                $sql_comments[$key2]['not_owner'] = true;
                            }

                            $query[$key]['comment'] = true;
                            $query[$key]['record'] = false;
                            $query[$key]['comment_form'] = false;
                            $query[$key]['all_comm'] = false;
                        }

                        $query[$key]['comments'] = $sql_comments;

                        //Загружаем форму ответа
//                        $query[$key]['rec_id'] = $row['id'];
//                        $query[$key]['user_id'] = $row['public_id'];
//                        $query[$key]['comment_form'] = true;
//                        $query[$key]['record'] = false;
////                        $query[$key]['comment'] = false;
//                        $query[$key]['all_comm'] = false;

                    }
                }
            }

//            $date = megaDate(strtotime($row['add_date']));
//            $query[$key]['date'] = megaDate(strtotime($row['add_date']));

//            if(isset($row['ac_user_id'])){
//                $row['add_date'] = $row['action_time'];
//            }


            //Мне нравится
            if (isset($row['likes_users'])) {
                if (stripos((string)$row['likes_users'], "u{$user_id}|") !== false) {
                    $query[$key]['yes_like'] = 'public_wall_like_yes';
                    $query[$key]['yes_like_color'] = 'public_wall_like_yes_color';
                    $query[$key]['like_js_function'] = 'groups.wall_remove_like(' . $row['id'] . ', ' . $user_id . ', ' . $action_type . ')';
                } else {
                    $query[$key]['yes_like'] = '';
                    $query[$key]['yes_like_color'] = '';
                    $query[$key]['like_js_function'] = 'groups.wall_add_like(' . $row['id'] . ', ' . $user_id . ', ' . $action_type . ')';
                }
            } else {
                $query[$key]['yes_like'] = '';
                $query[$key]['yes_like_color'] = '';
                $query[$key]['like_js_function'] = '';
            }

            if (isset($row['likes_num'])) {
                $query[$key]['likes'] = $row['likes_num'];
                $titles = array('человеку', 'людям', 'людям');//like
                $query[$key]['likes_text'] = '<span id="like_text_num' . $row['id'] . '">' . $row['likes_num'] . '</span> ' . Gramatic::declOfNum((int)$row['likes_num'], $titles);
            } else {
                if (!isset($row['id']))
                    $row['id'] = 0;//FIXME
                $query[$key]['likes'] = '';
                $query[$key]['likes_text'] = '<span id="like_text_num' . $row['id'] . '">0</span> человеку';
            }
        }

        return $query;
    }

}