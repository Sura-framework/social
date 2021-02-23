<?php
declare(strict_types = 1);
namespace App\Libs;

use Sura\Libs\Db;
use function Sura\resolve;

class Migrate
{
    public static function main()
    {
        $tableSchema = array();

        $dir = resolve('app')->get('path.base').'/config/';

        $files = scandir($dir,1);
        unset($files[count($files)-1]);
        unset($files[count($files)-1]);
        foreach ($files as $v){
            require_once($v);
        }

        $tableSchema[] = "CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `text` varchar(64) NOT NULL,
  `description` varchar(100) NOT NULL,
  `whads` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `typepay` int(11) NOT NULL,
  `image` text NOT NULL,
  `link` text NOT NULL,
  `views` int(11) NOT NULL,
  `click` int(11) NOT NULL,
  `balance` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `active` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `ads_settings` (
  `idad` int(11) NOT NULL,
  `country` int(11) NOT NULL,
  `city` int(11) NOT NULL,
  `sex` int(11) NOT NULL,
  `agef` int(11) NOT NULL,
  `agel` int(11) NOT NULL,
  `sp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `albums` (
  `aid` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `descr` varchar(255) NOT NULL,
  `photo_num` smallint(6) UNSIGNED NOT NULL,
  `comm_num` mediumint(8) UNSIGNED NOT NULL,
  `ahash` varchar(32) NOT NULL,
  `adate` datetime NOT NULL,
  `cover` varchar(25) NOT NULL,
  `position` smallint(6) UNSIGNED NOT NULL,
  `privacy` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `antispam` (
  `id` int(11) NOT NULL,
  `act` tinyint(3) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` int(10) NOT NULL,
  `txt` varchar(32) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `apps` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `flash` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `secret` varchar(255) NOT NULL,
  `balance` int(10) NOT NULL DEFAULT '0',
  `cols` int(11) NOT NULL DEFAULT '0',
  `width` int(5) NOT NULL DEFAULT '696',
  `height` int(5) NOT NULL DEFAULT '800',
  `status` int(2) NOT NULL DEFAULT '-1',
  `type` int(11) NOT NULL,
  `admins` varchar(255) NOT NULL DEFAULT '|0|',
  `admins_num` int(11) NOT NULL DEFAULT '1',
  `user_id` int(10) NOT NULL,
  `app` int(11) NOT NULL,
  `url_embed` text NOT NULL,
  `api_embed` text NOT NULL,
  `iframe` text NOT NULL,
  `tb1.game_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `apps_transactions` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `votes` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `whom` int(11) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `apps_users` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `balance` int(10) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `attach` (
  `id` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `ouser_id` int(11) NOT NULL,
  `acomm_num` mediumint(8) NOT NULL,
  `public_id` int(11) NOT NULL,
  `add_date` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `attach_comm` (
  `id` int(11) NOT NULL,
  `forphoto` varchar(30) NOT NULL,
  `auser_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `adate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `audio` (
  `id` int(11) UNSIGNED NOT NULL,
  `oid` int(11) UNSIGNED NOT NULL,
  `url` text NOT NULL,
  `artist` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `date` varchar(10) NOT NULL,
  `filename` text NOT NULL,
  `duration` varchar(5) NOT NULL,
  `add_count` bigint(20) NOT NULL DEFAULT 0,
  `text` text NOT NULL DEFAULT '',
  `genre` bigint(20) NOT NULL DEFAULT 0,
  `original` int(11) NOT NULL DEFAULT 0,
  `public` int(11) NOT NULL DEFAULT 0,
  `add_date` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `banned` (
  `id` int(11) NOT NULL,
  `descr` text NOT NULL,
  `date` varchar(15) NOT NULL,
  `always` smallint(4) NOT NULL,
  `ip` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `blog` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(65) NOT NULL,
  `story` text NOT NULL,
  `date` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `bugs` (
  `id` int(11) UNSIGNED NOT NULL,
  `uids` int(11) UNSIGNED NOT NULL,
  `title` text NOT NULL,
  `text` text NOT NULL,
  `images` varchar(40) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `date` varchar(15) NOT NULL,
  `admin_text` text NOT NULL,
  `admin_id` int(11) NOT NULL DEFAULT '693'
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `bugs_comments` (
  `id` int(11) NOT NULL,
  `author_user_id` int(11) NOT NULL,
  `bug_id` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  `add_date` datetime NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";



        $tableSchema[] = "CREATE TABLE `codes` (
  `id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `fbm` int(11) NOT NULL,
  `rub` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `activate` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities` (
  `id` int(11) UNSIGNED NOT NULL,
  `admin` text NOT NULL,
  `title` varchar(60) NOT NULL,
  `descr` text NOT NULL DEFAULT '',
  `cat` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(3) UNSIGNED NOT NULL,
  `traf` int(11) UNSIGNED NOT NULL,
  `ulist` text NOT NULL,
  `date` datetime NOT NULL,
  `photo` varchar(25) NOT NULL DEFAULT '',
  `feedback` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `comments` tinyint(1) UNSIGNED NOT NULL,
  `real_admin` int(11) UNSIGNED NOT NULL,
  `rec_num` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `photos_num` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `del` tinyint(2) NOT NULL DEFAULT 0,
  `ban` tinyint(2) NOT NULL DEFAULT 0,
  `adres` varchar(40) NOT NULL DEFAULT '',
  `audio_num` mediumint(8) NOT NULL DEFAULT 0,
  `forum_num` mediumint(8) NOT NULL DEFAULT 0,
  `discussion` tinyint(1) NOT NULL DEFAULT 0,
  `status_text` varchar(255) NOT NULL DEFAULT '',
  `web` varchar(255) NOT NULL DEFAULT '',
  `videos_num` int(11) NOT NULL DEFAULT 0,
  `cover` varchar(25) NOT NULL DEFAULT '',
  `cover_pos` varchar(4) NOT NULL DEFAULT '',
  `data_del` text NOT NULL,
  `ban_reason` text NOT NULL,
  `links_num` int(11) NOT NULL,
  `type_public` varchar(5) NOT NULL,
  `date_created` varchar(10) NOT NULL,
  `privacy` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities_admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `level` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities_audio` (
  `aid` int(11) UNSIGNED NOT NULL,
  `public_id` int(11) UNSIGNED NOT NULL,
  `url` text NOT NULL,
  `artist` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `adate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities_feedback` (
  `cid` int(11) NOT NULL,
  `fuser_id` int(11) NOT NULL,
  `office` varchar(30) NOT NULL,
  `fphone` varchar(15) NOT NULL,
  `femail` varchar(40) NOT NULL,
  `fdate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities_forum` (
  `fid` int(11) UNSIGNED NOT NULL,
  `public_id` int(11) UNSIGNED NOT NULL,
  `fuser_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(65) NOT NULL,
  `text` text NOT NULL,
  `attach` text NOT NULL,
  `fdate` varchar(10) NOT NULL,
  `msg_num` mediumint(8) UNSIGNED NOT NULL,
  `lastdate` varchar(10) NOT NULL,
  `lastuser_id` int(11) UNSIGNED NOT NULL,
  `fixed` tinyint(2) UNSIGNED NOT NULL,
  `status` tinyint(2) UNSIGNED NOT NULL,
  `vote` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities_forum_msg` (
  `mid` int(11) UNSIGNED NOT NULL,
  `fid` int(11) UNSIGNED NOT NULL,
  `muser_id` int(11) UNSIGNED NOT NULL,
  `msg` text NOT NULL,
  `attach` text NOT NULL,
  `mdate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities_join` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `for_user_id` int(11) NOT NULL,
  `public_id` int(11) NOT NULL,
  `date` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities_stats` (
  `id` int(11) NOT NULL,
  `gid` int(11) NOT NULL DEFAULT 0,
  `date` int(10) NOT NULL DEFAULT 0,
  `cnt` int(11) NOT NULL DEFAULT 0,
  `hits` int(11) DEFAULT 0,
  `new_users` int(11) DEFAULT 0,
  `exit_users` int(11) NOT NULL DEFAULT 0,
  `date_x` int(10) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities_stats_log` (
  `gid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `act` tinyint(3) NOT NULL,
  `date` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities_wall` (
  `id` int(11) UNSIGNED NOT NULL,
  `public_id` int(11) UNSIGNED NOT NULL,
  `text` text NOT NULL,
  `add_date` varchar(15) NOT NULL,
  `fast_comm_id` int(11) UNSIGNED NOT NULL,
  `fasts_num` mediumint(8) UNSIGNED NOT NULL,
  `likes_num` mediumint(8) UNSIGNED NOT NULL,
  `likes_users` text NOT NULL,
  `attach` text NOT NULL,
  `tell_uid` int(11) UNSIGNED NOT NULL,
  `tell_date` varchar(10) NOT NULL,
  `public` tinyint(1) UNSIGNED NOT NULL,
  `tell_comm` text NOT NULL,
  `fixed` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `communities_wall_like` (
  `rec_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `date` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `doc` (
  `did` int(11) UNSIGNED NOT NULL,
  `duser_id` int(11) UNSIGNED NOT NULL,
  `dname` varchar(255) NOT NULL,
  `dsize` varchar(10) NOT NULL,
  `ddate` varchar(10) NOT NULL,
  `ddownload_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `fave` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `fave_id` int(11) UNSIGNED NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `friends` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) UNSIGNED NOT NULL,
  `friends_date` datetime NOT NULL,
  `subscriptions` tinyint(3) UNSIGNED NOT NULL,
  `views` mediumint(8) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `friends_demands` (
  `id` int(11) NOT NULL,
  `for_user_id` int(11) UNSIGNED NOT NULL,
  `from_user_id` int(11) UNSIGNED NOT NULL,
  `demand_date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `gifts` (
  `gid` int(11) UNSIGNED NOT NULL,
  `uid` int(11) UNSIGNED NOT NULL,
  `from_uid` int(11) UNSIGNED NOT NULL,
  `gift` varchar(10) NOT NULL,
  `msg` varchar(200) NOT NULL,
  `privacy` tinyint(3) UNSIGNED NOT NULL,
  `gdate` varchar(10) NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `gifts_list` (
  `gid` int(11) NOT NULL,
  `img` varchar(50) NOT NULL,
  `price` mediumint(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "INSERT INTO `gifts_list` (`gid`, `img`, `price`) VALUES
(8, '11', 30),
(9, '14', 3),
(10, '22', 2),
(11, '25', 8),
(12, '30', 5),
(13, '34', 4),
(14, '36', 3),
(15, '37', 6),
(16, '39', 4),
(17, '42', 4),
(18, '49', 15),
(19, '54', 50),
(20, '56', 7),
(21, '57', 4),
(22, '67', 2),
(23, '75', 9),
(24, '90', 4),
(25, '101', 2),
(26, '106', 7),
(27, '109', 6),
(28, '111', 3),
(29, '112', 5),
(30, '120', 10),
(31, '148', 9),
(32, '164', 3),
(33, '169', 8),
(34, '180', 2),
(35, '181', 8),
(36, '182', 12),
(37, '184', 7),
(38, '185', 7),
(39, '186', 75),
(40, '195', 7),
(41, '196', 8),
(42, '197', 9),
(43, '198', 8),
(44, '199', 4),
(45, '201', 5),
(46, '202', 2),
(47, '205', 3),
(48, '213', 19),
(49, '220', 20),
(50, '242', 18),
(51, '248', 19),
(52, '249', 12),
(53, '250', 13),
(54, '257', 1),
(55, '261', 2),
(56, '273', 7),
(57, '277', 8),
(58, '299', 6),
(59, '329', 7),
(60, '332', 2),
(61, '338', 3),
(62, '347', 4),
(63, '349', 3),
(64, '350', 3),
(65, '362', 2),
(66, '373', 4),
(67, '377', 3),
(68, '407', 5),
(69, '408', 4),
(70, '409', 8),
(71, '410', 7),
(72, '411', 10),
(73, '412', 9),
(74, '413', 5),
(75, '414', 4),
(76, '415', 15),
(77, '416', 14),
(78, '417', 2),
(79, '500', 25),
(80, '501', 30),
(82, '502', 20)";

        $tableSchema[] = "CREATE TABLE `im` (
  `id` int(11) NOT NULL,
  `iuser_id` int(11) UNSIGNED NOT NULL,
  `im_user_id` int(11) UNSIGNED NOT NULL,
  `idate` varchar(10) NOT NULL,
  `msg_num` mediumint(8) UNSIGNED NOT NULL,
  `all_msg_num` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `invites` (
  `uid` int(11) NOT NULL,
  `ruid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `browser` text NOT NULL,
  `ip` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `mail_tpl` (
  `id` mediumint(8) NOT NULL,
  `text` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "INSERT INTO `mail_tpl` (`id`, `text`) VALUES
(1, 'Доброго времени суток, {%user%}!\r\nПользователь {%user-friend%}, отправил Вам заявку на дружбу.\r\n\r\nПросмотреть заявку Вы можете по ссылке: http://mysocial.ua/friends/requests\r\n\r\nС уважением, Социальная сеть.\r\nАдминистрация http://mysocial.ua/'),
(2, 'Доброго времени суток, {%user%}!\r\nПользователь {%user-friend%}, ответил на Вашу запись {%rec-link%}\r\n\r\nС уважением, Социальная сеть.\r\nАдминистрация http://mysocial.ua/'),
(3, 'Доброго времени суток, {%user%}!\r\nПользователь {%user-friend%}, оставил комментарий к Вашей видеозаписи {%rec-link%}\r\n\r\nС уважением, Социальная сеть.\r\nАдминистрация http://mysocial.ua/'),
(4, 'Доброго времени суток, {%user%}!\r\nПользователь {%user-friend%}, оставил комментарий к Вашей фотографии {%rec-link%}\r\n\r\nС уважением, Социальная сеть.\r\nАдминистрация http://mysocial.ua/'),
(5, 'Доброго времени суток, {%user%}!\r\nПользователь {%user-friend%}, оставил комментарий к Вашей заметке {%rec-link%}\r\n\r\nС уважением, Социальная сеть.\r\nАдминистрация http://mysocial.ua/'),
(6, 'Доброго времени суток, {%user%}!\r\nПользователь {%user-friend%}, отправил Вам подарок.\r\n\r\nПросмотреть подарок Вы можете по ссылке: {%rec-link%}\r\n\r\nС уважением, Социальная сеть.\r\nАдминистрация http://mysocial.ua/'),
(7, 'Доброго времени суток, {%user%}!\r\nПользователь {%user-friend%}, оставил на Вашей стене новую запись.\r\n\r\nПросмотреть запись можете по ссылке: {%rec-link%}\r\n\r\nС уважением, Социальная сеть.\r\nАдминистрация http://mysocial.ua/'),
(8, 'Доброго времени суток, {%user%}!\r\nПользователь {%user-friend%}, отправил Вам новое личное сообщение.\r\n\r\nПросмотреть сообщение можете по ссылке: {%rec-link%}\r\n\r\nС уважением, Социальная сеть.\r\nАдминистрация http://mysocial.ua/');
";

        $tableSchema[] = "CREATE TABLE `messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `theme` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `for_user_id` int(11) UNSIGNED NOT NULL,
  `from_user_id` int(11) UNSIGNED NOT NULL,
  `history_user_id` int(11) UNSIGNED NOT NULL,
  `date` varchar(15) NOT NULL,
  `pm_read` char(3) NOT NULL,
  `folder` varchar(10) NOT NULL,
  `attach` text NOT NULL,
  `tell_uid` int(11) UNSIGNED NOT NULL,
  `tell_date` varchar(10) NOT NULL,
  `public` tinyint(2) UNSIGNED NOT NULL,
  `tell_comm` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `news` (
  `ac_id` int(11) UNSIGNED NOT NULL,
  `ac_user_id` int(11) UNSIGNED NOT NULL,
  `action_type` tinyint(4) UNSIGNED NOT NULL,
  `action_text` text NOT NULL,
  `obj_id` int(11) UNSIGNED NOT NULL,
  `action_time` int(11) UNSIGNED NOT NULL,
  `for_user_id` int(11) UNSIGNED NOT NULL,
  `answer_text` text NOT NULL,
  `link` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `photos` (
  `id` int(11) UNSIGNED NOT NULL,
  `album_id` int(11) UNSIGNED NOT NULL,
  `photo_name` varchar(25) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `descr` text NOT NULL,
  `comm_num` mediumint(8) UNSIGNED NOT NULL,
  `date` datetime NOT NULL,
  `position` int(11) UNSIGNED NOT NULL,
  `rating_num` int(11) NOT NULL,
  `rating_all` int(11) NOT NULL,
  `rating_max` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `photos_comments` (
  `id` int(11) UNSIGNED NOT NULL,
  `owner_id` int(11) UNSIGNED NOT NULL,
  `album_id` int(11) UNSIGNED NOT NULL,
  `pid` int(11) UNSIGNED NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  `hash` varchar(32) NOT NULL,
  `photo_name` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `photos_mark` (
  `muser_id` int(11) UNSIGNED NOT NULL,
  `mphoto_id` int(11) UNSIGNED NOT NULL,
  `mphoto_name` varchar(50) NOT NULL,
  `mdate` varchar(15) NOT NULL,
  `msettings_pos` varchar(90) NOT NULL,
  `mmark_user_id` int(11) UNSIGNED NOT NULL,
  `mapprove` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `photos_rating` (
  `id` int(11) NOT NULL,
  `photo_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` varchar(10) NOT NULL,
  `rating` tinyint(3) NOT NULL,
  `owner_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `report` (
  `id` int(11) UNSIGNED NOT NULL,
  `act` varchar(10) NOT NULL,
  `type` smallint(5) UNSIGNED NOT NULL,
  `text` text NOT NULL,
  `mid` int(11) UNSIGNED NOT NULL,
  `date` varchar(10) NOT NULL,
  `ruser_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `restore` (
  `id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `ip` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `date` int(10) NOT NULL,
  `approve` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `sms_log` (
  `user_id` int(11) NOT NULL,
  `from_u` varchar(20) NOT NULL,
  `msg` varchar(100) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `country` int(11) NOT NULL,
  `short_number` varchar(50) NOT NULL,
  `abonent_cost` float NOT NULL,
  `date` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `static` (
  `id` int(11) NOT NULL,
  `alt_name` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `text` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `stories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `add_date` int(11) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `stories_feed` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `add_date` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `support` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(65) NOT NULL,
  `question` text NOT NULL,
  `suser_id` int(11) UNSIGNED NOT NULL,
  `sfor_user_id` int(11) UNSIGNED NOT NULL,
  `sdate` varchar(15) NOT NULL,
  `сdate` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `support_answers` (
  `id` int(11) UNSIGNED NOT NULL,
  `qid` int(11) UNSIGNED NOT NULL,
  `auser_id` int(11) UNSIGNED NOT NULL,
  `adate` varchar(15) NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `updates` (
  `id` int(11) UNSIGNED NOT NULL,
  `for_user_id` mediumint(8) UNSIGNED NOT NULL,
  `from_user_id` mediumint(8) UNSIGNED NOT NULL,
  `type` smallint(6) UNSIGNED NOT NULL,
  `date` varchar(10) NOT NULL,
  `text` text NOT NULL,
  `lnk` varchar(100) NOT NULL,
  `user_search_pref` varchar(50) NOT NULL,
  `user_photo` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `users` (
  `user_id` mediumint(11) NOT NULL,
  `user_email` varchar(50) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_name` varchar(30) NOT NULL,
  `user_lastname` varchar(30) NOT NULL,
  `user_photo` varchar(255) NOT NULL,
  `user_wall_id` int(11) NOT NULL,
  `user_birthday` varchar(30) NOT NULL,
  `user_sex` varchar(3) NOT NULL,
  `user_day` varchar(3) NOT NULL,
  `user_month` varchar(3) NOT NULL,
  `user_year` varchar(4) NOT NULL,
  `user_country` varchar(6) NOT NULL,
  `user_city` varchar(6) NOT NULL,
  `user_reg_date` varchar(20) NOT NULL,
  `user_lastdate` varchar(20) NOT NULL,
  `user_group` varchar(1) NOT NULL,
  `user_hash` varchar(64) NOT NULL,
  `user_country_city_name` varchar(100) NOT NULL,
  `user_search_pref` varchar(60) NOT NULL,
  `user_xfields` text NOT NULL,
  `xfields` text NOT NULL,
  `user_xfields_all` text NOT NULL,
  `user_albums_num` smallint(6) NOT NULL,
  `user_friends_demands` int(11) NOT NULL,
  `user_friends_num` mediumint(8) NOT NULL,
  `user_last_visit` varchar(15) NOT NULL,
  `user_fave_num` mediumint(8) NOT NULL,
  `user_pm_num` mediumint(8) NOT NULL,
  `user_notes_num` mediumint(8) NOT NULL,
  `user_subscriptions_num` mediumint(8) NOT NULL,
  `user_videos_num` mediumint(8) NOT NULL,
  `user_wall_num` int(11) NOT NULL,
  `user_status` varchar(255) NOT NULL,
  `user_privacy` varchar(250) NOT NULL,
  `user_blacklist_num` mediumint(8) NOT NULL,
  `user_blacklist` text NOT NULL,
  `user_sp` varchar(10) NOT NULL,
  `user_support` smallint(6) NOT NULL,
  `user_balance` mediumint(8) NOT NULL,
  `user_lastupdate` varchar(10) NOT NULL,
  `user_gifts` mediumint(8) NOT NULL,
  `user_public_num` mediumint(8) NOT NULL,
  `user_audio` int(11) NOT NULL,
  `user_msg_type` tinyint(2) NOT NULL,
  `user_delet` tinyint(3) NOT NULL,
  `user_ban` tinyint(3) NOT NULL,
  `user_ban_date` varchar(10) NOT NULL,
  `user_new_mark_photos` mediumint(8) NOT NULL,
  `user_doc_num` mediumint(8) NOT NULL,
  `user_logged_mobile` tinyint(1) NOT NULL,
  `guests` mediumint(8) NOT NULL,
  `user_cover` varchar(25) NOT NULL,
  `user_cover_pos` varchar(4) NOT NULL,
  `balance_rub` double NOT NULL,
  `user_rating` mediumint(8) NOT NULL,
  `invties_pub_num` smallint(6) NOT NULL,
  `notifications_list` text NOT NULL,
  `user_text` varchar(255) NOT NULL,
  `time_zone` int(11) NOT NULL DEFAULT 0,
  `alias` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `users_blacklist` (
  `id` int(11) NOT NULL,
  `users` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `users_rating` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `for_user_id` int(11) NOT NULL,
  `addnum` int(11) NOT NULL,
  `date` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `users_stats` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `users` int(11) UNSIGNED NOT NULL,
  `views` int(11) UNSIGNED NOT NULL,
  `date` int(6) UNSIGNED NOT NULL,
  `date_x` int(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `users_stats_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `for_user_id` int(11) UNSIGNED NOT NULL,
  `date` int(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `videos` (
  `id` int(11) UNSIGNED NOT NULL,
  `owner_user_id` int(11) UNSIGNED NOT NULL,
  `public_id` int(11) UNSIGNED NOT NULL,
  `video` text NOT NULL,
  `photo` varchar(255) NOT NULL,
  `title` varchar(65) NOT NULL,
  `descr` text NOT NULL,
  `comm_num` mediumint(8) UNSIGNED NOT NULL,
  `add_date` datetime NOT NULL,
  `privacy` tinyint(3) UNSIGNED NOT NULL,
  `views` mediumint(8) UNSIGNED NOT NULL,
  `download` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `videos_comments` (
  `id` int(11) UNSIGNED NOT NULL,
  `author_user_id` int(11) UNSIGNED NOT NULL,
  `video_id` int(11) UNSIGNED NOT NULL,
  `text` text NOT NULL,
  `add_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `videos_decode` (
  `id` int(11) NOT NULL,
  `video` varchar(255) NOT NULL,
  `type` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `title` varchar(80) NOT NULL,
  `answers` text NOT NULL,
  `answer_num` mediumint(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `votes_result` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_id` int(11) NOT NULL,
  `answer` tinyint(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `wall` (
  `id` int(11) UNSIGNED NOT NULL,
  `author_user_id` int(11) UNSIGNED NOT NULL,
  `for_user_id` int(11) UNSIGNED NOT NULL,
  `text` text NOT NULL,
  `add_date` varchar(15) NOT NULL,
  `fast_comm_id` int(11) UNSIGNED NOT NULL,
  `fasts_num` mediumint(8) UNSIGNED NOT NULL,
  `likes_num` mediumint(8) UNSIGNED NOT NULL,
  `likes_users` text NOT NULL,
  `tell_uid` int(11) UNSIGNED NOT NULL,
  `tell_date` varchar(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `public` tinyint(1) UNSIGNED NOT NULL,
  `attach` text NOT NULL,
  `tell_comm` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $tableSchema[] = "CREATE TABLE `wall_like` (
  `id` int(11) NOT NULL,
  `rec_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `date` varchar(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $tableSchema[] = "ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `ads_settings`
  ADD UNIQUE KEY `idad` (`idad`)";

        $tableSchema[] = "ALTER TABLE `albums`
  ADD PRIMARY KEY (`aid`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `photo_num` (`photo_num`),
  ADD KEY `position` (`position`),
  ADD KEY `privacy` (`privacy`),
  ADD KEY `ahash` (`ahash`)";

        $tableSchema[] = "ALTER TABLE `antispam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `act` (`act`,`user_id`,`date`),
  ADD KEY `act_2` (`act`,`user_id`,`date`,`txt`)";

        $tableSchema[] = "ALTER TABLE `apps`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `apps_users`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `attach`
  ADD PRIMARY KEY (`id`),
  ADD KEY `photo` (`photo`),
  ADD KEY `public_id` (`public_id`)";

        $tableSchema[] = "ALTER TABLE `attach_comm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forphoto` (`forphoto`)";

        $tableSchema[] = "ALTER TABLE `audio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auser_id` (`oid`),
  ADD KEY `adate` (`date`);
ALTER TABLE `audio` ADD FULLTEXT KEY `artist` (`artist`,`title`)";

        $tableSchema[] = "ALTER TABLE `banned`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date` (`date`)";

        $tableSchema[] = "ALTER TABLE `bugs`
  ADD UNIQUE KEY `id` (`id`)";

        $tableSchema[] = "ALTER TABLE `bugs`
  ADD UNIQUE KEY `id` (`id`)";

        $tableSchema[] = "ALTER TABLE `bugs_comments`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `codes`
  ADD PRIMARY KEY (`id`)";



        $tableSchema[] = "ALTER TABLE `communities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `del` (`del`),
  ADD KEY `ban` (`ban`),
  ADD KEY `traf` (`traf`),
  ADD KEY `photo` (`photo`);
ALTER TABLE `communities` ADD FULLTEXT KEY `title` (`title`)";

        $tableSchema[] = "ALTER TABLE `communities_admins`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `communities_audio`
  ADD PRIMARY KEY (`aid`),
  ADD KEY `auser_id` (`public_id`),
  ADD KEY `adate` (`adate`)";

        $tableSchema[] = "ALTER TABLE `communities_feedback`
  ADD KEY `cid` (`cid`),
  ADD KEY `fuser_id` (`fuser_id`),
  ADD KEY `fdate` (`fdate`)";

        $tableSchema[] = "ALTER TABLE `communities_forum`
  ADD PRIMARY KEY (`fid`),
  ADD KEY `public_id` (`public_id`),
  ADD KEY `fdate` (`fdate`),
  ADD KEY `lastdate` (`lastdate`),
  ADD KEY `fixed` (`fixed`)";

        $tableSchema[] = "ALTER TABLE `communities_forum_msg`
  ADD PRIMARY KEY (`mid`),
  ADD KEY `fid` (`fid`),
  ADD KEY `muser_id` (`muser_id`),
  ADD KEY `mdate` (`mdate`)";

        $tableSchema[] = "ALTER TABLE `communities_join`
  ADD PRIMARY KEY (`id`),
  ADD KEY `for_sel` (`for_user_id`,`public_id`),
  ADD KEY `for_sel_1` (`user_id`,`public_id`,`date`),
  ADD KEY `for_sel_2` (`for_user_id`,`public_id`,`user_id`)";

        $tableSchema[] = "ALTER TABLE `communities_stats`
  ADD KEY `sel` (`gid`,`date`),
  ADD KEY `date` (`date`),
  ADD KEY `cnt` (`cnt`),
  ADD KEY `hits` (`hits`),
  ADD KEY `new_users` (`new_users`),
  ADD KEY `exit_users` (`exit_users`),
  ADD KEY `sel_1` (`gid`,`date_x`)";

        $tableSchema[] = "ALTER TABLE `communities_stats_log`
  ADD KEY `gid` (`gid`,`user_id`,`date`,`act`)";

        $tableSchema[] = "ALTER TABLE `communities_wall`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fast_comm_id` (`fast_comm_id`),
  ADD KEY `public_id` (`public_id`),
  ADD KEY `add_date` (`add_date`),
  ADD KEY `tell_date` (`tell_date`)";

        $tableSchema[] = "ALTER TABLE `communities_wall_like`
  ADD KEY `rec_id` (`rec_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`)";

        $tableSchema[] = "ALTER TABLE `country`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `doc`
  ADD PRIMARY KEY (`did`),
  ADD KEY `duser_id` (`duser_id`),
  ADD KEY `ddate` (`ddate`)";

        $tableSchema[] = "ALTER TABLE `fave`
  ADD KEY `for_fast_select1` (`user_id`,`fave_id`,`date`)";

        $tableSchema[] = "ALTER TABLE `friends`
  ADD KEY `for_fast_select1` (`user_id`,`friend_id`,`friends_date`),
  ADD KEY `subscriptions` (`subscriptions`),
  ADD KEY `views` (`views`),
  ADD KEY `friends_date` (`friends_date`)";

        $tableSchema[] = "ALTER TABLE `friends_demands`
  ADD KEY `for_fast_select1` (`for_user_id`,`from_user_id`,`demand_date`)";

        $tableSchema[] = "ALTER TABLE `gifts`
  ADD PRIMARY KEY (`gid`),
  ADD KEY `uid` (`uid`),
  ADD KEY `from_uid` (`from_uid`),
  ADD KEY `status` (`status`),
  ADD KEY `gdate` (`gdate`)";

        $tableSchema[] = "ALTER TABLE `gifts_list`
  ADD PRIMARY KEY (`gid`),
  ADD KEY `img` (`img`)";

        $tableSchema[] = "ALTER TABLE `im`
  ADD KEY `iuser_id` (`iuser_id`),
  ADD KEY `im_user_id` (`im_user_id`),
  ADD KEY `idate` (`idate`)";

        $tableSchema[] = "ALTER TABLE `invites`
  ADD KEY `uid` (`uid`)";

        $tableSchema[] = "ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`)";

        $tableSchema[] = "ALTER TABLE `mail_tpl`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `for_fast_select1` (`for_user_id`,`from_user_id`,`folder`,`history_user_id`),
  ADD KEY `date` (`date`),
  ADD KEY `for_user_id` (`for_user_id`),
  ADD KEY `from_user_id` (`from_user_id`),
  ADD KEY `history_user_id` (`history_user_id`),
  ADD KEY `folder` (`folder`)";

        $tableSchema[] = "ALTER TABLE `news`
  ADD PRIMARY KEY (`ac_id`),
  ADD KEY `for_fast_select1` (`ac_user_id`,`action_time`),
  ADD KEY `for_fast_select2` (`ac_user_id`,`action_type`,`action_time`),
  ADD KEY `for_user_id` (`for_user_id`),
  ADD KEY `obj_id` (`obj_id`),
  ADD KEY `action_time` (`action_time`)";

        $tableSchema[] = "ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `album_id` (`album_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `position` (`position`),
  ADD KEY `date` (`date`),
  ADD KEY `photo_name` (`photo_name`)";

        $tableSchema[] = "ALTER TABLE `photos_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `album_id` (`album_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hash` (`hash`),
  ADD KEY `photo_name` (`photo_name`),
  ADD KEY `date` (`date`)";

        $tableSchema[] = "ALTER TABLE `photos_mark`
  ADD KEY `muser_id` (`muser_id`),
  ADD KEY `mphoto_id` (`mphoto_id`),
  ADD KEY `mdate` (`mdate`);
ALTER TABLE `photos_mark` ADD FULLTEXT KEY `mphoto_name` (`mphoto_name`)";

        $tableSchema[] = "ALTER TABLE `photos_rating`
  ADD PRIMARY KEY (`id`),
  ADD KEY `for_select_1` (`id`,`user_id`),
  ADD KEY `for_select_2` (`photo_id`,`user_id`)";

        $tableSchema[] = "ALTER TABLE `report`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ruser_id` (`ruser_id`),
  ADD KEY `mid` (`mid`),
  ADD KEY `act` (`act`),
  ADD KEY `date` (`date`)";

        $tableSchema[] = "ALTER TABLE `restore`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `hash` (`hash`),
  ADD KEY `ip` (`ip`)";

        $tableSchema[] = "ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date` (`date`),
  ADD KEY `for_sel` (`user_id`,`approve`),
  ADD KEY `approve` (`approve`)";

        $tableSchema[] = "ALTER TABLE `sms_log`
  ADD KEY `user_id` (`user_id`)";

        $tableSchema[] = "ALTER TABLE `static`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alt_name` (`alt_name`)";

        $tableSchema[] = "ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `stories_feed`
  ADD UNIQUE KEY `id` (`id`)";

        $tableSchema[] = "ALTER TABLE `support`
  ADD PRIMARY KEY (`id`),
  ADD KEY `suser_id` (`suser_id`),
  ADD KEY `сdate` (`сdate`)";

        $tableSchema[] = "ALTER TABLE `support_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qid` (`qid`),
  ADD KEY `auser_id` (`auser_id`),
  ADD KEY `adate` (`adate`)";

        $tableSchema[] = "ALTER TABLE `updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `for_user_id` (`for_user_id`),
  ADD KEY `date` (`date`)";

        $tableSchema[] = "ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_logged_hash` (`user_hash`),
  ADD KEY `user_password` (`user_password`),
  ADD KEY `user_email` (`user_email`),
  ADD KEY `user_country` (`user_country`),
  ADD KEY `user_city` (`user_city`),
  ADD KEY `user_photo` (`user_photo`),
  ADD KEY `user_sex` (`user_sex`),
  ADD KEY `user_day` (`user_day`),
  ADD KEY `user_month` (`user_month`),
  ADD KEY `user_year` (`user_year`),
  ADD KEY `user_delet` (`user_delet`),
  ADD KEY `user_ban` (`user_ban`),
  ADD KEY `user_reg_date` (`user_reg_date`),
  ADD KEY `user_last_visit` (`user_last_visit`),
  ADD KEY `user_sp` (`user_sp`),
  ADD KEY `user_rating` (`user_rating`),
  ADD KEY `user_search_pref` (`user_search_pref`)";

        $tableSchema[] = "ALTER TABLE `users_rating`
  ADD PRIMARY KEY (`id`),
  ADD KEY `for_select` (`user_id`)";

        $tableSchema[] = "ALTER TABLE `users_stats`
  ADD KEY `date` (`date`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date_x` (`date_x`),
  ADD KEY `views` (`views`),
  ADD KEY `users` (`users`)";

        $tableSchema[] = "ALTER TABLE `users_stats_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`for_user_id`,`date`)";

        $tableSchema[] = "ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_user_id` (`owner_user_id`),
  ADD KEY `privacy` (`privacy`),
  ADD KEY `public_id` (`public_id`),
  ADD KEY `add_date` (`add_date`);
ALTER TABLE `videos` ADD FULLTEXT KEY `title` (`title`)";

        $tableSchema[] = "ALTER TABLE `videos_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `for_fast_select1` (`author_user_id`,`video_id`),
  ADD KEY `add_date` (`add_date`)";

        $tableSchema[] = "ALTER TABLE `videos_decode`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`)";

        $tableSchema[] = "ALTER TABLE `votes_result`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vote_id` (`vote_id`),
  ADD KEY `answer` (`answer`)";

        $tableSchema[] = "ALTER TABLE `wall`
  ADD PRIMARY KEY (`id`),
  ADD KEY `for_fast_select1` (`for_user_id`,`author_user_id`),
  ADD KEY `fast_comm_id` (`fast_comm_id`),
  ADD KEY `tell_uid` (`tell_uid`,`tell_date`),
  ADD KEY `add_date` (`add_date`)";

        $tableSchema[] = "ALTER TABLE `wall_like`
  ADD PRIMARY KEY (`id`),
  ADD KEY `for_fast_select1` (`rec_id`,`user_id`),
  ADD KEY `date` (`date`)";

        $tableSchema[] = "ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `albums`
  MODIFY `aid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `antispam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `apps_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `attach`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `attach_comm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `audio`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `banned`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `blog`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `bugs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `bugs_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2";

        $tableSchema[] = "ALTER TABLE `communities`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `communities_audio`
  MODIFY `aid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `communities_forum`
  MODIFY `fid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `communities_forum_msg`
  MODIFY `mid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `communities_join`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `communities_wall`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `country`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `doc`
  MODIFY `did` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `gifts`
  MODIFY `gid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `gifts_list`
  MODIFY `gid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83";

        $tableSchema[] = "ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `mail_tpl`
  MODIFY `id` mediumint(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9";

        $tableSchema[] = "ALTER TABLE `messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `news`
  MODIFY `ac_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `photos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `photos_comments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `photos_rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `report`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `restore`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `static`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `stories_feed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `support`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `support_answers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `updates`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `users`
  MODIFY `user_id` mediumint(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `users_rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `users_stats_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `videos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `videos_comments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `videos_decode`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `votes_result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `wall`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT";

        $tableSchema[] = "ALTER TABLE `wall_like`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";

        $db = Db::getDB();

        foreach($tableSchema as $table) {

            $db->query($table);

        }

    }
}