<?php
/* 
	Appointment: Просмотр видео и комментари к видео
	File: video.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Modules;

use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Gramatic;
use System\Libs\Tools;

class VideoController extends Module{

    public function index()
    {
        $tpl = Registry::get('tpl');

        $db = $this->db();
        // $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        $vid = intval($_POST['vid']);
        $close_link = $_POST['close_link'];

        //Выводи данные о видео если оно есть
        $row = $db->super_query("SELECT tb1.video, title, download, add_date, descr, owner_user_id, views, comm_num, privacy, public_id, tb2.user_search_pref FROM `".PREFIX."_videos` tb1, `".PREFIX."_users` tb2 WHERE tb1.id = '{$vid}' AND tb1.owner_user_id = tb2.user_id");

        if($row){
            //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
            if($user_id != $get_user_id)
                $check_friend = CheckFriends($row['owner_user_id']);

            //Blacklist
            $CheckBlackList = CheckBlackList($row['owner_user_id']);

            //Приватность
            if(!$CheckBlackList AND $row['privacy'] == 1 OR $row['privacy'] == 2 AND $check_friend OR $user_info['user_id'] == $row['owner_user_id'])
                $privacy = true;
            else
                $privacy = false;

            if($privacy){
                $config = include __DIR__.'/../data/config.php';

                //Выводим комментарии если они есть
                if($row['comm_num'] AND $config['video_mod_comm'] == 'yes'){

                    if($row['public_id']){

                        $infoGroup = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

                        if(strpos($infoGroup['admin'], "u{$user_id}|") !== false) $public_admin = true;
                        else $public_admin = false;

                    }

                    if($row['comm_num'] > 3)
                        $limit_comm = $row['comm_num']-3;
                    else
                        $limit_comm = 0;

                    $sql_comm = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `".PREFIX."_videos_comments` tb1, `".PREFIX."_users` tb2 WHERE tb1.video_id = '{$vid}' AND tb1.author_user_id = tb2.user_id ORDER by `add_date` ASC LIMIT {$limit_comm}, {$row['comm_num']}", 1);
                    $tpl->load_template('videos/comment.tpl');
                    foreach($sql_comm as $row_comm){

                        $online = Online($row_comm['user_last_visit'], $row_comm['user_logged_mobile']);
                        $tpl->set('{online}', $online);

                        $tpl->set('{uid}', $row_comm['author_user_id']);
                        $tpl->set('{author}', $row_comm['user_search_pref']);
                        $tpl->set('{comment}', stripslashes($row_comm['text']));
                        $tpl->set('{id}', $row_comm['id']);

                        $date = megaDate(strtotime($row_comm['add_date']));
                        $tpl->set('{date}', $date);

                        if($row_comm['author_user_id'] == $user_id || $row['owner_user_id'] == $user_id || $public_admin){
                            $tpl->set('[owner]', '');
                            $tpl->set('[/owner]', '');
                        } else
                            $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                        if($row_comm['user_photo'])
                            $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comm['author_user_id'].'/50_'.$row_comm['user_photo']);
                        else
                            $tpl->set('{ava}', '/images/no_ava_50.png');
                        $tpl->compile('comments');
                    }
                }


                $sql_playlist = $db->super_query("SELECT id, photo, title, views FROM `".PREFIX."_videos` WHERE owner_user_id = '{$row['owner_user_id']}' ORDER by id DESC LIMIT 0, 5", 1);

                $vplaylist = '';
                foreach($sql_playlist as $row_playlist){

                    if($vid == $row_playlist['id']) $active = ' active';
                    else $active = '';

                    $vplaylist .= '<a class="plvideo'.$row_playlist['id'].'" href="/video'.$row['owner_user_id'].'_'.$row_playlist['id'].'" onclick="$(`.video_view`).remove(); videos.show('.$row_playlist['id'].', this.href); return false;"><div class="plvideo'.$row_playlist['id'].' videopl'.$active.'" style="display: flex;font-size: 12px;padding: 6px 5px 6px 10px;">
                    <div style="margin-right: 7px;"><img style="width:100;height:55px;" src="'.$row_playlist['photo'].'"/></div>
                    <div>
                    <div style="max-height: 34px;margin-bottom: 2px;display: -webkit-box;-webkit-line-clamp: 2;-webkit-box-orient: vertical;overflow: hidden;text-overflow: ellipsis;line-height: 17px;color: #fff;opacity: 0.7;">'.$row_playlist['title'].'</div>
                    <div style="color: #fff;opacity: 0.35;">'.$row_playlist['views'].' '.declofnum($row_playlist['views'], array('просмотр','просмотра','просмотров')).'</div>
                    </div>
                    </div></a>';
                }


                if($row['download'] == '1') {

                    $tpl->load_template('videos/show.tpl');
                    $tpl->set('{photo}', $row['photo']);
                    //

                    $video_strlen = mb_strlen($row['video']);

                    $video_str_count = $video_strlen - 4;

                    $video_w_patch = substr($row['video'], 0, $video_str_count);

                    $video_patch = str_replace($config['home_url'], '', $video_w_patch);


                    $modules_dir = dirname (__FILE__);
                    $root_dir = str_replace('/system/modules', '', $modules_dir);
                    $check_converted = $db->super_query("SELECT id FROM `".PREFIX."_videos_decode` WHERE video = '".$root_dir."/public/".$video_patch.".mp4'");


                    if (file_exists(__DIR__.'/../../public/'.$video_patch.'_240.mp4')) {
                        $tpl->set('{video_240}', '<source src="'.$video_w_patch.'_240.mp4" type="video/mp4" size="240" />');
                    }else{
                        $tpl->set('{video_240}', '<!--  '.printf($check_converted).' -->');
                    }

                    if (file_exists(__DIR__.'/../../public/'.$video_patch.'_720.mp4')) {
                        $tpl->set('{video_720}', '<source src="'.$video_w_patch.'_720.mp4" type="video/mp4" size="720" />');
                    }else{
                        $tpl->set('{video_720}', '');
                    }
                    // $tpl->set('{video_1080}', '<source src="'.$video_w_patch.'_240.mp4" type="video/mp4" size="320" />');
                    $tpl->set('{video}', '<source src="'.$row['video'].'" type="video/mp4" size="1080" />');
                }else {
                    $tpl->load_template('videos/full.tpl');
                    $row['video'] = str_replace('960','800',$row['video']);
                    $tpl->set('{video}', $row['video']);
                }
                $tpl->set('{vid}', $vid);


                $tpl->set('{vplaylist}', $vplaylist);
                $type = explode('.', $row['video']);
                $tpl->set('{type}', $type[count($type)-1]);

                $tpl->set('{vid}', $vid);
                if($row['views']) $tpl->set('{views}', $row['views'].' '.Gramatic::gram_record($row['views'], 'video_views').'<br /><br />'); else $tpl->set('{views}', '');
                $tpl->set('{title}', stripslashes($row['title']));
                $tpl->set('{descr}', stripslashes($row['descr']));
                $tpl->set('{author}', $row['user_search_pref']);
                $tpl->set('{uid}', $row['owner_user_id']);
                $tpl->set('{comments}', $tpl->result['comments']);
                $tpl->set('{comm-num}', $row['comm_num']);
                $tpl->set('{owner-id}', $row['owner_user_id']);
                $tpl->set('{close-link}', $close_link);
                $date = megaDate(strtotime($row['add_date']));
                $tpl->set('{date}', $date);

                if($row['owner_user_id'] == $user_id){
                    $tpl->set('[owner]', '');
                    $tpl->set('[/owner]', '');
                    $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                } else {
                    $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                    $tpl->set('[not-owner]', '');
                    $tpl->set('[/not-owner]', '');
                }

                if($row['public_id']){

                    $tpl->set_block("'\\[public\\](.*?)\\[/public\\]'si","");

                } else {

                    $tpl->set('[public]', '');
                    $tpl->set('[/public]', '');

                }

                if($config['video_mod_add_my'] == 'no')
                    $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");

                $tpl->set('{prev-text-comm}', Gramatic::gram_record(($row['comm_num']-3), 'prev').' '.($row['comm_num']-3).' '.Gramatic::gram_record(($row['comm_num']-3), 'comments'));
                if($row['comm_num'] < 4)
                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                else {
                    $tpl->set('[all-comm]', '');
                    $tpl->set('[/all-comm]', '');
                }

                if($config['video_mod_comm'] == 'yes'){
                    $tpl->set('[admin-comments]', '');
                    $tpl->set('[/admin-comments]', '');
                } else
                    $tpl->set_block("'\\[admin-comments\\](.*?)\\[/admin-comments\\]'si","");

                $tpl->compile('content');
                Tools::AjaxTpl($tpl);

                $db->query("UPDATE LOW_PRIORITY `".PREFIX."_videos` SET views = views+1 WHERE id = '".$vid."'");
            } else
                echo 'err_privacy';
        } else
            echo 'no_video';

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}