<?php


namespace App\Libs;


use App\Models\News;
use Sura\Libs\Gramatic;
use Sura\Libs\Registry;
use Sura\Libs\Settings;
use Sura\Libs\Tools;

class Wall2
{

    /**
     * @param array $query
     * @return array
     */
    public static function build_news(array $query): array
    {
        $user_info = Registry::get('user_info');
        $user_id = $user_info['user_id'];
        $News = new News;

        foreach ($query as $key => $row) {

            $query[$key]['user_id'] = $user_id;

            /**
             * Определяем юзер или сообщество
             * 1 - юзер
             * 2 - сообщество
             */
            if (($row['ac_id'])) {
                $row['id'] = $row['ac_id'];
//                $row['text'] = $row['action_text'];
                $query[$key]['text'] = $row['action_text'];
                if ($row['action_type'] == 11) {
                    $row['action_type'] = 2;
                    $row['type'] = 2;
                    $query[$key]['action_type'] = $action_type = 2;
                    $row['public_id'] = $row['ac_user_id'];
                } elseif ($row['action_type'] == 1) {
                    $row['action_type'] = 1;
                    $row['type'] = 1;
                    $query[$key]['action_type'] = $action_type = 1;
//                    $row_wall['public_id'] = $row_wall['ac_user_id'];
                }
            }

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

            /** @fixed */
//            $query[$key]['author_id'] = $row['ac_user_id'];
            $query[$key]['author_user_id'] = $row['ac_user_id'];

//                    if (!isset($row['user_logged_mobile']))
//                        $row['user_logged_mobile'] = '0';//bug: undefined
//
//                    if (!isset($row['user_last_visit']))
//                        $row['user_last_visit'] = null;

//            $query[$key]['online'] = Online($row['user_last_visit'], $row['user_logged_mobile']);
            //FIXME
            $query[$key]['online'] = 'online';

            //Выводим данные о действии
            $date = \Sura\Time\Date::megaDate((int)$row['action_time']);
            $query[$key]['date'] = $date;
//            $query[$key]['action_text'] = stripslashes($row['action_text']);
            $query[$key]['news_id'] = $row['ac_id'];
            $query[$key]['action_type_updates'] = '';

            $query[$key]['privacy_comment'] = false;

            //Выводим информацию о том кто смотрит страницу для себя
            $query[$key]['viewer_id'] = $user_id;
            if ($user_info['user_photo']) {
                $query[$key]['viewer_ava'] = '/uploads/users/' . $user_id . '/50_' . $user_info['user_photo'];
            } else {
                $query[$key]['viewer_ava'] = '/images/no_ava_50.png';
            }


            //public
            if ($row['action_type'] == 2) {
                $ac_user_id = (int)$row['ac_user_id'];//FIXME
                $rowInfoUser = $News->row_type11($ac_user_id, 2);
                $query[$key]['name'] = $rowInfoUser['title'];
                $query[$key]['id'] = $row['ac_id'];
                $row['user_search_pref'] = $rowInfoUser['title'];

                $query[$key]['author'] = $rowInfoUser['title'];
                $query[$key]['link'] = 'public';
                $query[$key]['address'] = 'public' . $row['public_id'];

                if ($rowInfoUser['photo']) {
                    $query[$key]['ava'] = '/uploads/groups/' . $row['ac_user_id'] . '/50_' . $rowInfoUser['photo'];
                } else {
                    $query[$key]['ava'] = '/images/no_ava_50.png';
                }

                //Выводим кол-во комментов, мне нравится, и список юзеров кто поставил лайки к записи если это не страница "ответов"
                $rec_info_groups = $News->rec_info_groups((int)$row['obj_id']);

                //Кнопка Показать полностью..
                $expBR = explode('<br />', $row['action_text']);
                $textLength = count($expBR);
                $strTXT = strlen($row['action_text']);
                if ($textLength > 9 or $strTXT > 600) {
                    $row['action_text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row['obj_id'] . '">' . $row['action_text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row['obj_id'] . ' , this.id)" id="hide_wall_rec_lnk' . $row['obj_id'] . '">Показать полностью..</div>';
                }


                //Прикрепленные файлы
                if (isset($rec_info_groups['attach'])) {
                    $attach_arr = explode('||', $rec_info_groups['attach']);
                    $cnt_attach = 1;
                    $cnt_attach_link = 1;
                    $jid = 0;
                    $attach_result = '';
                    //$attach_result .= '<div class=""></div>';//div.clear
                    $config = Settings::load();
                    $row_wall = null;
                    $resLinkTitle = '';
                    foreach ($attach_arr as $attach_file) {
                        $attach_type = explode('|', $attach_file);

                        if ($rec_info_groups['public']) $row['ac_user_id'] = $rec_info_groups['tell_uid'];

                        //Фото со стены сообщества
                        if ($attach_type[0] == 'photo' and file_exists(__DIR__ . "/../../public/uploads/groups/{$row['ac_user_id']}/photos/c_{$attach_type[1]}")) {
                            if ($cnt_attach < 2) $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row['ac_user_id']}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['ac_user_id']}/photos/{$attach_type[1]}\"  alt=/" / " /></div>"; else
                                $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['ac_user_id']}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row['ac_user_id']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=/" / "/>";

                            $cnt_attach++;

                            //Фото со стены юзера
                        } elseif ($attach_type[0] == 'photo_u') {
                            if ($rec_info_groups['tell_uid']) $attauthor_user_id = $rec_info_groups['tell_uid']; else $attauthor_user_id = $row['ac_user_id'];

                            if ($attach_type[1] == 'attach' and file_exists(__DIR__ . "/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")) {
                                if ($cnt_attach < 2) $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\"  alt=\"\" /></div>"; else
                                    $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                $cnt_attach++;
                            } elseif (file_exists(__DIR__ . "/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")) {
                                if ($cnt_attach < 2) $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\"  alt=\"\" /></div>"; else
                                    $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row['obj_id']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";
                                $cnt_attach++;
                            }
                            $resLinkTitle = '';
                        } //Видео
                        elseif ($attach_type[0] == 'video' and file_exists(__DIR__ . "/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")) {

                            $for_cnt_attach_video = explode('video|', $rec_info_groups['attach']);
                            $cnt_attach_video = count($for_cnt_attach_video) - 1;

                            if ($cnt_attach_video == 1 and preg_match('/(photo|photo_u)/i', $rec_info_groups['attach']) == false) {

                                $video_id = (int)$attach_type[2];

                                $row_video = $News->video_info($video_id);
                                $row_video['title'] = stripslashes($row_video['title']);
                                $row_video['video'] = stripslashes($row_video['video']);
                                $row_video['video'] = strtr($row_video['video'], array('width="770"' => 'width="390"', 'height="420"' => 'height="310"'));

                                $attach_result .= "<div class=\"cursor_pointer \" id=\"no_video_frame{$video_id}\" onClick=\"$('#'+this.id).hide();$('#video_frame{$video_id}').show();\">
                                        <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px\" width=\"390\" height=\"310\"  alt=/" / "/></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div><div class=\"video_inline_vititle\"></div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><b>{$row_video['title']}</b></a>";

                            } else {

                                $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\"  alt=\"\" /></a></div>";

                            }

                            $resLinkTitle = '';

                        } //Музыка
                        elseif ($attach_type[0] == 'audio') {
                            $audioId = (int)$attach_type[1];
                            $audioInfo = $News->audio_info($audioId);
                            if ($audioInfo) {
                                $jid++;
                                $attach_result .= '<div class="audioForSize' . $row['obj_id'] . ' player_mini_mbar_wall_all" id="audioForSize"><div class="audio_onetrack audio_wall_onemus"><div class="audio_playic cursor_pointer fl_l" onClick="music.newStartPlay(\'' . $jid . '\', ' . $row['obj_id'] . ')" id="icPlay_' . $row['obj_id'] . $jid . '"></div><div id="music_' . $row['obj_id'] . $jid . '" data="' . $audioInfo['url'] . '" class="fl_l" style="margin-top:-1px"><a href="/?go=search&type=5&query=' . $audioInfo['artist'] . '&n=1" onClick="Page.Go(this.href); return false"><b>' . stripslashes($audioInfo['artist']) . '</b></a> &ndash; ' . stripslashes($audioInfo['title']) . '</div><div id="play_time' . $row['obj_id'] . $jid . '" class="color777 fl_r no_display" style="margin-top:2px;margin-right:5px">00:00</div><div class="player_mini_mbar fl_l no_display player_mini_mbar_wall_all" id="ppbarPro' . $row['obj_id'] . $jid . '"></div></div></div>';
                            }

                            $resLinkTitle = '';

                        } //Смайлик
                        elseif ($attach_type[0] == 'smile' and file_exists(__DIR__ . "/../../public/uploads/smiles/{$attach_type[1]}")) {
                            $attach_result .= '<img src=\"/uploads/smiles/' . $attach_type[1] . '\" style="margin-right:5px" />';

                            $resLinkTitle = '';

                        } //Если ссылка
                        elseif ($attach_type['0'] == 'link' and preg_match('/https:\/\/(.*?)+$/i', $attach_type[1]) and $cnt_attach_link == 1 and stripos(str_replace('https://www.', 'https://', $attach_type[1]), $config['home_url']) === false) {
//                                    $count_num = count($attach_type);
                            $domain_url_name = explode('/', $attach_type['1']);
                            $rdomain_url_name = str_replace('https://', '', $domain_url_name[2]);

                            $attach_type['3'] = stripslashes($attach_type['3']);
                            $attach_type['3'] = substr($attach_type['3'], 0, 200);

                            $attach_type['2'] = stripslashes($attach_type[2]);
                            $str_title = substr($attach_type['2'], 0, 55);

                            if (stripos($attach_type['4'], '/uploads/attach/') === false) {
                                $attach_type['4'] = '/images/no_ava_groups_100.gif';
                                $no_img = false;
                            } else
                                $no_img = true;

                            if (!$attach_type['3']) $attach_type['3'] = '';

                            if ($no_img and $attach_type['2']) {
                                if ($rec_info_groups['tell_comm']) {
                                    $no_border_link = 'border:0';
                                } else {
                                    $no_border_link = '';
                                }

                                $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url=' . $attach_type['1'] . '" target="_blank">' . $rdomain_url_name . '</a></div></div><div class=""></div><div class="wall_show_block_link" style="' . $no_border_link . '"><a href="/away.php?url=' . $attach_type['1'] . '" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="' . $attach_type['4'] . '"  alt=\"\" /></div></a><div class="attatch_link_title"><a href="/away.php?url=' . $attach_type['1'] . '" target="_blank">' . $str_title . '</a></div><div style="max-height:50px;overflow:hidden">' . $attach_type['3'] . '</div></div></div>';

                                $resLinkTitle = $attach_type[2];
                                $resLinkUrl = $attach_type[1];
                            } elseif ($attach_type['1'] and $attach_type['2']) {
                                $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url=' . $attach_type['1'] . '" target="_blank">' . $rdomain_url_name . '</a></div></div></div><div class=""></div>';

                                $resLinkTitle = $attach_type['2'];
                                $resLinkUrl = $attach_type['1'];
                            }

                            $cnt_attach_link++;

                        } //Если документ
                        elseif ($attach_type['0'] == 'doc') {

                            $doc_id = (int)$attach_type['1'];

                            $row_doc = $News->doc_info($doc_id);

                            if ($row_doc) {

                                $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class=""><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did=' . $doc_id . '" target="_blank" onMouseOver="myhtml.title(\'' . $doc_id . $cnt_attach . $row['obj_id'] . '\', \'<b>Размер файла: ' . $row_doc['dsize'] . '</b>\', \'doc_\')" id="doc_' . $doc_id . $cnt_attach . $row['obj_id'] . '">' . $row_doc['dname'] . '</a></div></div></div><div class=""></div>';

                                $cnt_attach++;
                            }

                        } //Если опрос
                        elseif ($attach_type['0'] == 'vote') {

                            $vote_id = intval($attach_type['1']);

                            $row_vote = $News->video_info($vote_id);

                            if ($vote_id) {

                                $checkMyVote = $News->vote_info_check($vote_id, $user_id);

                                $row_vote['title'] = stripslashes($row_vote['title']);

                                if (!$row_wall['text']) $row_wall['text'] = $row_vote['title'];

                                $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                $max = $row_vote['answer_num'];

                                $queryanswer = $News->vote_info_answer($vote_id);
                                $answer = array();
                                foreach ($queryanswer as $row_answer) {

                                    $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                                }

                                $attach_result .= "<div class=\"\" style=\"height:10px\"></div><div id=\"result_vote_block{$vote_id}\"><div class=\"wall_vote_title\">{$row_vote['title']}</div>";

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
                                                    </div><div class=\"\"></div>";

                                    }

                                }
                                $titles = array('человек', 'человека', 'человек');//fave
                                if ($row_vote['answer_num']) $answer_num_text = Gramatic::declOfNum($row_vote['answer_num'], $titles); else $answer_num_text = 'человек';

                                if ($row_vote['answer_num'] <= 1) $answer_text2 = 'Проголосовал'; else $answer_text2 = 'Проголосовало';

                                $attach_result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"\" style=\"margin-top:10px\"></div></div>";

                            }

                        } else
                            $attach_result .= '';
                    }

                    //FIXME
                    if (!isset($resLinkUrl)) {
                        $resLinkUrl = false;
                    }
                    if (!isset($resLinkTitle)) {
                        $resLinkTitle = false;
                    }

                    if ($resLinkTitle and $row['action_text'] == $resLinkUrl or !$row['action_text']) $row['action_text'] = $resLinkTitle . $attach_result; elseif ($attach_result) $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']) . $attach_result;
                    else
                        $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);

                } else {
                    $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);
                }

                $resLinkTitle = '';

                //Если это запись с "рассказать друзьям"
                if (isset($rec_info_groups['tell_uid'])) {

                    if ($rec_info_groups['tell_date'] > 0) {
                        $dateTell = \Sura\Time\Date::megaDate((int)$rec_info_groups['tell_date']);
                    } else {
                        $dateTell = 'N/A';
                    }

                    if ($rec_info_groups['public']) {
                        $rowUserTell = $News->user_tell_info((int)$rec_info_groups['tell_uid'], 2);
//                        $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                        $row_user_name = stripslashes($rowUserTell['title']);
                        $tell_link = 'public';
                        if ($rowUserTell['photo']) {
                            $avaTell = '/uploads/groups/' . $rec_info_groups['tell_uid'] . '/50_' . $rowUserTell['photo'];
                        } else {
                            $avaTell = '/images/no_ava_50.png';
                        }
                    } else {
                        $rowUserTell = $News->user_tell_info((int)$rec_info_groups['tell_uid'], 1);
                        if (empty($rowUserTell['user_search_pref'])) {
                            $row_user_name = 'Неизвестный пользователь';
                        } else {
                            $row_user_name = $rowUserTell['user_search_pref'];
                        }
                        $tell_link = 'u';
                        if (isset($rowUserTell['user_photo'])) {
                            $avaTell = '/uploads/users/' . $rec_info_groups['tell_uid'] . '/50_' . $rowUserTell['user_photo'];
                        } else {
                            $avaTell = '/images/no_ava_50.png';
                        }
                    }

                    if ($rec_info_groups['tell_comm']) $border_tell_class = 'wall_repost_border'; else $border_tell_class = 'wall_repost_border3';

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
                                    <a href="/{$tell_link}{$rec_info_groups['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$row_user_name}</b></a>
                                </div>
                                <div class="wall_tell_date">{$dateTell}</div>
                            </div>{$row['action_text']}
                                <div class=""></div>
                            </div>
                            HTML;
                }


                $query[$key]['comment'] = stripslashes($row['action_text']);

                //Если есть комменты к записи, то выполняем след. действия
                if ($rec_info_groups['fasts_num'] or $rowInfoUser['comments'] == false) {
                    $query[$key]['comments_link'] = true;
                } else {
                    $query[$key]['comments_link'] = false;
                }

                //Мне нравится
                if (stripos((string)$rec_info_groups['likes_users'], "u{$user_id}|") !== false) {
                    $query[$key]['yes_like'] = 'public_wall_like_yes';
                    $query[$key]['yes_like_color'] = 'public_wall_like_yes_color';
                    $query[$key]['like_js_function'] = 'groups.wall_remove_like(' . $row['obj_id'] . ', ' . $user_id . ', ' . $action_type . ')';
                } else {
                    $query[$key]['yes_like'] = '';
                    $query[$key]['yes_like_color'] = '';
                    $query[$key]['like_js_function'] = 'groups.wall_add_like(' . $row['obj_id'] . ', ' . $user_id . ', ' . $action_type . ')';
                }

                if ($rec_info_groups['likes_num']) {
//                            $tpl->set('{likes}', $rec_info_groups['likes_num']);
                    $titles = array('человеку', 'людям', 'людям');//like
//                            $tpl->set('{likes-text}', '<span id="like_text_num'.$row['obj_id'].'">'.$rec_info_groups['likes_num'].'</span> '.Gramatic::declOfNum($rec_info_groups['likes_num'], $titles));
                    $query[$key]['likes'] = $rec_info_groups['likes_num'];
                    $query[$key]['likes_text'] = '<span id="like_text_num' . $row['obj_id'] . '">' . $rec_info_groups['likes_num'] . '</span> ' . Gramatic::declOfNum((int)$rec_info_groups['likes_num'], $titles);
                } else {
//                            $tpl->set('{likes}', '');
//                            $tpl->set('{likes-text}', '<span id="like_text_num'.$row['obj_id'].'">0</span> человеку');
                    $query[$key]['likes'] = '';
                    $query[$key]['likes_text'] = '<span id="like_text_num' . $row['obj_id'] . '">0</span> человеку';
                }

                //Выводим информцию о том кто смотрит страницу для себя
//                        $tpl->set('{viewer-id}', $user_id);
                $query[$key]['viewer-id'] = $user_id;
                if ($user_info['user_photo']) {
//                            $tpl->set('{viewer-ava}', '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
                    $query[$key]['viewer_ava'] = '/uploads/users/' . $user_id . '/50_' . $user_info['user_photo'];
                } else {
//                            $tpl->set('{viewer-ava}', '/images/no_ava_50.png');
                    $query[$key]['viewer_ava'] = '/images/no_ava_50.png';
                }

//                        $tpl->set('{rec-id}', $row['obj_id']);
                $query[$key]['rec_id'] = $row['obj_id'];
//                        $tpl->set('[record]', '');
//                        $tpl->set('[/record]', '');
                $query[$key]['record'] = true;
//                        $tpl->set('[wall]', '');
//                        $tpl->set('[/wall]', '');
                $query[$key]['wall'] = true;
//                        $tpl->set('[groups]', '');
//                        $tpl->set('[/groups]', '');
                $query[$key]['groups'] = true;
//                        $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                $query[$key]['wall_func'] = false;
//                        $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                $query[$key]['comment'] = false;
//                        $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                $query[$key]['comment_form'] = false;
//                        $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                $query[$key]['all_comm'] = false;
//                        $tpl->compile('content');

                //Если есть комменты, то выводим и страница не "ответы"
                if ($rowInfoUser['comments']) {

                    //Помещаем все комменты в id wall_fast_block_{id} это для JS
//                            $tpl->result['content'] .= '<div id="wall_fast_block_'.$row['obj_id'].'">';
                    if ($rec_info_groups['fasts_num']) {
                        if ($rec_info_groups['fasts_num'] > 3) {
                            $comments_limit = $rec_info_groups['fasts_num'] - 3;
                        } else {
                            $comments_limit = 0;
                        }

                        $querycomments = $News->comments($row['obj_id'], $comments_limit);

                        //Загружаем кнопку "Показать N записи"
                        /** @var  $num - BUGFIX */
                        $num = (int)$rec_info_groups['fasts_num'] - 3;
                        if ($num < 0) {
                            $num = 0;
                        }
                        $titles = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                        $prev = Gramatic::declOfNum($num, $titles);
                        $titles = array('комментарий', 'комментария', 'комментариев');//comments
                        $comments = Gramatic::declOfNum($num, $titles);
                        $query[$key]['gram_record_all_comm'] = $prev . ' ' . $num . ' ' . $comments;


                        if ($rec_info_groups['fasts_num'] < 4) {
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                            $query[$key]['all_comm'] = false;
                        } else {
//                                    $tpl->set('{rec-id}', $row['obj_id']);
//                                    $tpl->set('[all-comm]', '');
//                                    $tpl->set('[/all-comm]', '');
                            $query[$key]['rec_id'] = $row['obj_id'];
                            $query[$key]['all_comm'] = true;
                        }
//                                $tpl->set('{author-id}', $row['ac_user_id']);
                        $query[$key]['author_id'] = $row['ac_user_id'];
//                                $tpl->set('[groups]', '');
//                                $tpl->set('[/groups]', '');
                        $query[$key]['groups'] = true;
//                                $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                        $query[$key]['wall_func'] = false;
//                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                        $query[$key]['record'] = false;
//                                $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                        $query[$key]['comment_form'] = false;
//                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                        $query[$key]['comment'] = false;
//                                $tpl->compile('content');


                        $config = Settings::load();

                        //Собственно выводим комменты
                        foreach ($querycomments as $key2 => $row_comments) {
                            //                                    $tpl->set('{name}', $row_comments['user_search_pref']);
                            $querycomments[$key2]['name'] = $row_comments['user_search_pref'];
                            if ($row_comments['user_photo']) {
//                                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
                                $querycomments[$key2]['ava'] = $config['home_url'] . 'uploads/users/' . $row_comments['public_id'] . '/50_' . $row_comments['user_photo'];
                            } else {
//                                        $tpl->set('{ava}', '/images/no_ava_50.png');
                                $querycomments[$key2]['ava'] = '/images/no_ava_50.png';
                            }

//                                    $tpl->set('{rec-id}', $row['obj_id']);
                            $querycomments[$key2]['rec_id'] = $row['obj_id'];
//                                    $tpl->set('{comm-id}', $row_comments['id']);
                            $querycomments[$key2]['comm_id'] = $row_comments['id'];
//                                    $tpl->set('{user-id}', $row_comments['public_id']);
                            $querycomments[$key2]['user_id'] = $row_comments['public_id'];
//                                    $tpl->set('{public-id}', $row['ac_user_id']);
                            $querycomments[$key2]['public_id'] = $row['ac_user_id'];

                            $expBR2 = explode('<br />', $row_comments['text']);
                            $textLength2 = count($expBR2);
                            $strTXT2 = strlen($row_comments['text']);
                            if ($textLength2 > 6 or $strTXT2 > 470) $querycomments[$key2]['text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row_comments['id'] . '" style="max-height:102px"">' . $row_comments['text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row_comments['id'] . ', this.id)" id="hide_wall_rec_lnk' . $row_comments['id'] . '">Показать полностью..</div>';

                            //Обрабатываем ссылки
                            $querycomments[$key2]['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

//                                    $tpl->set('{text}', );
                            $querycomments[$key2]['text'] = stripslashes($row_comments['text']);
                            $date = \Sura\Time\Date::megaDate((int)$row_comments['add_date']);
//                                    $tpl->set('{date}', $date);
                            $querycomments[$key2]['date'] = $date;

                            $querycomments[$key2]['owner'] = false; //FIXME
                            if ($user_id == $row_comments['public_id']) {
                                $querycomments[$key2]['owner'] = true;
                            } else {
                                $querycomments[$key2]['owner'] = false;
                            }

                            if ($user_id == $row_comments['author_user_id']) {
                                $querycomments[$key2]['not_owner'] = false;
                            } else {
                                $querycomments[$key2]['not_owner'] = false;
                            }

//                                    $tpl->set('[comment]', '');
//                                    $tpl->set('[/comment]', '');
                            $querycomments[$key2]['comment'] = true;
//                                    $tpl->set('[groups]', '');
//                                    $tpl->set('[/groups]', '');
                            $querycomments[$key2]['groups'] = true;
//                                    $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
                            $querycomments[$key2]['wall_func'] = false;
//                                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $querycomments[$key2]['record'] = false;
//                                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                            $querycomments[$key2]['comment_form'] = true;
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                            $querycomments[$key2]['all_comm'] = false;
//                                    $tpl->compile('content');
                        }

                        $query[$key]['comments'] = $querycomments;

                        //Загружаем форму ответа
//                                $tpl->set('{rec-id}', $row['obj_id']);
//                        $query[$key]['rec_id'] = $row['obj_id'];
////                                $tpl->set('{author-id}', $row['ac_user_id']);
//                        $query[$key]['author_id'] = $row['ac_user_id'];
////                                $tpl->set('[comment-form]', '');
////                                $tpl->set('[/comment-form]', '');
//                        $query[$key]['comment_form'] = true;
////                                $tpl->set('[groups]', '');
////                                $tpl->set('[/groups]', '');
//                        $query[$key]['groups'] = true;
////                                $tpl->set_block("'\\[wall-func\\](.*?)\\[/wall-func\\]'si","");
//                        $query[$key]['wall_func'] = false;
////                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
//                        $query[$key]['record'] = false;
////                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
//                        $query[$key]['comment'] = false;
////                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
//                        $query[$key]['all_comm'] = false;
//                                $tpl->compile('content');
                    }
//                            $tpl->result['content'] .= '</div>';
                }
//                        $tpl->result['content'] .= '</div></div>';
                //ads
            }
            //user
//            elseif($row['action_type'] == 1 || $query[$key]['action_type'] = 1) {
            elseif ($row['action_type'] == 1) {
                $rowInfoUser = $News->row_type11((int)$row['ac_user_id'], 1);

                $query[$key]['name'] = $rowInfoUser['user_search_pref'];
                $query[$key]['id'] = $row['ac_id'];

                $row['user_search_pref'] = $rowInfoUser['user_search_pref'];

                $query[$key]['author'] = $rowInfoUser['user_search_pref'];
                $row['user_last_visit'] = $rowInfoUser['user_last_visit'];
                $row['user_logged_mobile'] = $rowInfoUser['user_logged_mobile'];
                $row['user_photo'] = $rowInfoUser['user_photo'];
                $row['user_sex'] = $rowInfoUser['user_sex'];
                $row['user_privacy'] = $rowInfoUser['user_privacy'];
                $query[$key]['link'] = 'u';
                $query[$key]['address'] = 'u' . $row['ac_user_id'];
                if ($row['user_photo']) {
                    $query[$key]['ava'] = '/uploads/users/' . $row['ac_user_id'] . '/50_' . $row['user_photo'];
                } else {
                    $query[$key]['ava'] = '/images/no_ava_50.png';
                }

                $for_user_id = $row['for_user_id'] ?? ($row['for_user_id'] = false);//FIXME

                if ($user_id == $row['ac_user_id'] || $user_id == $for_user_id) {
                    $query[$key]['owner'] = true;
                } else {
                    $query[$key]['owner'] = false;
                }

                //Приватность
                $user_privacy = xfieldsdataload($row['user_privacy']);
                $check_friend = (new Friends)->CheckFriends((int)$row['ac_user_id']);

                //Выводим кол-во комментов, мне нравится, и список юзеров кто поставил лайки к записи если это не страница "ответов"
                $rec_info = $News->rec_info((int)$row['obj_id']);

                //КНопка Показать полностью..
                $expBR = explode('<br />', $row['action_text']);
                $textLength = count($expBR);
                $strTXT = strlen($row['action_text']);
                if ($textLength > 9 or $strTXT > 600) {
                    $row['action_text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row['obj_id'] . '">' . $row['action_text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row['obj_id'] . ', this.id)" id="hide_wall_rec_lnk' . $row['obj_id'] . '">Показать полностью..</div>';
                }

                //Прикрипленные файлы
                if ($rec_info['attach']) {
                    $attach_arr = explode('||', $rec_info['attach']);
                    $cnt_attach = 1;
                    $cnt_attach_link = 1;
                    $jid = 0;
                    $attach_result = '';
                    $attach_result .= '<div class=""></div>';
                    $config = Settings::load();
                    $resLinkTitle = '';
                    $resLinkUrl = '';
                    $row_wall = null; //bug

                    foreach ($attach_arr as $attach_file) {
                        $attach_type = explode('|', $attach_file);

                        //Фото со стены сообщества
                        if ($attach_type[0] == 'photo' and file_exists(__DIR__ . "/../../public/uploads/groups/{$rec_info['tell_uid']}/photos/c_{$attach_type[1]}")) {
                            if ($cnt_attach < 2) $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$rec_info['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$rec_info['tell_uid']}/photos/{$attach_type[1]}\"  alt=\"\" /></div>"; else
                                $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/groups/{$rec_info['tell_uid']}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$rec_info['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                            $cnt_attach++;

                            $resLinkTitle = '';

                            //Фото со стены юзера
                        } elseif ($attach_type[0] == 'photo_u') {
                            if ($rec_info['tell_uid']) $attauthor_user_id = $rec_info['tell_uid']; else $attauthor_user_id = $row['ac_user_id'];
                            if ($attach_type[1] == 'attach' and file_exists(__DIR__ . "/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")) {
                                if ($cnt_attach < 2) $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\"  alt=\"\" /></div>"; else
                                    $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                $cnt_attach++;
                            } elseif (file_exists(__DIR__ . "/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")) {
                                if ($cnt_attach < 2) $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['obj_id']}\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\"  alt=\"\" /></div>"; else
                                    $attach_result .= "<img id=\"photo_wall_{$row['obj_id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['obj_id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['obj_id']}\"  alt=\"\" />";

                                $cnt_attach++;
                            }

                            $resLinkTitle = '';

                            //Видео
                        } elseif ($attach_type[0] == 'video' and file_exists(__DIR__ . "/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")) {

                            $for_cnt_attach_video = explode('video|', $rec_info['attach']);
                            $cnt_attach_video = count($for_cnt_attach_video) - 1;

                            if ($cnt_attach_video == 1 and preg_match('/(photo|photo_u)/i', $rec_info['attach']) == false) {

                                $video_id = intval($attach_type[2]);

                                $row_video = $News->video_info($video_id);
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
                        } elseif ($attach_type[0] == 'audio') {
                            $audioId = intval($attach_type[1]);
                            $audioInfo = $News->audio_info($audioId);
                            if ($audioInfo) {
                                $jid++;
                                $attach_result .= '<div class="audioForSize' . $row['obj_id'] . ' player_mini_mbar_wall_all" id="audioForSize"><div class="audio_onetrack audio_wall_onemus"><div class="audio_playic cursor_pointer fl_l" onClick="music.newStartPlay(\'' . $jid . '\', ' . $row['obj_id'] . ')" id="icPlay_' . $row['obj_id'] . $jid . '"></div><div id="music_' . $row['obj_id'] . $jid . '" data="' . $audioInfo['url'] . '" class="fl_l" style="margin-top:-1px"><a href="/?go=search&type=5&query=' . $audioInfo['artist'] . '&n=1" onClick="Page.Go(this.href); return false"><b>' . stripslashes($audioInfo['artist']) . '</b></a> &ndash; ' . stripslashes($audioInfo['title']) . '</div><div id="play_time' . $row['obj_id'] . $jid . '" class="color777 fl_r no_display" style="margin-top:2px;margin-right:5px">00:00</div><div class="player_mini_mbar fl_l no_display player_mini_mbar_wall player_mini_mbar_wall_all" id="ppbarPro' . $row['obj_id'] . $jid . '"></div></div></div>';
                            }

                            $resLinkTitle = '';

                            //Смайлик
                        } elseif ($attach_type[0] == 'smile' and file_exists(__DIR__ . "/../../public/uploads/smiles/{$attach_type[1]}")) {
                            $attach_result .= '<img src=\"/uploads/smiles/' . $attach_type[1] . '\" />';

                            $resLinkTitle = '';
                            //Если ссылка
                        } elseif ($attach_type[0] == 'link' and preg_match('/https:\/\/(.*?)+$/i', $attach_type[1]) and $cnt_attach_link == 1 and stripos(str_replace('https://www.', 'https://', $attach_type[1]), $config['home_url']) === false) {
//                                    $count_num = count($attach_type);
                            $domain_url_name = explode('/', $attach_type[1]);
                            $rdomain_url_name = str_replace('https://', '', $domain_url_name[2]);

                            $attach_type[3] = stripslashes($attach_type[3]);
                            $attach_type[3] = substr($attach_type[3], 0, 200);

                            $attach_type[2] = stripslashes($attach_type[2]);
                            $str_title = substr($attach_type[2], 0, 55);

                            if (stripos($attach_type[4], '/uploads/attach/') === false) {
                                $attach_type[4] = '/images/no_ava_groups_100.gif';
                                $no_img = false;
                            } else
                                $no_img = true;

                            if (!$attach_type[3]) $attach_type[3] = '';

                            if ($no_img and $attach_type[2]) {
                                if ($rec_info['tell_comm']) $no_border_link = 'border:0';

                                $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url=' . $attach_type[1] . '" target="_blank">' . $rdomain_url_name . '</a></div></div><div class=""></div><div class="wall_show_block_link" style="' . $no_border_link . '"><a href="/away.php?url=' . $attach_type[1] . '" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="' . $attach_type[4] . '"  alt=""/></div></a><div class="attatch_link_title"><a href="/away.php?url=' . $attach_type[1] . '" target="_blank">' . $str_title . '</a></div><div style="max-height:50px;overflow:hidden">' . $attach_type[3] . '</div></div></div>';

                                $resLinkTitle = $attach_type[2];
                                $resLinkUrl = $attach_type[1];
                            } elseif ($attach_type[1] and $attach_type[2]) {
                                $attach_result .= '<div style="margin-top:2px" class=""><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url=' . $attach_type[1] . '" target="_blank">' . $rdomain_url_name . '</a></div></div></div><div class=""></div>';

                                $resLinkTitle = $attach_type[2];
                                $resLinkUrl = $attach_type[1];
                            }

                            $cnt_attach_link++;

                            //Если документ
                        } elseif ($attach_type[0] == 'doc') {

                            $doc_id = (int)$attach_type[1];

                            $row_doc = $News->doc_info($doc_id);

                            if ($row_doc) {

                                $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class=""><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did=' . $doc_id . '" target="_blank" onMouseOver="myhtml.title(\'' . $doc_id . $cnt_attach . $row['obj_id'] . '\', \'<b>Размер файла: ' . $row_doc['dsize'] . '</b>\', \'doc_\')" id="doc_' . $doc_id . $cnt_attach . $row['obj_id'] . '">' . $row_doc['dname'] . '</a></div></div></div><div class=""></div>';

                                $cnt_attach++;
                            }

                            //Если опрос
                        } elseif ($attach_type[0] == 'vote') {

                            $vote_id = (int)$attach_type[1];

                            $row_vote = $News->video_info($vote_id);

                            if ($vote_id) {
                                $checkMyVote = $News->vote_info_check($vote_id, $user_id);

                                $row_vote['title'] = stripslashes($row_vote['title']);

                                if (!$row_wall['text']) $row_wall['text'] = $row_vote['title'];

                                $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                $max = $row_vote['answer_num'];

                                $queryanswer = $News->vote_info_answer($vote_id);
                                $answer = array();
                                foreach ($queryanswer as $row_answer) {

                                    $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                                }

                                $attach_result .= "<div class=\"\" style=\"height:10px\"></div><div id=\"result_vote_block{$vote_id}\"><div class=\"wall_vote_title\">{$row_vote['title']}</div>";

                                $aiMax = count($arr_answe_list);
                                for ($ai = 0; $ai < $aiMax; $ai++) {

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
                                                    </div><div class=\"\"></div>";

                                    }

                                }
                                $titles = array('человек', 'человека', 'человек');//fave
                                if ($row_vote['answer_num']) $answer_num_text = Gramatic::declOfNum($row_vote['answer_num'], $titles); else $answer_num_text = 'человек';

                                if ($row_vote['answer_num'] <= 1) $answer_text2 = 'Проголосовал'; else $answer_text2 = 'Проголосовало';

                                $attach_result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"\" style=\"margin-top:10px\"></div></div>";

                            }

                        } else

                            $attach_result .= '';

                    }

                    if ($resLinkTitle and $row['action_text'] == $resLinkUrl or !$row['action_text']) $row['action_text'] = $resLinkTitle . $attach_result; elseif ($attach_result) $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']) . $attach_result;
                    else
                        $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);
                } else {
                    $row['action_text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row['action_text']);
                }


                $resLinkTitle = '';

                //Если это запись с "рассказать друзьям"
                if ($rec_info['tell_uid']) {
                    if ($rec_info['public']) {
                        $rowUserTell = $News->user_tell_info($rec_info['tell_uid'], 2);
                    } else {
                        $rowUserTell = $News->user_tell_info($rec_info['tell_uid'], 1);
                    }

//                    $server_time = \Sura\Libs\Tools::time();

                    if (is_int($rec_info['tell_date'])) {
                        $dateTell = \Sura\Time\Date::megaDate((int)$rec_info['tell_date']);
                    } else {
                        $dateTell = 'N/A';
                    }

                    if ($rec_info['public']) {
                        $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                        $tell_link = 'public';
                        if ($rowUserTell['photo']) {
                            $avaTell = '/uploads/groups/' . $rec_info['tell_uid'] . '/50_' . $rowUserTell['photo'];
                        } else {
                            $avaTell = '/images/no_ava_50.png';
                        }
                    } else {
                        $tell_link = 'u';
                        if ($rowUserTell['user_photo']) {
                            $avaTell = '/uploads/users/' . $rec_info['tell_uid'] . '/50_' . $rowUserTell['user_photo'];
                        } else {
                            $avaTell = '/images/no_ava_50.png';
                        }
                    }

                    if ($rec_info['tell_comm']) {
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

                $query[$key]['comment'] = stripslashes($row['action_text']);

                //Если есть комменты к записи, то выполняем след. действия
                if ($rec_info['fasts_num']) {
                    $query[$key]['comments_link'] = false;
                } else {
                    $query[$key]['comments_link'] = true;
                }

                if ($user_privacy['val_wall3'] == 1 or $user_privacy['val_wall3'] == 2 and $check_friend or $user_id == $row['ac_user_id']) {
                    $query[$key]['comments_link'] = true;
                } else {
                    $query[$key]['comments_link'] = false;
                }

                if ($rec_info['type']) $query[$key]['action_type_updates'] = $rec_info['type']; else {
                    $query[$key]['action_type_updates'] = '';
                }

                //Мне нравится
                if (stripos($rec_info['likes_users'], "u{$user_id}|") !== false) {
                    $query[$key]['yes_like'] = 'public_wall_like_yes';
                    $query[$key]['yes_like_color'] = 'public_wall_like_yes_color';
                    $query[$key]['like_js_function'] = 'groups.wall_remove_like(' . $row['obj_id'] . ', ' . $user_id . ', ' . $action_type . ')';
                } else {
                    $query[$key]['yes_like'] = '';
                    $query[$key]['yes_like_color'] = '';
                    $query[$key]['like_js_function'] = 'groups.wall_add_like(' . $row['obj_id'] . ', ' . $user_id . ', ' . $action_type . ')';
                }

                if ($rec_info['likes_num']) {
                    $query[$key]['likes'] = $rec_info['likes_num'];
                    $titles = array('человеку', 'людям', 'людям');//like
                    $query[$key]['likes_text'] = '<span id="like_text_num' . $row['obj_id'] . '">' . $rec_info['likes_num'] . '</span> ' . Gramatic::declOfNum((int)$rec_info['likes_num'], $titles);
                } else {
                    $query[$key]['likes'] = '';
                    $query[$key]['likes_text'] = '<span id="like_text_num' . $row['obj_id'] . '">0</span> человеку';
                }

                $query[$key]['rec_id'] = $row['obj_id'];
                $query[$key]['record'] = true;
                $query[$key]['wall'] = true;
                $query[$key]['wall_func'] = true;
                $query[$key]['groups'] = false;
                $query[$key]['comment'] = false;
                $query[$key]['comment_form'] = false;
                $query[$key]['all_comm'] = false;


                //Если есть комменты, то выводим и страница не "ответы"
                if ($user_privacy['val_wall3'] == 1 or $user_privacy['val_wall3'] == 2 and $check_friend or $user_id == $row['ac_user_id']) {
                    //Помещаем все комменты в id wall_fast_block_{id} это для JS
//                            $tpl->result['content'] .= '<div id="wall_fast_block_'.$row['obj_id'].'">';


                    if ($rec_info['fasts_num']) {
                        if ($rec_info['fasts_num'] > 3) {
                            $comments_limit = $rec_info['fasts_num'] - 3;
                        } else {
                            $comments_limit = 0;
                        }


                        $querycomments = $News->comments($row['obj_id'], $comments_limit);

                        //Загружаем кнопку "Показать N запсии"
                        /** @var  $num - BUGFIX */
                        $num = (int)$rec_info['fasts_num'] - 3;
                        if ($num < 0) {
                            $num = 0;
                        }
                        $titles = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                        $prev = Gramatic::declOfNum($num, $titles);
                        $titles = array('комментарий', 'комментария', 'комментариев');//comments
                        $comments = Gramatic::declOfNum($num, $titles);
                        $query[$key]['gram_record_all_comm'] = $prev . ' ' . $num . ' ' . $comments;

                        if ($rec_info['fasts_num'] < 4) {
                            $query[$key]['all_comm'] = false;
                        } else {
                            $query[$key]['rec_id'] = $row['obj_id'];
                            $query[$key]['all_comm'] = true;
                        }
//                        $query[$key]['author_id '] = $row['ac_user_id'];
                        $query[$key]['wall_func'] = true;
                        $query[$key]['groups'] = false;
//                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                        $query[$key]['record'] = false;
//                                $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                        $query[$key]['comment_form'] = false;
//                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                        $query[$key]['comment'] = false;
//                                $tpl->compile('content');
                        $config = Settings::load();

                        //Собственно выводим комменты
                        foreach ($querycomments as $key2 => $row_comments) {
//                                    $tpl->set('{name}', $row_comments['user_search_pref']);
                            $querycomments[$key2]['name'] = $row_comments['user_search_pref'];
                            if ($row_comments['user_photo']) {
                                $querycomments[$key2]['ava'] = $config["home_url"] . 'uploads/users/' . $row_comments['author_user_id'] . '/50_' . $row_comments['user_photo'];
                            } else {
                                $querycomments[$key2]['ava'] = '/images/no_ava_50.png';
                            }
                            $querycomments[$key2]['rec_id'] = $row['obj_id'];
                            $querycomments[$key2]['comm_id'] = $row_comments['id'];
                            $querycomments[$key2]['user_id'] = $row_comments['author_user_id'];

                            $expBR2 = explode('<br />', $row_comments['text']);
                            $textLength2 = count($expBR2);
                            $strTXT2 = strlen($row_comments['text']);
                            if ($textLength2 > 6 or $strTXT2 > 470) {
                                $querycomments[$key2]['text'] = '<div class="wall_strlen" id="hide_wall_rec' . $row_comments['id'] . '" style="max-height:102px"">' . $row_comments['text'] . '</div><div class="wall_strlen_full" onMouseDown="wall.FullText(' . $row_comments['id'] . ', this.id)" id="hide_wall_rec_lnk' . $row_comments['id'] . '">Показать полностью..</div>';
                            }

                            //Обрабатываем ссылки
                            $querycomments[$key2]['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $querycomments[$key2]['text']);

                            $querycomments[$key2]['text'] = stripslashes($row_comments['text']);
//                            $querycomments[$key2] = ;
                            $querycomments[$key2]['date'] = \Sura\Time\Date::megaDate((int)$row_comments['add_date']);

                            $querycomments[$key2]['owner'] = false;//FIXME

                            if ($user_id == $row_comments['author_user_id']) {
                                $querycomments[$key2]['owner'] = true;
                            } else {
                                $querycomments[$key2]['owner'] = false;
                            }
                            if ($user_id == $row_comments['author_user_id']) {
                                $querycomments[$key2]['not_owner'] = false;
                            } else {
                                $querycomments[$key2]['not_owner'] = true;
                            }
//                                    $tpl->set('[comment]', '');
//                                    $tpl->set('[/comment]', '');
                            $query[$key]['comment'] = true;
//                                    $tpl->set('[wall-func]', '');
//                                    $tpl->set('[/wall-func]', '');
                            $query[$key]['wall_func'] = true;
//                                    $tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
                            $query[$key]['groups'] = false;
//                                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $query[$key]['record'] = false;
//                                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                            $query[$key]['comment_form'] = false;
//                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                            $query[$key]['all_comm'] = false;
//                                    $tpl->compile('content');
                        }

                        $query[$key]['comments'] = $querycomments;
//
//                        //Загружаем форму ответа
////                                $tpl->set('{rec-id}', $row['obj_id']);
//                        $query[$key]['rec_id'] = $row['obj_id'];
////                                $tpl->set('{author-id}', $row['ac_user_id']);
//                        $query[$key]['author_id'] = $row['ac_user_id'];
////                                $tpl->set('[comment-form]', '');
////                                $tpl->set('[/comment-form]', '');
//                        $query[$key]['comment_form'] = true;
////                                $tpl->set('[wall-func]', '');
////                                $tpl->set('[/wall-func]', '');
//                        $query[$key]['wall_func'] = true;
////                                $tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
//                        $query[$key]['groups'] = false;
////                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
//                        $query[$key]['record'] = false;
////                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
//                        $query[$key]['comment'] = false;
////                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
//                        $query[$key]['all_comm'] = false;
//                                $tpl->compile('content');
                    }
                }
            } else {
                $rowInfoUser = $News->row_type11($row['ac_user_id'], 1);

                $row['user_search_pref'] = $rowInfoUser['user_search_pref'];
                $row['user_last_visit'] = $rowInfoUser['user_last_visit'];
                $row['user_logged_mobile'] = $rowInfoUser['user_logged_mobile'];
                $row['user_photo'] = $rowInfoUser['user_photo'];
                $row['user_sex'] = $rowInfoUser['user_sex'];
                $row['user_privacy'] = $rowInfoUser['user_privacy'];
//                        $query[$key]['user'] =
//                        $tpl->set('{link}', 'u');
                $query[$key]['link'] = 'u';

                if ($row['user_photo']) {
//                            $tpl->set('{ava}', '/uploads/users/'.$row['ac_user_id'].'/50_'.$row['user_photo']);
                    $query[$key]['ava'] = '/uploads/users/' . $row['ac_user_id'] . '/50_' . $row['user_photo'];
                } else {
//                            $tpl->set('{ava}', '/images/no_ava_50.png');
                    $query[$key]['ava'] = '/images/no_ava_50.png';
                }

                $query[$key]['record'] = true;
                $query[$key]['comment'] = false;
                $query[$key]['wall'] = false;
                $query[$key]['comment_form'] = false;
                $query[$key]['all_comm'] = false;
                $query[$key]['comments_link'] = false;

//                        if($action_cnt){
//                            $tpl->compile('content');
//                        }
            }
        }
        return $query;
    }
}