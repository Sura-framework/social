<?php


namespace App\Libs;


use Sura\Libs\Langs;
use Sura\Libs\Registry;

class Support
{

    public function head_script_uId()
    {
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        if (isset($logged))
            return '<script>var kj = {uid:\''.$user_info['user_id'].'\'}</script>';
        else
            return false;
    }

    public function head_js()
    {
        $logged = Registry::get('logged');
        $langs = Langs::check_lang();
        $check_lang = $langs['check_lang'];
        if (isset($logged))
            return '<script type="text/javascript" src="/js/jquery.lib.js"></script>
            <script type="text/javascript" src="/js/'.$check_lang.'/lang.js"></script>
            <script type="text/javascript" src="/js/main.js"></script>
            <script type="text/javascript" src="/js/profile.js"></script>
            <script type="text/javascript" src="/js/ads.js"></script>';
        else
            return '<script type="text/javascript" src="/js/jquery.lib.js"></script>
        <script type="text/javascript" src="/js/'.$check_lang.'/lang.js"></script>
        <script type="text/javascript" src="/js/main.js"></script>
        <script type="text/javascript" src="/js/auth.js?=1"></script>';
    }

    public function header()
    {
        if (empty($metatags['title']))
            $metatags['title'] = 'Sura';

        return '<title>'.$metatags['title'].'</title>
<meta name="generator" content="CMS TOOLS" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
    }

    public function logged()
    {
        $logged = Registry::get('logged');
        if (!empty($logged))
            return true;
        else
            return false;
    }

    public function lang()
    {
        $lang = Langs::check_lang();
        return $lang['mylang'];
    }
}