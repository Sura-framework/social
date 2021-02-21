<?php

namespace App\Modules;


use App\Libs\Friends;
use Sura\Libs\Gramatic;
use Sura\Libs\Request;
use Sura\Libs\Tools;

class TagsController  extends Module
{

    /**
     * user/group mini-box tultipe
     *
     * @throws \JsonException
     */
    public function Index(): int
    {
        Tools::NoAjaxRedirect();
        $db = $this->db();
        $user_info = $this->user_info();

        $request = (Request::getRequest()->getGlobal());

        $id = (int)$request['id'];
        $rand = (int)$request['rand'];
        $type = (int)$request['type'];
        if (!$id || !$rand || !$type){
            $row = false;
        }

        /**
         * 1 - человек
         * 2 - сообщество
         */
        if($type == 1){
            $row = $db->super_query("SELECT user_id, user_search_pref, user_photo FROM `users` WHERE user_id = '{$id}'");
            $name = $row['user_search_pref'];
            $photo = $row['user_photo'];
            $status = '';
            $link = 'u'.$id;

            $check_yes_demands = $db->super_query("SELECT for_user_id FROM `friends_demands` WHERE for_user_id = '{$id}' AND from_user_id = '{$user_info['user_id']}'");
            if($check_yes_demands['for_user_id']){
                $yesf = true;
            } else {
                $yesf = false;
            }

            $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($id);

            /** check send friend */
            $check2 = $db->super_query("SELECT for_user_id FROM `friends_demands` WHERE for_user_id = '{$id}' AND from_user_id = '{$user_info['user_id']}'");
            /** check friends */
//            $check1 = $db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_info['user_id']}' AND friend_id = '{$id}' AND subscriptions = 0");
            $check1 = (new \App\Libs\Friends)->CheckFriends($id);

            if (!$CheckBlackList){
                if($id == $user_info['user_id']){
                    $button = '<a href="/settings/" class="btn btn-secondary" onclick="Page.Go(this.href); return false;">Настройки</a><button class="btn btn-secondary ml-1" onclick="Profile_edit.Open()">Редактировать профиль</button>';
                }elseif($check1){
                    $button = '<button class="btn btn-secondary">Сообщение </button><button class="btn btn-secondary">Друзья</button>';
                }elseif($yesf){
                    $button = '<button class="btn btn-secondary">Вы уже отправили заявку</button>';
                }
                elseif($check2){
                    $button = '<button class="btn btn-secondary">Отклонить</button>';
                } else{
                    $button = '<button class="btn btn-secondary">Добавить в друзья</button>';
                }

            }else{
                $button = '<button class="btn btn-secondary">Вы заблокированы</button>';
            }

        }
        elseif($type == 2) {
            $row = $db->super_query("SELECT id, title, traf, photo FROM `communities` WHERE id = '{$id}'");
            if ($row){
                $name = $row['title'];
                $titles = array('подписчик', 'подписчика', 'подписчиков');//subscribers
                if ($row['traf'] > 0){
                    $row['traf'] = 0;
                }
                $status = $row['traf'].' '. Gramatic::declOfNum($row['traf'], $titles);
                $photo = $row['photo'];
                $link = 'public'.$id;
                $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `friends` WHERE friend_id = '{$id}' AND user_id = '{$user_info['user_id']}' AND subscriptions = 2");

                if($check['cnt']){
                    $button = '<button  class="btn btn-secondary">Вы подписаны</button>';
                } else {
                    $button = '<button  class="btn btn-secondary">Подписаться</button>';
                }
            }

            if (!isset($photo)) {
                $photo = '';//FIXME
            }
        }else{
            $photo = '';
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
        if (empty($button)) {
            $button = '';
        }

        if($row){
            $data = '<div class="tt_w tt_default mention_tt mention_has_actions tt_down"  onmouseover="removeTimer(\'hidetag\')" onmouseout="wall.hideTag('.$id.', '.$rand.', 1)" style="position: absolute; display: block; opacity: 1;" id="tt_wind2">
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
        </div></div>
        <div class="wrapped_t" style="width: auto;height: 30px;"></div>
        </div>';
        } else {
            $data = '<div class="tt_w tt_default mention_tt mention_has_actions tt_down" style="position: absolute; display: block; opacity: 1;" id="tt_wind2">
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
        </div></div>
        <div class="wrapped_t" style="width: auto;height: 30px;"></div>
        </div>';
        }

        $result = array(
            'data' => $data,
        );

        header('Content-Type: application/json');
        return _e_json($result);
    }
}