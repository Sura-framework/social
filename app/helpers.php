<?php

use JetBrains\PhpStorm\Pure;
use Sura\Libs\Db;
use Sura\Libs\Gramatic;
use Sura\Libs\Langs;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\View\Blade;

if (!function_exists('GetVar')) {
    function GetVar(string $v) : string
    {
        if(ini_get('magic_quotes_gpc'))
            return stripslashes($v) ;
        return $v;
    }
}

//#[Deprecated]
if (!function_exists('msgbox')) {
    /**
     * alert html box (old)
     * @param $text
     * @param $tpl_name
     * @return false|string
     */
    #[Pure] function msgbox($text, $tpl_name) : string|false
    {
        return msg_box($text, $tpl_name);
    }
}

if (!function_exists('msg_box')) {
    /**
     * alert html box
     * @param $text
     * @param $tpl
     * @return false|string
     */
    function msg_box($text, $tpl) : string|false
    {
        if ($tpl == 'info') {
            return '<div class="err_yellow">' . $text . '</div>';
        } elseif ($tpl == 'info_red') {
            return '<div class="err_red">' . $text . '</div>';
        } elseif ($tpl == 'info_2') {
            return '<div class="info_center">' . $text . '</div>';
        } elseif ($tpl == 'info_box') {
            return '<div class="msg_none">' . $text . '</div>';
        } elseif ($tpl == 'info_search') {
            return '<div class="margin_top_10"></div><div class="search_result_title" style="border-bottom:1px solid #e4e7eb">Ничего не найдено</div>
    <div class="info_center" style="width:630px;padding-top:140px;padding-bottom:154px">Ваш запрос не дал результатов</div>';
        } elseif ($tpl == 'info_yellow') {
            return '<div class="err_yellow"><ul class="listing">' . $text . '</ul></div>';
        }else{
            return false;
        }
    }
}

if (!function_exists('check_smartphone')) {
    #[Pure] function check_smartphone()
    {

        if (isset($_SESSION['mobile_enable'])) return true;
        $phone_array = array('iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'mobile windows', 'cellphone', 'opera mobi', 'operamobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'symbos', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser', 'android');
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        foreach ($phone_array as $value) {
            if (str_contains($agent, $value)) return true;
        }
        return false;
    }
}

if (!function_exists('installationSelected')) {
    function installationSelected($id, $options): array|string
    {
        return str_replace('value="' . $id . '"', 'value="' . $id . '" selected', $options);
    }
}

if (!function_exists('xfieldsdataload')) {
    function xfieldsdataload($string): array
    {

        $xfieldsdata = explode("||", $string);
        $xfieldsdata = array_trim_end($xfieldsdata);


        $data = [];
        foreach ($xfieldsdata as $xfielddata) {

            list ($xfielddataname, $xfielddatavalue) = explode("|", $xfielddata);
            $xfielddataname = str_replace("&#124;", "|", $xfielddataname);
            $xfielddataname = str_replace("__NEWL__", "\r\n", $xfielddataname);
            $xfielddatavalue = str_replace("&#124;", "|", $xfielddatavalue);
            $xfielddatavalue = str_replace("__NEWL__", "\r\n", $xfielddatavalue);
            $data[$xfielddataname] = $xfielddatavalue;
        }
        return $data;
    }
}

function array_trim_end($array){

    $num=count($array);
    $num=$num-1;
    if (empty($array[$num]))
        unset($array[$num]);

    return $array;
}

if (!function_exists('profileload')) {
    function profileload(): bool|array
    {
        $path = __DIR__ . '/../config/xfields.txt';
        $filecontents = file($path);

        if (!is_array($filecontents)) {
            exit('Невозможно загрузить файл');
        }

        foreach ($filecontents as $name => $value) {
            $filecontents[$name] = explode("|", trim($value));
            foreach ($filecontents[$name] as $name2 => $value2) {
                $value2 = str_replace("&#124;", "|", $value2);
                $value2 = str_replace("__NEWL__", "\r\n", $value2);
                $filecontents[$name][$name2] = $value2;
            }
        }
        return $filecontents;
    }
}

if (!function_exists('Hacking')) {
    function Hacking()
    {
        $ajax = $_POST['ajax'];
        $lang = langs::get_langs();

        if ($ajax) {
            NoAjaxQuery();
            echo <<<HTML
        <script type="text/javascript">
        document.title = '{$lang['error']}';
        document.getElementById('speedbar').innerHTML = '{$lang['error']}';
        document.getElementById('page').innerHTML = '{$lang['no_notes']}';
        </script>
        HTML;
            die();
        }
//	else
//		return header('Location: /index.php?go=none');
    }
}

if (!function_exists('user_age')) {
    function user_age($user_year, $user_month, $user_day): false|string
    {
        $server_time = intval($_SERVER['REQUEST_TIME']);

        if ($user_year) {
            $current_year = date('Y', $server_time);
            $current_month = date('n', $server_time);
            $current_day = date('j', $server_time);

            $current_str = strtotime($current_year . '-' . $current_month . '-' . $current_day);
            $current_user = strtotime($current_year . '-' . $user_month . '-' . $user_day);

            if ($current_str >= $current_user)
                $user_age = $current_year - $user_year;
            else
                $user_age = $current_year - $user_year - 1;

            if ($user_month and $user_day) {
                $titles = array('год', 'года', 'лет');//c
                return $user_age . ' ' . Gramatic::declOfNum($user_age, $titles);
            } else
                return false;
        }
        return false;
    }
}

if (!function_exists('langdate')) {
    function langdate($format, $stamp): string
    {
        $langdate = Langs::get_langdate();
        return strtr(@date($format, $stamp), $langdate);
    }
}

if (!function_exists('megaDate')) {
    function megaDate($date, $func = false, $full = false): string
    {
        $server_time = intval($_SERVER['REQUEST_TIME']);

        if (date('Y-m-d', $date) == date('Y-m-d', $server_time))
            return $date = langdate('сегодня в H:i', $date);
        elseif (date('Y-m-d', $date) == date('Y-m-d', ($server_time - 84600)))
            return $date = langdate('вчера в H:i', $date);
        else
            if ($func == 'no_year')
                return $date = langdate('j M в H:i', $date);
            else
                if ($full)
                    return $date = langdate('j F Y в H:i', $date);
                else
                    return $date = langdate('j M Y в H:i', $date);
    }
}

if (!function_exists('Online')) {
    function Online($time, $mobile = false): string
    {
        $lang = langs::get_langs();
        $config = Settings::loadsettings();
        //$config = include __DIR__ . '/data/config.php';
        $server_time = intval($_SERVER['REQUEST_TIME']);
        $online_time = $server_time - $config['online_time'];

        //Если человек сидит с мобильнйо версии
        if ($mobile)
            $mobile_icon = '<img src="/images/spacer.gif" class="mobile_online"  alt=""/>';
        else
            $mobile_icon = '';

        if ($time >= $online_time)
            return $lang['online'] . $mobile_icon;
        else
            return '';
    }
}

if (!function_exists('GenerateAlbumPhotosPosition')) {
    function GenerateAlbumPhotosPosition($uid, $aid = false)
    {
        $db = Db::getDB();
        //Выводим все фотографии из альбома и обновляем их позицию только для просмотра альбома
        if ($uid and $aid) {
            $sql_ = $db->super_query("SELECT id FROM `photos` WHERE album_id = '{$aid}' ORDER by `position` ASC", 1);
            $count = 1;
            $photo_info = '';
            foreach ($sql_ as $row) {
                $db->query("UPDATE LOW_PRIORITY `photos` SET position = '{$count}' WHERE id = '{$row['id']}'");
                $photo_info .= $count . '|' . $row['id'] . '||';
                $count++;
            }
            Sura\Libs\Cache::mozg_create_cache('user_' . $uid . '/position_photos_album_' . $aid, $photo_info);
        }
    }
}

if (!function_exists('AntiSpam')) {
//!nb move
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
}

if (!function_exists('AntiSpamLogInsert')) {

//!nb move
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

    }


}

/**
 * Run the blade engine. It returns the result of the code.
 *
 * @param string|null $view The name of the cache. Ex: "folder.folder.view" ("/folder/folder/view.blade")
 * @param array $variables An associative arrays with the values to display.
 * @return string
 * @throws Exception
 */
function view(?string $view, $variables = [])
{
    $views = __DIR__ . '/views';
    $cache = __DIR__ . '/cache/views';

    class myBlade extends Blade
    {
        use Sura\View\Lang;
    }

    $blade = new myBlade($views,$cache,Blade::MODE_AUTO); // MODE_DEBUG allows to pinpoint troubles.
    $lang = langs::check_lang();
    $lang_list = include __DIR__.'/lang/'.$lang.'.php';
    $blade::$dictionary=$lang_list;


    $variables['url'] = 'https://'.$_SERVER['HTTP_HOST'];
    $blade->setBaseUrl('https://'.$_SERVER['HTTP_HOST']);

    $blade->csrfIsValid(true, '_mytoken');
    $logged = Registry::get('logged');
    if (!empty($logged)){
        $blade->setAuth('johndoe','user');
    }

    try {
        if (Request::ajax()){
            $json_content = $blade->run($view, $variables);
            $title = $variables['title'];
            if (!empty($logged)){
                $result_ajax = array(
                    'title' => $title,
//                        'new_notifications' => $params['notify_count'],
                    'content' => $json_content
                );
            }else{
                $result_ajax = array(
                    'title' => $title,
                    'content' => $json_content
                );
            }
            header('Content-Type: application/json');
            $response = json_encode($result_ajax);
//                echo $blade->run("app.json", ['json' => $json]);
        }else{

            header('Access-Control-Allow-Origin: *');
            $response = $blade->run($view, $variables);
        }
    } catch (Exception $e) {
        $response = "error found ".$e->getMessage()."<br>".$e->getTraceAsString();
    }

//    echo $response;
    return _e($response);
}

if (! function_exists('_e')) {
    /**
     * Encode HTML special characters in a string.
     *
     * @param string $value
     * @return string
     */
    function _e(string $value): string
    {
        return print($value);
    }
}
