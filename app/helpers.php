<?php
declare(strict_types=1);

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;
use Sura\Libs\Db;
use Sura\Libs\Gramatic;
use Sura\Libs\Langs;
use Sura\Libs\Settings;

if (!function_exists('GetVar')) {
    /**
     * @param string $v
     * @return string
     */
    #[Pure] function GetVar(string $v): string
    {
        if (ini_get('magic_quotes_gpc')) return stripslashes($v);
        return $v;
    }
}

if (!function_exists('msg_box')) {
    /**
     * alert html box
     * @param $text
     * @param $tpl
     * @return false|string
     */
    function msg_box(string $text, string $tpl): string|false
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
        } else {
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

function array_trim_end($array)
{
    $num = count($array);
    --$num;
    if (empty($array[$num])) unset($array[$num]);

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
    /**
     * @deprecated
     */
    function Hacking()
    {
        $ajax = $_POST['ajax'];
        $lang = langs::get_langs();

        if ($ajax) {
//            NoAjaxQuery();
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

if (!function_exists('_e')) {
    /**
     * Encode HTML special characters in a string.
     *
     * @param string $value
     * @return int
     */
    function _e(string $value): int
    {
        return print($value);
    }
}

if (!function_exists('_e_json')) {
    /**
     * Encode HTML special characters in a string.
     *
     * @param array $value
     * @return int
     * @throws JsonException
     * @since 0.9.2
     */
    function _e_json(array $value): int
    {
        header('Content-Type: application/json');
        return print(json_encode($value, JSON_THROW_ON_ERROR));
    }
}