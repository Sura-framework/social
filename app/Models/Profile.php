<?php

declare(strict_types=1);

namespace App\Models;

use Sura\Database\Connection;
use Sura\Libs\Gramatic;
use Sura\Libs\Langs;
use Sura\Libs\Model;
use Sura\Libs\Settings;
use Sura\Time\Date;
use Sura\Libs\Db;

class Profile
{

    private ?Db $db;

    private Connection $database;

    /**
     * Profile constructor.
     */
    public function __construct()
    {
        $this->db = Db::getDB();

        $this->database = Model::getDB();
    }

    /**
     * @param $id
     * @return array
     */
    public function user_row(int $id): array
    {
        return $this->database->fetch("SELECT user_name, user_id, user_search_pref, user_country_city_name, user_birthday, user_xfields, user_xfields_all, user_city, user_country, user_photo, user_friends_num, user_notes_num, user_subscriptions_num, user_wall_num, user_albums_num, user_last_visit, user_videos_num, user_status, user_privacy, user_sp, user_sex, user_gifts, user_public_num, user_audio, user_delet, user_ban_date, xfields, user_logged_mobile , user_cover, user_cover_pos, user_rating, user_balance, balance_rub FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function user_xfields(int $id): array
    {
        return $this->database->fetch("SELECT user_xfields FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param int $id
     * @return array
     */
    public function miniature(int $id): array
    {
        return $this->database->fetch("SELECT user_photo FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function user_online(int $id): array
    {
        return $this->database->fetch("SELECT user_last_visit, user_logged_mobile FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function friends(int $id): array
    {
        return $this->database->fetchAll("SELECT tb1.friend_id, tb2.user_search_pref, user_photo FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6");
    }

    /**
     * @param $id
     * @param $online_time
     * @return array
     */
    public function friends_online_cnt(int $id, $online_time): array
    {
        return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$id}' AND tb1.user_last_visit >= '{$online_time}' AND subscriptions = 0");
    }

    /**
     * @param $id
     * @param $online_time
     * @return array
     */
    public function friends_online(int $id, string $online_time): array
    {
        return $this->database->fetchAll("SELECT tb1.user_id, user_country_city_name, user_search_pref, user_birthday, user_photo FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$id}' AND tb1.user_last_visit >= '{$online_time}'  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6");
    }

    /**
     * @param $id
     * @param $sql_privacy
     * @param $cache_pref_videos
     * @return array
     */
    public function videos_online_cnt(int $id, string $sql_privacy, string $cache_pref_videos): array
    {
        return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0'");
    }

    /**
     * @param $id
     * @param $sql_privacy
     * @param $cache_pref_videos
     * @return array
     */
    public function videos_online(int $id, string $sql_privacy, string $cache_pref_videos): array
    {
        return $this->database->fetchAll("SELECT id, title, add_date, comm_num, photo FROM `videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0' ORDER by `add_date` DESC LIMIT 0,2");
    }

    /**
     * @param $id
     * @param $cache_pref_subscriptions
     * @return array
     */
    public function subscriptions(int $id, string $cache_pref_subscriptions): array
    {
        return $this->database->fetchAll("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_country_city_name, user_status FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 1 ORDER by `friends_date` DESC LIMIT 0,5");
    }

    /**
     * @param $id
     * @return array
     */
    public function audio(int $id): array
    {
        return $this->database->fetchAll("SELECT id, url, artist, title, duration FROM `audio` WHERE oid = '{$id}' and public = '0' ORDER by `id` DESC LIMIT 0, 3");
    }

    /**
     * @param $id
     * @param $server_time
     * @return array
     */
    public function happy_friends(int $id, $server_time): array
    {
        return $this->database->fetchAll("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_birthday FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '" . $id . "' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 AND user_day = '" . date('j', $server_time) . "' AND user_month = '" . date('n', $server_time) . "' ORDER by `user_last_visit` DESC LIMIT 0, 50");
    }

    /**
     * @param int $id
     * @return array
     */
    public function groups(int $id): array
    {

        $db = Db::getDB();
        return $this->database->fetchAll("SELECT tb1.friend_id, tb2.id, title, photo, adres, status_text FROM `friends` tb1, `communities` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT 0, 5");
    }

    /**
     * @param $id
     * @return array
     */
    public function gifts(int $id): array
    {
        return $this->database->fetchAll("SELECT gift FROM `gifts` WHERE uid = '{$id}' ORDER by `gdate` DESC LIMIT 0, 5");
    }

    /**
     * @param $id
     * @return array
     */
    public function user_sp(int $id): array
    {
        return $this->database->fetch("SELECT user_search_pref, user_sp, user_sex FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function cnt_rec(int $id): array
    {
        return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `wall` WHERE for_user_id = '{$id}' AND author_user_id = '{$id}' AND fast_comm_id = 0");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function check_subscr(int $id, int $user_id): array
    {
        return $this->database->fetch("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$id}' AND subscriptions = 1");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function check_fave(int $id, int $user_id): array
    {
        return $this->database->fetch("SELECT user_id FROM `fave` WHERE user_id = '{$user_id}' AND fave_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function row_video(int $id): array
    {
        return $this->database->fetch("SELECT video, title, download FROM `videos` WHERE id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function row_audio(int $id): array
    {
        return $this->database->fetch("SELECT id, oid, artist, title, url, duration FROM `audio` WHERE id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function row_doc(int $id): array
    {
        return $this->database->fetch("SELECT dname, dsize FROM `doc` WHERE did = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function row_vote(int $id): array
    {
        return $this->database->fetch("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$id}'");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function vote_check(int $id, int $user_id): array
    {
        return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function vote_answer(int $id): array
    {
        return $this->database->fetchAll("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$id}' GROUP BY answer");
    }

    /**
     * @param $id
     * @return array
     */
    public function author_user_id(int $id): array
    {
        return $this->database->fetch("SELECT author_user_id FROM `wall` WHERE id = '{$id}'");
    }

    /**
     * @param $user_id
     * @param $type
     * @return array
     */
    public function user_tell_info(int $user_id, int $type): array
    {
        if ($type == 1) {
            return $this->database->fetch("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");
        }
        return $this->database->fetch("SELECT title, photo FROM `communities` WHERE id = '{$user_id}'");
    }

    /**
     * @param $id
     * @param $limit
     * @return array
     */
    public function comments(int $id, int $limit): array
    {
        return $this->database->fetchAll("SELECT tb1.id, author_user_id, text, add_date, tb2.user_photo, user_search_pref FROM `wall` tb1, `users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$id}' ORDER by `add_date` LIMIT {$limit}, 3");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function count_common(int $id, int $user_id): array
    {
        return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = '{$user_id}' AND tb2.friend_id = '{$id}' AND tb1.subscriptions = 0 AND tb2.subscriptions = 0");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function mutual(int $id, int $user_id): array
    {
        return $this->database->fetchAll("SELECT tb1.friend_id, tb3.user_photo, user_search_pref FROM `users` tb3, `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = '{$user_id}' AND tb2.friend_id = '{$id}' AND tb1.subscriptions = 0 AND tb2.subscriptions = 0 AND tb1.friend_id = tb3.user_id ORDER by rand() LIMIT 0, 3");
    }

    /**
     * @param $id
     * @param $albums_privacy
     * @param $type
     * @return array
     */
    public function albums_count(int $id, string $albums_privacy, int $type): array
    {
        if ($type == 1) {
            return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `albums` WHERE user_id = '{$id}' {$albums_privacy}");
        }
        return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `albums` WHERE user_id = '{$id}' {$albums_privacy}");
    }

    /**
     * @param int $id
     * @param string $albums_privacy
     * @param $cache_pref
     * @return array
     */
    public function row_albums(int $id, string $albums_privacy, $cache_pref): array
    {
        return $this->database->fetchAll("SELECT SQL_CALC_FOUND_ROWS aid, name, adate, photo_num, cover FROM `albums` WHERE user_id = '{$id}' {$albums_privacy} ORDER by `position` ASC LIMIT 0, 3");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function friend_visit(int $id, int $user_id): array
    {
        return $this->database->fetch("UPDATE LOW_PRIORITY `friends` SET views = views+1 WHERE user_id = '{$user_id}' AND friend_id = '{$id}' AND subscriptions = 0");
    }

    /**
     * @param $user_year
     * @param $user_month
     * @param $user_day
     * @return string
     */
    public static function user_age($user_year, $user_month, $user_day): string
    {
        $server_time = Date::time();

        $current_year = date('Y', $server_time);
        $current_month = date('n', $server_time);
        $current_day = date('j', $server_time);

        $current_str = strtotime($current_year . '-' . $current_month . '-' . $current_day);
        $current_user = strtotime($current_year . '-' . $user_month . '-' . $user_day);

        if ($current_str >= $current_user) {
            $user_age = $current_year - $user_year;
        } else {
            $user_age = $current_year - $user_year - 1;
        }

        if ($user_month && $user_day) {
            $titles = array('год', 'года', 'лет');
            return $user_age . ' ' . Gramatic::declOfNum($user_age, $titles);
        }

        return '';
    }

    /**
     * @param $time
     * @param false $mobile
     * @return string
     */
    public static function Online($time, $mobile = false): string
    {
        $lang = langs::get_langs();
        $config = Settings::load();
        $server_time = (int)$_SERVER['REQUEST_TIME'];
        $online_time = $server_time - $config['online_time'];

        /** Если человек сидит с мобильнйо версии */
        if ($mobile) {
            $mobile_icon = '<img src="/images/spacer.gif" class="mobile_online"  alt=""/>';
        } else {
            $mobile_icon = '';
        }

        if ($time >= $online_time) {
            return $lang['online'] . $mobile_icon;
        }
        return '';
    }
}
