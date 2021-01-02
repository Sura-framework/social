<?php


namespace App\Libs;


use Sura\Libs\Langs;
use Sura\Libs\Registry;
use Sura\Libs\Validation;

class Support
{

    /**
     * @return string
     */
    public function head_script_uId() : string
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
    public function head_js() : string
    {
        $logged = Registry::get('logged');
        $lang = Langs::check_lang();
        $url = 'https://'.$_SERVER['HTTP_HOST'];

        $v = 7;

        if (isset($logged)) {
            return '<script type="text/javascript" src="' . $url . '/js/jquery.lib.js?='.$v.'"></script>
            <script type="text/javascript" src="' . $url . '/js/' . $lang . '/lang.js?='.$v.'"></script>
            <script type="text/javascript" src="' . $url . '/js/main.js?='.$v.'"></script>
            <script type="text/javascript" src="' . $url . '/js/profile.js?='.$v.'"></script>
            <script type="text/javascript" src="' . $url . '/js/ads.js?='.$v.'"></script>';
        }

        return '<script type="text/javascript" src="' . $url . '/js/jquery.lib.js?='.$v.'"></script>
    <script type="text/javascript" src="' . $url . '/js/' . $lang . '/lang.js?='.$v.'"></script>
    <script type="text/javascript" src="' . $url . '/js/main.js?='.$v.'"></script>
    <script type="text/javascript" src="' . $url . '/js/auth.js?='.$v.'"></script>';
    }

    /**
     * @return string
     */
    public function header() : string
    {
        if (empty($meta_tags['title'])) {
            $meta_tags['title'] = 'Sura';
        }

        return '<title>'.$meta_tags['title'].'</title><meta name="generator" content="QD2.RU" /><meta http-equiv="content-type" content="text/html; charset=utf-8" />';
    }

    /**
     * @return bool
     */
    public function logged() : bool
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
    public function lang() : string
    {
        $lang = Langs::check_lang();
        return $lang['mylang'];
    }

    /**
     * @return string
     */
    public function search() : string
    {
        if (isset($_GET['query'])){
            return Validation::strip_data(urldecode($_GET['query']));
        }

        return '';
    }
}