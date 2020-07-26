<?php

namespace App\Modules;


use Sura\Libs\Tools;

class TagsController  extends Module
{

    public function Index($params)
    {
        Tools::NoAjaxRedirect();

        //$lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();

        if (isset($_GET['id']) AND isset($_GET['rand']) AND isset($_GET['type'])){
            $id = intval($_GET['id']);
            $rand = intval($_GET['rand']);
            $type = intval($_GET['type']);
        }else{
            $id = intval($_POST['id']);
            $rand = intval($_POST['rand']);
            $type = intval($_POST['type']);
        }

        if($type == 1){
            $row = $db->super_query("SELECT user_id, user_search_pref, user_status, user_photo FROM `users` WHERE user_id = '{$id}'");
            $name = $row['user_search_pref'];
            $status = $row['user_status'];
            $photo = $row['user_photo'];
            $link = 'u'.$id;
            $check2 = $db->super_query("SELECT for_user_id FROM `friends_demands` WHERE for_user_id = '{$id}' AND from_user_id = '{$user_info['user_id']}'");
            $check1 = $db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_info['user_id']}' AND friend_id = '{$id}' AND subscriptions = 0");

            if($id == $user_info['user_id']){
                $button = '<a href="/settings/" class="btn btn-secondary" onclick="Page.Go(this.href); return false;">Настройки</a><button class="btn btn-secondary ml-1" onclick="Profile_edit.Open()">Редактировать профиль</button>';
            } elseif($check1){
                $button = '<button class="btn btn-secondary">У вас в друзьях</button>';
            } elseif($check2){
                $button = '<button class="btn btn-secondary">Вы отправили заявку в друзья</button>';
            } elseif(!$check2){
                $button = '<button class="btn btn-secondary">Добавить в друзья</button>';
            }
        } else {
            $row = $db->super_query("SELECT id, title, traf, photo FROM `communities` WHERE id = '{$id}'");
            $name = $row['title'];
            $status = $row['traf'].' '.gram_record($row['traf'], 'subscribers');
            $photo = $row['photo'];
            $link = 'public'.$id;
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `friends` WHERE friend_id = '{$id}' AND user_id = '{$user_info['user_id']}' AND subscriptions = 2");

            if($check['cnt']){
                $button = '<button  class="btn btn-secondary">Вы подписаны</button>';
            } else {
                $button = '<button  class="btn btn-secondary">Подписаться</button>';
            }
        }
        if($photo){
            if($type == 1){
                $ava = '/uploads/users/'.$id.'/100_'.$photo;
            } else {
                $ava = '/uploads/groups/'.$id.'/100_'.$photo;
            }
        }	else {
            $ava = '/images/100_no_ava.png';
        }
        if (empty($button)) $button = '';

        if($row){
            $data = '<div class="tt_w tt_default mention_tt mention_has_actions tt_down"  onmouseover="removeTimer(\'hidetag\')" onmouseout="wall.hideTag('.$id.', '.$rand.', 1)" style="position: absolute; display: none; opacity: 1;" id="tt_wind2">
        <div class="wrapped card"><div class="card-body mention_tt_wrap ">
        <a href="/'.$link.'" class="mention_tt_photo"><img class="mention_tt_img" src="'.$ava.'" alt="'.$name.'"></a>
        <div class="mention_tt_data">
        <div class="mention_tt_title"><a class="mention_tt_name" href="/'.$link.'">'.$name.'</a></div>
        <div class="mention_tt_info">
        <div class="mention_tt_row">'.$status.'</div>
        </div>
        </div>
        </div>
        <div class="card-footer">
        '.$button.'
        </div></div></div>';
        } else {
            $data = '<div class="tt_w tt_default mention_tt mention_has_actions tt_down" style="position: absolute; display: none; opacity: 1;" id="tt_wind2">
        <div class="wrapped"><div class="mention_tt_wrap">
        <a href="/" class="mention_tt_photo"><img class="mention_tt_img" src="/images/100_no_ava.png" alt="Неизвестная страница"></a>
        <div class="mention_tt_data">
        <div class="mention_tt_title"><a class="mention_tt_name" href="/"><b>Неизвестная страница</a></a></div>
        <div class="mention_tt_info">
        <div class="mention_tt_row"></div>
        </div>
        </div>
        </div>
        <div class="mention_tt_actions">
        </div></div></div>';
        }

        $result = array(
            'data' => $data,
        );

        header('Content-Type: application/json');
        echo json_encode($result);

    }
}