<?php
declare(strict_types=1);

namespace App\Libs;


use Sura\Cache\Cache;
use Sura\Cache\Storages\MemcachedStorage;
use Sura\Libs\Db;
use Sura\Libs\Langs;
use Sura\Libs\Model;
use Sura\Libs\Registry;
use Sura\Libs\Validation;
use function PHPUnit\Framework\exactly;

class Support
{
    private \Sura\Database\Connection $database;

    /**
     * Profile constructor.
     */
    public function __construct()
    {
        $this->database = Model::getDB();
    }

    /**
     * @return string
     */
    public static function head_script_uId(): string
    {
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        if (isset($logged, $user_info)) {
            return '<script>var kj = {uid:\'' . $user_info['user_id'] . '\'}</script>';

        }

        return '';
    }

    /**
     * @return string
     */
    public static function head_js(): string
    {
        $logged = Registry::get('logged');
        $lang = Langs::check_lang();
        $url = 'https://' . $_SERVER['HTTP_HOST'];

        $v = 8;
        $jquery = 'jquery.lib.js';

//        header("link: </js/".$jquery.">; rel=preload; as=script", false);

        if (isset($logged)) {
            return '
            <script src="' . $url . '/js/' . $jquery . '?=' . $v . '"></script>
            <script src="' . $url . '/js/' . $lang . '/lang.js?=' . $v . '"></script>
            <script src="' . $url . '/js/main.js?=' . $v . '"></script>
            <script src="' . $url . '/js/profile.js?=' . $v . '"></script>
            <script src="' . $url . '/js/ads.js?=' . $v . '"></script>
            <script src="' . $url . '/js/audio.js?=' . $v . '"></script>';
        }

        return '
        <script src="' . $url . '/js/' . $jquery . '?=' . $v . '"></script>
    <script src="' . $url . '/js/' . $lang . '/lang.js?=' . $v . '"></script>
    <script src="' . $url . '/js/main.js?=' . $v . '"></script>
    <script src="' . $url . '/js/auth.js?=' . $v . '"></script>';
    }

    /**
     * @return string
     */
    public static function header(): string
    {
        if (empty($meta_tags['title'])) {
            $meta_tags['title'] = 'Sura';
        }

        return '<title>' . $meta_tags['title'] . '</title><meta name="generator" content="QD2.RU" /><meta http-equiv="content-type" content="text/html; charset=utf-8" />';
    }

    public static function theme(): string
    {
        if (!isset($_COOKIE['theme'])) {
            \Sura\Libs\Tools::set_cookie("theme", '0', 30);
            return '';
        }

        if ($_COOKIE['theme'] > 0) {
            if ($_COOKIE['theme'] == 'dark' || $_COOKIE['theme'] == 1) {
                return '<link media="screen" href="/style/dark.css" type="text/css" rel="stylesheet" />';
            }
            return '';
        }

        if ($_COOKIE['theme'] == 0) {
            return '';
        }
        return '';
    }

    /**
     * @param string $theme
     * @return string
     */
    public static function checkTheme($theme = ''): string
    {
        if ($_COOKIE['theme'] == 'dark' || $_COOKIE['theme'] == 1) {
            return 'checked';
        }
        return '';
    }

    public static function day_list(): array
    {
        return array(
            0 => array('id' => 1,'name' => '1'),
            1 => array('id' => 2,'name' => '2'),
            2 => array('id' => 3,'name' => '3'),
            3 => array('id' => 4,'name' => '4'),
            4 => array('id' => 5,'name' => '5'),
            5 => array('id' => 6,'name' => '6'),
            6 => array('id' => 7,'name' => '7'),
            7 => array('id' => 8,'name' => '8'),
            8 => array('id' => 9,'name' => '9'),
            9 => array('id' => 10,'name' => '10'),
            10 => array('id' => 11,'name' => '11'),
            11 => array('id' => 12,'name' => '12'),
            12 => array('id' => 13,'name' => '13'),
            13 => array('id' => 14,'name' => '14'),
            14 => array('id' => 15,'name' => '15'),
            15 => array('id' => 16,'name' => '16'),
            16 => array('id' => 17,'name' => '17'),
            17 => array('id' => 18,'name' => '18'),
            18 => array('id' => 19,'name' => '19'),
            19 => array('id' => 20,'name' => '20'),
            20 => array('id' => 21,'name' => '21'),
            21 => array('id' => 22,'name' => '22'),
            22 => array('id' => 23,'name' => '23'),
            23 => array('id' => 24,'name' => '24'),
            24 => array('id' => 25,'name' => '25'),
            25 => array('id' => 26,'name' => '26'),
            26 => array('id' => 27,'name' => '27'),
            27 => array('id' => 28,'name' => '28'),
            28 => array('id' => 29,'name' => '29'),
            29 => array('id' => 30,'name' => '30'),
            30 => array('id' => 31,'name' => '31'),
        );
    }

    public static function month_list(): array
    {
        return array(
            0 => array('id' => 1,'name' => 'Января'),
            1 => array('id' => 2,'name' => 'Февраля'),
            2 => array('id' => 3,'name' => 'Марта'),
            3 => array('id' => 4,'name' => 'Апреля'),
            4 => array('id' => 5,'name' => 'Мая'),
            5 => array('id' => 6,'name' => 'Июня'),
            6 => array('id' => 7,'name' => 'Июля'),
            7 => array('id' => 8,'name' => 'Августа'),
            8 => array('id' => 9,'name' => 'Сентября'),
            9 => array('id' => 10,'name' => 'Октября'),
            10 => array('id' => 11,'name' => 'Ноября'),
            11 => array('id' => 12,'name' => 'Декабря'),
        );
    }

    public static function year_list(): array
    {
        $list = array();
        for ($i=1930;$i<=2007;$i++){
            $list[] = array('id' => $i,'name' => (string)$i);
        }
        return $list;
    }

    /**
     * @return bool
     */
    public function logged(): bool
    {
        $logged = Registry::get('logged');
        if (!empty($logged)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function lang(): string
    {
        $lang = Langs::check_lang();
        return $lang['mylang'];
    }

    /**
     * @return string
     */
    public static function search(): string
    {
        if (isset($_GET['query'])) {
            return Validation::strip_data(urldecode($_GET['query']));
        }

        return '';
    }

    public static function getUser($value = 'user_search_pref')
    {
        $user = Registry::get('user_info');
        return $user[$value];
    }

    /**
     * @param int $country
     * @return string
     * @throws \Throwable
     */
    public function allCountry(int $country = 0): string
    {
        $storage = new MemcachedStorage('localhost');
        $cache = new Cache($storage, 'system');

        $key = "all_country";
        $value = $cache->load($key, function (&$dependencies) {
            $dependencies[Cache::EXPIRE] = '20 minutes';
        });
        if ($value == null) {
            $row = $this->database->fetchALL("SELECT * FROM `country` ORDER by `name`");
            $value = serialize($row);
            $cache->save($key, $value);
        } else {
            $row = unserialize($value, $options = []);
        }
        return $this->compile_list($row, $country);
    }

    /**
     * @param int $country
     * @param int $city
     * @return string
     * @throws \Throwable
     */
    public function allCity(int $country, int $city): string
    {
        $storage = new MemcachedStorage('localhost');
        $cache = new Cache($storage, 'system');

        $key = "all_city_{$country}";
        $value = $cache->load($key, function (&$dependencies) {
            $dependencies[Cache::EXPIRE] = '20 minutes';
        });
        if ($value == null) {
            $row = $this->database->fetchALL("SELECT id, name FROM `city` WHERE id_country = '{$country}' ORDER by `name`");
            $value = serialize($row);
            $cache->save($key, $value);
        } else {
            $row = unserialize($value, $options = []);
        }
        return $this->compile_list($row, $city);
    }

    /**
     * @return array[]
     */
    public static function sex_list(): array
    {
        return array(
            0 => array(
                'id' => 0,
                'name' => 'не выбранно',
            ),
            1 => array(
                'id' => 1,
                'name' => 'мужской',
            ),
            2 => array(
                'id' => 2,
                'name' => 'женский',
            )
        );
    }

    /**
     * @param array $list -list select
     * @param int $selected
     * @return string
     */
    public function compile_list(array $list, int $selected): string
    {
        $res = '';
        foreach ($list as $row) {
            if ($row['id'] == $selected) {
                $name = stripslashes($row['name']);
                $res .= "<option value=\"{$row['id']}\" selected>{$name}</option>";
            } else {
                $name = stripslashes($row['name']);
                $res .= "<option value=\"{$row['id']}\">{$name}</option>";
            }
        }
        return $res;
    }
}