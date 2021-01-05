<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;

use Sura\Libs\Db;
use Sura\Libs\Gramatic;
use Sura\Libs\Langs;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\View\Blade;

if (!function_exists('GetVar')) {
    /**
     * @param string $v
     * @return string
     */
    #[Pure] function GetVar(string $v) : string
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
    #[Pure] #[Deprecated(
        reason: 'since Sura 9.0, use msg_box() instead',
        replacement: 'msg_box(!%parameter0%, !%parameter1%)'
    )]
    function msgbox($text, $tpl_name) : string|false
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
    function msg_box(string $text, string $tpl) : string|false
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
    #[Pure] function check_smartphone(): bool
    {

        if (isset($_SESSION['mobile_enable'])) {
            return true;
        }
        $phone_array = array('iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'mobile windows', 'cellphone', 'opera mobi', 'operamobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'symbos', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser', 'android');
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        foreach ($phone_array as $value) {
            if (str_contains($agent, $value)) {
                return true;
            }
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
        $x_fields_data = array_trim_end(explode("||", $string));
        $data = [];
        foreach ($x_fields_data as $x_field_data) {
            list ($x_field_data_name, $x_field_data_value) = explode("|", $x_field_data);
            $x_field_data_name = str_replace(array("&#124;", "__NEWL__"), array("|", "\r\n"), $x_field_data_name);
            $x_field_data_value = str_replace(array("&#124;", "__NEWL__"), array("|", "\r\n"), $x_field_data_value);
            $data[$x_field_data_name] = $x_field_data_value;
        }
        return $data;
    }
}

function array_trim_end($array){

    $num=count($array);
    --$num;
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
                $value2 = str_replace(array("&#124;", "__NEWL__"), array("|", "\r\n"), $value2);
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
        $server_time = (int)$_SERVER['REQUEST_TIME'];

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
        $lang_date = Langs::get_langdate();
        return strtr(@date($format, $stamp), $lang_date);
    }
}

if (!function_exists('megaDate')) {
    /**
     * @param int $timestamp - date
     * @param false|string $func - no_year
     * @param bool $full - full
     * @return string
     */
    function megaDate(int $timestamp, false|string $func = false, bool $full = false): string
    {
        $server_time = (int)$_SERVER['REQUEST_TIME'];
        if (date('Y-m-d', $timestamp) == date('Y-m-d', $server_time)) {
            return langdate('сегодня в H:i', $timestamp);
        }
        if (date('Y-m-d', $timestamp) == date('Y-m-d', ($server_time - 84600))) {
            return langdate('вчера в H:i', $timestamp);
        }
        if ($func == 'no_year') {
            return langdate('j M в H:i', $timestamp);
        }
        if ($full) {
            return langdate('j F Y в H:i', $timestamp);
        }
        return langdate('j M Y в H:i', $timestamp);
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

/**
 * Run the blade engine. It returns the result of the code.
 *
 * @param string|null $view The name of the cache. Ex: "folder.folder.view" ("/folder/folder/view.blade")
 * @param array $variables An associative arrays with the values to display.
 * @return string
 * @throws Exception
 */
function view(?string $view, $variables = []): string
{
    $views = __DIR__ . '/views';
    $cache = __DIR__ . '/cache/views';

    /**
     * Class myBlade
     */
    class myBlade extends Blade
    {
        use Sura\View\Lang;
    }

    $blade = new myBlade($views,$cache,Blade::MODE_AUTO); // MODE_DEBUG allows to pinpoint troubles.
    $blade::$dictionary = langs::get_langs();

    $variables['url'] = 'https://'.$_SERVER['HTTP_HOST'];
    $blade->setBaseUrl('https://'.$_SERVER['HTTP_HOST']);

    $blade->csrfIsValid(true, '_mytoken');
    $logged = Registry::get('logged');
    if (!empty($logged)){
        $blade->setAuth('john_doe','user');
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
            $json = json_encode($result_ajax, JSON_THROW_ON_ERROR);
            $response =  $blade->run("app.json", ['json' => $json]);
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

if (! function_exists('cache_init')) {
    /**
     * Cache initialize
     * @param array $config
     * @return \Sura\Cache\Cache
     */
    function cache_init(array $config): \Sura\Cache\Cache
    {
        if ($config['type'] == 'file')
        {
            $Cache = new \Sura\Cache\Adapter\FileAdapter();
        }elseif ($config['type'] == 'memcache'){

            $config = Settings::loadsettings();

            $Cache = new \Sura\Cache\Adapter\MemcachedAdapter();
            $Cache->init($config);
        }else{
            $Cache = new \Sura\Cache\Adapter\FileAdapter();
        }

        return new \Sura\Cache\Cache($Cache);
    }
}
