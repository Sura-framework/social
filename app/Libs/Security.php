<?php


namespace App\Libs;


class Security
{

    /**
     * spam check
     *
     * @param $act
     * @param bool $text
     */
    function AntiSpam($act, $text = false)
    {

        global $db, $user_info, $server_time;

        if ($text) $text = md5($text);

        /* Типы
            1 - Друзья
            2 - Сообщения не друзьям
            3 - Записей на стену
            4 - Проверка на одинаковый текст
            5 - Комментарии к записям (стены групп/людей)
        */

        //Антиспам дата
        $antiDate = date('Y-m-d', $server_time);
        $antiDate = strtotime($antiDate);

        //Лимиты на день
        $max_frieds = 40; #макс. заявок в друзья
        $max_msg = 40; #макс. сообщений не друзьям
        $max_wall = 500; #макс. записей на стену
        $max_identical = 100; #макс. одинаковых текстовых данных
        $max_comm = 2000; #макс. комментариев к записям на стенах людей и сообществ
        $max_groups = 5; #макс. сообществ за день

        //Если антиспам на друзей
        if ($act == 'friends') {

            //Проверяем в таблице
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `antispam` WHERE act = '1' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}'");

            //Если кол-во, логов больше, то ставим блок
            if ($check['cnt'] >= $max_frieds) {

                die('antispam_err');

            }

        } //Если антиспам на сообщения
        elseif ($act == 'messages') {

            //Проверяем в таблице
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `antispam` WHERE act = '2' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}'");

            //Если кол-во, логов больше, то ставим блок
            if ($check['cnt'] >= $max_msg) {

                die('antispam_err');

            }

        } //Если антиспам на проверку стены
        elseif ($act == 'wall') {

            //Проверяем в таблице
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `antispam` WHERE act = '3' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}'");

            //Если кол-во, логов больше, то ставим блок
            if ($check['cnt'] >= $max_wall) {

                die('antispam_err');

            }

        } //Если антиспам на одинаковые тестовые данные
        elseif ($act == 'identical') {

            //Проверяем в таблице
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `antispam` WHERE act = '4' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}' AND txt = '{$text}'");

            //Если кол-во, логов больше, то ставим блок
            if ($check['cnt'] >= $max_identical) {

                die('antispam_err');

            }

        } //Если антиспам на проверку комментов
        elseif ($act == 'comments') {

            //Проверяем в таблице
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `antispam` WHERE act = '5' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}'");

            //Если кол-во, логов больше, то ставим блок
            if ($check['cnt'] >= $max_comm) {

                die('antispam_err');

            }

        } //Если антиспам на проверку сообществ
        elseif ($act == 'groups') {

            //Проверяем в таблице
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `antispam` WHERE act = '6' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}'");

            //Если кол-во, логов больше, то ставим блок
            if ($check['cnt'] >= $max_groups) {

                die('antispam_err');

            }

        }


    }

    function AntiSpamLogInsert($act, $text = false)
    {

        global $db, $user_info, $server_time;

        if ($text)
            $text = md5($text);

        //Антиспам дата
        $antiDate = date('Y-m-d', $server_time);
        $antiDate = strtotime($antiDate);

        //Если антиспам на друзей
        if ($act == 'friends') {

            $db->query("INSERT INTO `antispam` SET act = '1', user_id = '{$user_info['user_id']}', date = '{$antiDate}'");

            //Если антиспам на сообщения не друзьям
        } elseif ($act == 'messages') {

            $db->query("INSERT INTO `antispam` SET act = '2', user_id = '{$user_info['user_id']}', date = '{$antiDate}'");

            //Если антиспам на стену
        } elseif ($act == 'wall') {

            $db->query("INSERT INTO `antispam` SET act = '3', user_id = '{$user_info['user_id']}', date = '{$antiDate}'");

            //Если антиспам на одинаковых текстов
        } elseif ($act == 'identical') {

            $db->query("INSERT INTO `antispam` SET act = '4', user_id = '{$user_info['user_id']}', date = '{$antiDate}', txt = '{$text}'");

            //Если антиспам комменты
        } elseif ($act == 'comments') {

            $db->query("INSERT INTO `antispam` SET act = '5', user_id = '{$user_info['user_id']}', date = '{$antiDate}'");

            //Если антиспам комменты
        } elseif ($act == 'groups') {

            $db->query("INSERT INTO `antispam` SET act = '6', user_id = '{$user_info['user_id']}', date = '{$antiDate}'");

        }

        return true;

    }
}