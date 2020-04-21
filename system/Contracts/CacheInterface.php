<?php


namespace System\Contracts;


interface CacheInterface
{
    public static function creat_system_cache(string $prefix, $cache_text);

    public static function system_cache(string $prefix);

    public static function mozg_clear_cache();

    public static function mozg_clear_cache_folder(string $folder);

    public static function mozg_clear_cache_file(string $prefix);

    public static function mozg_mass_clear_cache_file(string $prefix);

    public static function mozg_create_folder_cache(string $prefix);

    public static function mozg_create_cache(string $prefix, string $cache_text);

    public static function mozg_cache(string $prefix);

}