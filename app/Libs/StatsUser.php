<?php


namespace App\Libs;

use Sura\Libs\Db;
use Sura\Time\Date;

/**
 * Class StatsUser
 * @package App\Libs
 */
class StatsUser
{

//    public int $id;

    /**
     * StatsUser constructor.
     * @param int $id
     */
    public function __construct(
        int $id
    )
    {

    }

    /**
     * @param $user_id
     * @return bool
     */
    public function add(int $user_id): bool
    {
        $db = Db::getDB();

        $id = $this->id;

        $stat_date = date('Ymd', Date::time());
        $stat_x_date = date('Ym', Date::time());

        $check_user_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `users_stats_log` WHERE user_id = '{$user_id}' AND for_user_id = '{$id}' AND date = '{$stat_date}'");

        if (!$check_user_stat['cnt']) {
            $check_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `users_stats` WHERE user_id = '{$id}' AND date = '{$stat_date}'");
            if ($check_stat['cnt']) {
                $db->query("UPDATE `users_stats` SET users = users + 1, views = views + 1 WHERE user_id = '{$id}' AND date = '{$stat_date}'");
            }
            else {
                $db->query("INSERT INTO `users_stats` SET user_id = '{$id}', date = '{$stat_date}', users = '1', views = '1', date_x = '{$stat_x_date}'");
            }
            $db->query("INSERT INTO `users_stats_log` SET user_id = '{$user_id}', date = '{$stat_date}', for_user_id = '{$id}'");
        } else {
            $db->query("UPDATE `users_stats` SET views = views + 1 WHERE user_id = '{$id}' AND date = '{$stat_date}'");
        }

        return true;
    }
}