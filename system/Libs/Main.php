<?php


namespace System\Libs;


class Main
{
    public static function headers(){
        $metatags['title'] = (!$metatags['title']) ?  $config['home'] : $metatags['title'];
        $speedbar = ($user_speedbar) ? $user_speedbar : $lang['welcome'] ;

        $headers = '<title>'.$metatags['title'].'</title>
        <meta name="generator" content="Vii Engine" />
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />';
        return  $headers;
    }

    public static function js(){
        $checkLang = '';
        $logged = Registry::get('logged');

        if($logged) {
            $js = '<script type="text/javascript" src="/js/jquery.lib.js"></script>
            <script type="text/javascript" src="/js/'.$checkLang.'/lang.js"></script>
            <script type="text/javascript" src="/js/main.js"></script>
            <script type="text/javascript" src="/js/profile.js"></script>
            <script type="text/javascript" src="/js/ads.js"></script>';
        }
            else
            $js = '<script type="text/javascript" src="/js/jquery.lib.js"></script>
            <script type="text/javascript" src="/js/'.$checkLang.'/lang.js"></script>
            <script type="text/javascript" src="/js/main.js"></script>
            <script type="text/javascript" src="/js/reg.js"></script>';

        return $js;
    }

    public static function myid(){
        $logged = Registry::get('logged');
        if ($logged){
            $user_info = Registry::get('user_info');
            return '<script>var kj = {uid:\''.$user_info['user_id'].'\'}</script>';
        }else{
            return false;
        }
    }

    public static function home_link(){
        $logged = Registry::get('logged');
        if ($logged){
            return 'news/';
        }else{
            return false;
        }
    }
}