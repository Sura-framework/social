<?php
declare(strict_types=1);

namespace App\Libs;


use Sura\Cache\Cache;
use Sura\Cache\Storages\MemcachedStorage;
use Sura\Libs\Db;
use Sura\Libs\Langs;
use Sura\Libs\Registry;
use Sura\Libs\Validation;
use function PHPUnit\Framework\exactly;

class Support
{
	
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
	}
	
	/**
	 * @param string $theme
	 * @return string
	 */
	public static function checkTheme($theme = ''): string
	{
//        $theme
		if ($_COOKIE['theme'] == 'dark' || $_COOKIE['theme'] == 1) {
			return 'checked';
		}
		return '';
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
		$db = Db::getDB();
		$storage = new MemcachedStorage('localhost');
		$cache = new Cache($storage, 'system');
		
		$key = "all_country";
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $db->super_query("SELECT * FROM `country` ORDER by `name`", 1);
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $this->compile_list($row, $country);;
	}
	
	/**
	 * @param int $country
	 * @param int $city
	 * @return string
	 * @throws \Throwable
	 */
	public function allCity(int $country, int $city): string
	{
		$db = Db::getDB();
		$storage = new MemcachedStorage('localhost');
		$cache = new Cache($storage, 'system');
		
		$key = "all_city_{$country}";
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $db->super_query("SELECT id, name FROM `city` WHERE id_country = '{$country}' ORDER by `name`", true);
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $this->compile_list($row, $city);
	}
	
	/**
	 * @param array $list
	 * @param int $selected
	 * @return string
	 */
	public function compile_list(array $list, int $selected): string
	{
		$res = '';
		foreach ($list as $row) {
			if ($row['id'] == $selected and $selected != 0) {
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