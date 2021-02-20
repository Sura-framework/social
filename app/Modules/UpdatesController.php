<?php

declare(strict_types=1);

namespace App\Modules;

use JsonException;
use Sura\Cache\Cache;
use Sura\Cache\Storages\MemcachedStorage;
use Sura\Time\Date;
use Sura\Libs\Status;
use Throwable;

class UpdatesController extends Module
{

    /**
     * Моментальные оповещения
     *
     * @throws JsonException
     * @throws Throwable
     */
    public function index(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if ($logged) {
            $user_id = $user_info['user_id'];

            $storage = new MemcachedStorage('localhost');
            $cache = new Cache($storage, 'users');
//
//            $cntCacheUp = $cache->load("{$user_id}/updates");
            $cntCacheUp2 = true;

            if ($cntCacheUp2) {
                $server_time = Date::time();
                $update_time = $server_time - 70;
                $row = $db->super_query("SELECT id, type, from_user_id, text, lnk, user_search_pref, user_photo FROM `updates` WHERE for_user_id = '{$user_id}' AND date > '{$update_time}' ORDER by `date`");
                if ($row) {
                    if ($row['user_photo']) {
                        $ava = "/uploads/users/{$row['from_user_id']}/50_{$row['user_photo']}";
                    } else {
                        $ava = "/images/no_ava_50.png";
                    }
                    $row['text'] = str_replace("|", "&#124;", $row['text']);
                    $res = array(
                        'type' => $row['type'],
                        'name' => $row['user_search_pref'],
                        'id' => $row['from_user_id'],
                        'text' => stripslashes($row['text']),
                        'time' => $server_time,
                        'ava' => $ava,
                        'link' => $row['lnk'],
                    );
                    $db->query("DELETE FROM `updates` WHERE id = '{$row['id']}'");

                    $status = Status::OK;
//                        return _e( json_encode(array('res' => $res), JSON_THROW_ON_ERROR));

                    // echo $row['type'].'|'.$row['user_search_pref'].'|'.$row['from_user_id'].'|'.stripslashes($row['text']).'|'.$server_time.'|'.$ava.'|'.$row['lnk'];
//                        $res = $row['type'].'|'.$row['user_search_pref'].'|'.$row['from_user_id'].'|'.stripslashes($row['text']).'|'.$server_time.'|'.$ava.'|'.$row['lnk'];
                } else {
                    $cache->save("{$user_id}/updates", '');

                    $status = Status::NOT_FOUND;
                    $res = false;
                }
            } else {
                $status = Status::NOT_FOUND;
                $res = false;
            }
        } else {
            $status = Status::BAD_LOGGED;
            $res = false;
        }
        return _e_json(array('status' => $status, 'res' => $res));
    }
}