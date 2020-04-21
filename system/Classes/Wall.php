<?php
/* 
	Appointment: Класс для стены
	File: wall.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Classes;

use System\Libs\Gramatic;

class Wall {

	var $query = false;
	var $template = false;
	var $compile = false;
	var $comm_query = false;
	var $comm_template = false;
	var $comm_compile = false;
	
	function query($query){
		global $db;
		
		$this->query = $db->super_query($query, 1);
	}

	function template($template, $tpl){
		//global $tpl;
		$this->template = $tpl->load_template($template);
		return $tpl;
	}
	
	function compile($compile){
		$this->compile = $compile;
	}
	
	function select(){
		global $tpl, $db, $config, $user_id, $id, $for_user_id, $lang, $user_privacy, $check_friend, $user_info;

        $server_time = intval($_SERVER['REQUEST_TIME']);
        $config = include __DIR__.'/data/config.php';

		$this->template;
		foreach($this->query as $row_wall){
			$tpl->set('{rec-id}', $row_wall['id']);
			
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
				$jid = 0;
				$attach_result = '';
				$attach_result .= '<div class="clear"></div>';
				foreach($attach_arr as $attach_file){
					$attach_type = explode('|', $attach_file);

					//Фото со стены сообщества
					if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$row_wall['tell_uid']}/photos/c_{$attach_type[1]}")){
						if($cnt_attach < 2)
							$attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row_wall['tell_uid']}/photos/{$attach_type[1]}\" align=\"left\" /></div>";
						else
							$attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row_wall['tell_uid']}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" />";
						
						$cnt_attach++;
						
						$resLinkTitle = '';
	
					//Фото со стены юзера
					} elseif($attach_type[0] == 'photo_u'){
						if($row_wall['tell_uid']) $attauthor_user_id = $row_wall['tell_uid'];
						else $attauthor_user_id = $row_wall['author_user_id'];

						if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

							if($cnt_attach == 1)
							
								$attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\" align=\"left\" /></div>";

							else
								
								$attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" height=\"{$rodImHeigh}\" />";
								
							
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
				
						if($row_wall['tell_uid']) $attauthor_user_id = $row_wall['tell_uid'];

						if($cnt_attach_video == 1 AND preg_match('/(photo|photo_u)/i', $row_wall['attach']) == false){
							
							$video_id = intval($attach_type[2]);
							
							$row_video = $db->super_query("SELECT video, title, download FROM `".PREFIX."_videos` WHERE id = '{$video_id}'", false, "wall/video{$video_id}");
							$row_video['title'] = stripslashes($row_video['title']);
							$row_video['video'] = stripslashes($row_video['video']);
							$row_video['video'] = strtr($row_video['video'], array('width="770"' => 'width="390"', 'height="420"' => 'height="310"'));
							

							if ($row_video['download'] == '1') {
								$attach_result .= "<div class=\"cursor_pointer clear\" href=\"/video{$attauthor_user_id}_{$video_id}_sec=wall/fuser={$attauthor_user_id}\" id=\"no_video_frame{$video_id}\" onClick=\"videos.show({$video_id}, this.href, '/u{$attauthor_user_id}')\">
							<div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;max-width: 500px;\" height=\"350\" /></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div>";
							}else{
								$attach_result .= "<div class=\"cursor_pointer clear\" href=\"/video{$attauthor_user_id}_{$video_id}_sec=wall/fuser={$attauthor_user_id}\" id=\"no_video_frame{$video_id}\" onClick=\"videos.show({$video_id}, this.href, '/u{$attauthor_user_id}')\">
							<div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;max-width: 500px;\" height=\"350\" /></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div>";
							}
    

						
						} else {							
							if ($row_video['download'] == '1') {
								$attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";
							}else{
								$attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";

							}

							
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
							
							$attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away/?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="'.$no_border_link.'"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'" /></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

							$resLinkTitle = $attach_type[2];
							$resLinkUrl = $attach_type[1];
						} else if($attach_type[1] AND $attach_type[2]){
							$attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away/?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class="clear"></div>';
							
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
					$row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_wall['text']).$attach_result;
				else
					$row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_wall['text']);
			} else
				$row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_wall['text']);
				
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
			
			$tpl->set('{name}', $row_wall['user_search_pref']);
			$tpl->set('{user-id}', $row_wall['author_user_id']);

            $online = Online($row_wall['user_last_visit'], $row_wall['user_logged_mobile']);
            $tpl->set('{online}', $online);
            $date = megaDate($row_wall['add_date']);
            $tpl->set('{date}', $date);
			
			if($row_wall['user_photo'])
				$tpl->set('{ava}', '/uploads/users/'.$row_wall['author_user_id'].'/50_'.$row_wall['user_photo']);
			else
				$tpl->set('{ava}', '/images/no_ava_50.png');
			
			//Мне нравится
			if(stripos($row_wall['likes_users'], "u{$user_id}|") !== false){
				$tpl->set('{yes-like}', 'public_wall_like_yes');
				$tpl->set('{yes-like-color}', 'public_wall_like_yes_color');
				$tpl->set('{like-js-function}', 'groups.wall_remove_like('.$row_wall['id'].', '.$user_id.', \'uPages\')');
			} else {
				$tpl->set('{yes-like}', '');
				$tpl->set('{yes-like-color}', '');
				$tpl->set('{like-js-function}', 'groups.wall_add_like('.$row_wall['id'].', '.$user_id.', \'uPages\')');
			}

			if($row_wall['likes_num']){
				$tpl->set('{likes}', $row_wall['likes_num']);
                $titles = array('человеку', 'людям', 'людям');//like
				$tpl->set('{likes-text}', '<span id="like_text_num'.$row_wall['id'].'">'.$row_wall['likes_num'].'</span> '.Gramatic::declOfNum($row_wall['likes_num'], $titles));
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
			
			if($row_wall['type'])
				$tpl->set('{type}', $row_wall['type']);
			else
				$tpl->set('{type}', '');

			if(!$id)
				$id = $for_user_id;
			
			//Тег Owner означает показ записей только для владельца страницы или для того кто оставил запись
			if($user_id == $row_wall['author_user_id'] OR $user_id == $id){
				$tpl->set('[owner]', '');
				$tpl->set('[/owner]', '');
			} else
				$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

			//Показа кнопки "Рассказать др" только если это записи владельца стр.
			if($row_wall['author_user_id'] == $id AND $user_id != $id){
				$tpl->set('[owner-record]', '');
				$tpl->set('[/owner-record]', '');
			} else
				$tpl->set_block("'\\[owner-record\\](.*?)\\[/owner-record\\]'si","");
			
			//Если есть комменты к записи, то выполняем след. действия / Приватность
			if($row_wall['fasts_num']){
				$tpl->set('[if-comments]', '');
				$tpl->set('[/if-comments]', '');
				$tpl->set_block("'\\[comments-link\\](.*?)\\[/comments-link\\]'si","");
			} else {
				$tpl->set('[comments-link]', '');
				$tpl->set('[/comments-link]', '');
				$tpl->set_block("'\\[if-comments\\](.*?)\\[/if-comments\\]'si","");
			}

			//Приватность комментирования записей
			if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $id){
				$tpl->set('[privacy-comment]', '');
				$tpl->set('[/privacy-comment]', '');
			} else
				$tpl->set_block("'\\[privacy-comment\\](.*?)\\[/privacy-comment\\]'si","");
				
			$tpl->set('[record]', '');
			$tpl->set('[/record]', '');
			$tpl->set('{author-id}', $id);
			$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
			$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
			$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
			$tpl->compile($this->compile);

			//Помещаем все комменты в id wall_fast_block_{id} это для JS
			$tpl->result[$this->compile] .= '<div id="wall_fast_block_'.$row_wall['id'].'">';
				
			//Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
			if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $id){
				if($row_wall['fasts_num']){
					
					if($row_wall['fasts_num'] > 3)
						$comments_limit = $row_wall['fasts_num']-3;
					else
						$comments_limit = 0;
					
					$sql_comments = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, tb2.user_photo, user_search_pref FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$row_wall['id']}' ORDER by `add_date` ASC LIMIT {$comments_limit}, 3", 1);

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
					$tpl->set('{author-id}', $id);
					$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
					$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
					$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
					$tpl->compile($this->compile);
				
					//Сообственно выводим комменты
					foreach($sql_comments as $row_comments){
						$tpl->set('{name}', $row_comments['user_search_pref']);
						if($row_comments['user_photo'])
							$tpl->set('{ava}', '/uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo']);
						else
							$tpl->set('{ava}', '/images/no_ava_50.png');
			
						$tpl->set('{rec-id}', $row_wall['id']);
						$tpl->set('{comm-id}', $row_comments['id']);
						$tpl->set('{user-id}', $row_comments['author_user_id']);
						
						$expBR2 = explode('<br />', $row_comments['text']);
						$textLength2 = count($expBR2);
						$strTXT2 = strlen($row_comments['text']);
						if($textLength2 > 6 OR $strTXT2 > 470)
							$row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';
					
						//Обрабатываем ссылки
						$row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

						$tpl->set('{text}', stripslashes($row_comments['text']));

                        $date = megaDate($row_comments['add_date']);
                        $tpl->set('{date}', $date);
						if($user_id == $row_comments['author_user_id'] || $user_id == $id){
							$tpl->set('[owner]', '');
							$tpl->set('[/owner]', '');
						} else
							$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
					
						if($user_id == $row_comments['author_user_id'])
						
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
						$tpl->compile($this->compile);
					}

					//Загружаем форму ответа
					$tpl->set('{rec-id}', $row_wall['id']);
					$tpl->set('{author-id}', $id);
					$tpl->set('[comment-form]', '');
					$tpl->set('[/comment-form]', '');
					$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
					$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
					$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
					$tpl->compile($this->compile);
				}
			}
			
			//Закрываем блок для JS
			$tpl->result[$this->compile] .= '</div>';

		}

	}
	
	function comm_query($query){
		global $db;
		
		$this->comm_query = $db->super_query($query, 1);
	}
	
	function comm_template($template){
		global $tpl;
		$this->comm_template = $tpl->load_template($template);
	}
	
	function comm_compile($compile){
		$this->comm_compile = $compile;
	}
	
	function comm_select(){
		global $tpl, $db, $config, $user_id, $id, $for_user_id, $fast_comm_id, $record_fasts_num;
		
		if($this->comm_query){
			$this->comm_template;

			//Помещаем все комменты в id wall_fast_block_{id} это для JS
			$tpl->result[$this->compile] .= '<div id="wall_fast_block_'.$fast_comm_id.'">';

			//Загружаем кнопку "Показать N запсии" если их больше 3
			if($record_fasts_num > 3){
                $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
				$tpl->set('{gram-record-all-comm}', Gramatic::declOfNum(($record_fasts_num-3), $titles1).' '.($record_fasts_num-3).' '.Gramatic::declOfNum(($record_fasts_num-3), $titles2));
				$tpl->set('[all-comm]', '');
				$tpl->set('[/all-comm]', '');
				$tpl->set('{rec-id}', $fast_comm_id);
				$tpl->set('{author-id}', $for_user_id);
				$tpl->set('[wall-func]', '');
				$tpl->set('[/wall-func]', '');
				$tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
				$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
				$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
				$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
				$tpl->compile($this->comm_compile);
			} else
				$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");

			//Сообственно выводим комменты

            $comm_query = $this->comm_query;
			foreach($comm_query as $row_comments){
				$tpl->set('{name}', $row_comments['user_search_pref']);
				if($row_comments['user_photo'])
					$tpl->set('{ava}', '/uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo']);
				else
					$tpl->set('{ava}', '/images/no_ava_50.png');

				$tpl->set('{rec-id}', $fast_comm_id);
				$tpl->set('{comm-id}', $row_comments['id']);
				$tpl->set('{user-id}', $row_comments['author_user_id']);

				$expBR2 = explode('<br />', $row_comments['text']);
				$textLength2 = count($expBR2);
				$strTXT2 = strlen($row_comments['text']);
				if($textLength2 > 6 OR $strTXT2 > 470)
					$row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

				//Обрабатываем ссылки
				$row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

				$tpl->set('{text}', stripslashes($row_comments['text']));

                $date = megaDate($row_comments['add_date']);
                $tpl->set('{date}', $date);

				if(!$id)
					$id = $for_user_id;

				if($user_id == $row_comments['author_user_id'] || $user_id == $id){
					$tpl->set('[owner]', '');
					$tpl->set('[/owner]', '');
				} else
					$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

				if($user_id == $row_comments['author_user_id'])

					$tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");

				else {

					$tpl->set('[not-owner]', '');
					$tpl->set('[/not-owner]', '');

				}

				$tpl->set('[comment]', '');
				$tpl->set('[/comment]', '');
				$tpl->set('[wall-func]', '');
				$tpl->set('[/wall-func]', '');
				$tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
				$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
				$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
				$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
				$tpl->compile($this->comm_compile);
			}

			//Закрываем блок для JS
			$tpl->result[$this->compile] .= '</div>';

			//Загружаем форму ответа
			$tpl->set('{rec-id}', $fast_comm_id);
			$tpl->set('{author-id}', $for_user_id);
			$tpl->set('[comment-form]', '');
			$tpl->set('[/comment-form]', '');
			$tpl->set('[wall-func]', '');
			$tpl->set('[/wall-func]', '');
			$tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");
			$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
			$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
			$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
			$tpl->compile($this->comm_compile);
		}
	}
}
?>