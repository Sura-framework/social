<?php

namespace App\Services;

use Sura\Cache\Adapter\FileAdapter;
use Sura\Cache\Adapter\MemcachedAdapter;
use Sura\Libs\Settings;

class Cache
{
    /** @var string  */
    private static string $type = 'file';

    /**
     * Cache constructor.
     */
    public function __construct(
//        private string $type = 'memcache'
    )
    {
    }

    /**
     * @return \Sura\Cache\Cache
     */
    public static function initialize() : \Sura\Cache\Cache
    {

        if (self::$type == 'file')
        {
            $Cache = new FileAdapter();
        }elseif (self::$type == 'memcache'){

            $config = Settings::loadsettings();

            $Cache = new MemcachedAdapter();
            $Cache->init($config);
        }else{
            $Cache = new FileAdapter();
        }

        $Cache = new \Sura\Cache\Cache($Cache);
        return $Cache;
    }
}