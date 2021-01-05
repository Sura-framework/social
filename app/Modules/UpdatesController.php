<?php

namespace App\Modules;

use Exception;

class UpdatesController extends Module {

    /**
     * Моментальные оповещания
     *
     * @throws \JsonException
     */
    public function index(): string
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $user_id = $user_info['user_id'];

            $Cache = cache_init(array('type' => 'file'));
            try {
                $cntCacheUp = $Cache->get("users/{$user_id}/updates", $default = null);

                if($cntCacheUp){
                    $server_time = (int)$_SERVER['REQUEST_TIME'];
                    $update_time = $server_time - 70;
                    $row = $db->super_query("SELECT id, type, from_user_id, text, lnk, user_search_pref, user_photo FROM `updates` WHERE for_user_id = '{$user_id}' AND date > '{$update_time}' ORDER by `date`");
                    if($row){
                        if($row['user_photo']) {
                            $ava = "/uploads/users/{$row['from_user_id']}/50_{$row['user_photo']}";
                        } 
                        else {
                            $ava = "/images/no_ava_50.png";
                        } 
                        $row['text'] = str_replace("|", "&#124;", $row['text']);
                        $res = array(
                            'type' => '14', 
                            'name' => 'fccv', 
                            'id' => '14', 
                            'text' => stripslashes('Hello world'), 
                            'time' => $server_time, 
                            'ava' => $ava, 
                            'link' => $row['lnk'], 
                        );
                        header('Content-Type: application/json');
                        return _e( json_encode(array('res' => $res), JSON_THROW_ON_ERROR));

                        // echo $row['type'].'|'.$row['user_search_pref'].'|'.$row['from_user_id'].'|'.stripslashes($row['text']).'|'.$server_time.'|'.$ava.'|'.$row['lnk'];
                        $db->query("DELETE FROM `updates` WHERE id = '{$row['id']}'");
                    } else{
                        $Cache->set("users/{$user_id}/updates", '');

                        header('Content-Type: application/json');
                        return _e( json_encode(array('error' => 'error'), JSON_THROW_ON_ERROR) );
                    }
                }
            }catch (Exception $e){
                header('Content-Type: application/json');
                return _e( json_encode(array('error' => 'error'), JSON_THROW_ON_ERROR) );
            }
        }
        header('Content-Type: application/json');
        return _e( json_encode(array('error' => 'error'), JSON_THROW_ON_ERROR) );
    }
}