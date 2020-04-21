<?php

namespace System\Libs;

use System\Contracts\CacheInterface;

class Cache implements CacheInterface
{
	public static function creat_system_cache($prefix, $cache_text){
		$libs_dir = dirname (__FILE__);
		$root_dir = str_replace('/system/Libs', '', $libs_dir);
		$filename = $root_dir . '/system/cache/system/'.$prefix.'.php';

        if (!file_exists($filename)){
            $text = $cache_text."//Какой-то текст";
            $fp = fopen($filename, "w");
            fwrite($fp, $text);
            fclose($fp);
        }

	}

	public static function system_cache($prefix) {
		$filename = __DIR__.'/../cache/system/'.$prefix.'.php';
		return file_get_contents($filename);
	}
	public static function mozg_clear_cache(){
		$fdir = opendir(__DIR__.'/../cache/');
		
		while($file = readdir($fdir))
			if($file != '.' and $file != '..' and $file != '.htaccess' and $file != 'system')
				unlink(__DIR__.'/../cache/'.$file);
	}
	public static function mozg_clear_cache_folder($folder){
		$fdir = opendir(__DIR__.'/../cache/'.$folder);
		
		while($file = readdir($fdir))
			unlink(__DIR__.'/../cache/'.$folder.'/'.$file);
	}
	public static function mozg_clear_cache_file($prefix) {
		unlink(__DIR__.'/../cache/'.$prefix.'.tmp');
	}
	public static function mozg_mass_clear_cache_file($prefix){
		$arr_prefix = explode('|', $prefix);
		foreach($arr_prefix as $file)
			unlink(__DIR__.'/../cache/'.$file.'.tmp');
	}
	public static function mozg_create_folder_cache($prefix){
		if(!is_dir(__DIR__.'/../cache/'.$prefix)){
			mkdir(__DIR__.'/../cache/'.$prefix, 0777);
			chmod(__DIR__.'/../cache/'.$prefix, 0777);
		}
	}
	public static function mozg_create_cache($prefix, $cache_text) {
		$filename = __DIR__.'/../cache/'.$prefix.'.tmp';
		$fp = fopen($filename, 'wb+');
		fwrite($fp, $cache_text);
		fclose($fp);
		chmod($filename, 0666);
	}
	public static function mozg_cache($prefix) {
		$filename = __DIR__.'/../cache/'.$prefix.'.tmp';
		return file_get_contents($filename);
	}
}
