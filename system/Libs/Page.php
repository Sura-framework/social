<?php


namespace System\Libs;

use System\Classes\Db;
use System\Libs\Cache;
use System\Libs\Registry;

class Page
{

    function __construct()
    {

    }

    public static function generate(array $params = array()){
        $config = $params['config'];


        $metatags['title'] = '';
        if(!$metatags['title']){
            $metatags['title'] = $config['home'];
        }
        $lang['welcome'] = 'welcome';
        if(isset($user_speedbar))
            $speedbar = $user_speedbar;
        else
            $speedbar = $lang['welcome'];

        //$logged = Registry::get('logged');
        $logged = $params['user']['logged'];
        $params['logged'] = $logged;

        $notify_count = 0;
        if($logged){
            //$db = Registry::get('db');
            $user_info = Registry::get('user_info');
            //Загружаем кол-во новых новостей
            $CacheNews = Cache::mozg_cache('user_'.$user_info['user_id'].'/new_news');
            $params['CacheNews'] = $CacheNews;
            if($CacheNews){
                $params['new_news'] = '<span class="badge badge-secondary">'.$CacheNews.'</span>';
                $params['new_news'] = $CacheNews;
                $params['news_link'] = '/notifications/';
                $notify_count=+$CacheNews;
            }else{
                $params['news_link'] = '';
            }

            //Загружаем кол-во новых подарков
            $CacheGift = Cache::mozg_cache("user_{$user_info['user_id']}/new_gift");
            if($CacheGift){
                $params['new_ubm'] = '<span class="badge badge-secondary">'.$CacheGift.'</span>';
                $params['gifts_link'] = "/gifts/{$user_info['user_id']}/new/";
                $notify_count=+$CacheGift;
            } else
                $params['gifts_link'] = '/balance/';

            //Новые сообщения
            $user_pm_num = $user_info['user_pm_num'];
            if($user_pm_num){
                $params['user_pm_num'] = '<span class="badge badge-secondary">'.$user_pm_num.'</span>';
                $notify_count=+$user_pm_num;
            }else
                $params['user_pm_num'] = '';

            //Новые друзья
            $user_friends_demands = $user_info['user_friends_demands'];
            if($user_friends_demands){
                $params['demands'] = '<span class="badge badge-secondary">'.$user_friends_demands.'</span>';
                $params['requests_link'] = '/requests/';
                $notify_count=+$user_friends_demands;
            } else{
                $params['demands'] = '';
                $params['requests_link'] = '/';
            }

            //ТП
            $user_support = $user_info['user_support'];
            if($user_support){
                $params['support'] = '<span class="badge badge-secondary">'.$user_support.'</span>';
                $notify_count=+$user_support;
            }else
                $params['support'] = '';

            //Отметки на фото
            if($user_info['user_new_mark_photos']){
                $params['new_photos_link'] = 'newphotos';
                $params['new_photos'] = '<span class="badge badge-secondary">'.$user_info['user_new_mark_photos'].'</span>';
                $notify_count=+$user_info['user_new_mark_photos'];
            } else {
                $params['new_photos'] = '';
                $params['new_photos_link'] = $user_info['user_id'].'/';
            }

            //Приглашения в сообщества
            if($user_info['invties_pub_num']){

                $params['new_groups'] = '<span class="badge badge-secondary">'.$user_info['invties_pub_num'].'</span>';
                $params['new_groups_lnk'] = '/groups/invites/';
                $notify_count=+$user_info['invties_pub_num'];
            } else {
                $params['new_groups'] = '';
                $params['new_groups_lnk'] = '/groups/';
            }
            $params['notify_count'] = $notify_count;

        }
        if ($_POST['ajax'] == 'yes'){
            if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['ajax'] != 'yes')
                die('Неизвестная ошибка');

            self::main_ajax($params);
        }
        self::main($params);
    }

    public static function main_ajax(array $params){
        //Если есть POST Запрос и значение AJAX, а $ajax не равняется "yes" то не пропускаем
        $logged = $params['logged'];
        $tpl = $params['tpl'];
        //$tpl = Registry::get('tpl');
        $speedbar = '';
        if(isset($spBar)) {
            $ajaxSpBar = "$('#speedbar').show().html('{$speedbar}')";
        }
        else
            $ajaxSpBar = "$('#speedbar').hide()";

        if (!empty($metatags['title'])){
            $metatags['title'] = 'title';
        }

        if($logged){
            $result_ajax = array(
                'title' => $metatags['title'],
//                'user_pm_num' => $params['user_pm_num'],
//                'new_news' => $params['new_news'],
//                'new_ubm' => $params['new_ubm'],
//                'gifts_link' => $params['gifts_link'],
//                'support' => $params['support'],
//                'news_link' => $params['news_link'],
//                'demands' => $params['demands'],
//                'new_photos' => $params['new_photos'],
//                'new_photos_link' => $params['new_photos_link'],
//                'requests_link' => $params['requests_link'],
//                'new_groups' => $params['new_groups'],
//                'new_groups_lnk' => $params['new_groups_lnk'],
                'new_notifications' => $params['notify_count'],
                'sbar' => $spBar ? $speedbar : '',
                'content' => $tpl->result['info'].$tpl->result['content']
            );
        }else{
            $result_ajax = array(
                'title' => $metatags['title'],
                'sbar' => $spBar ? $speedbar : '',
                'content' => $tpl->result['info'].$tpl->result['content']
            );
        }

        echo json_encode($result_ajax);
        $tpl->global_clear();
        //$db = Db::getDB();
        //$db->close();
        //	if($config['gzip'] == 'yes')
        //		GzipOut();
        return die();
    }

    public static function main(array $params){
        //$tpl = Registry::get('tpl');
        $tpl = $params['tpl'];
        $tpl->load_template('main.tpl');

        //$user_info = Registry::get('user_info');
        $user_info = $params['user']['user_info'];


        //Если юзер залогинен
        //$logged = Registry::get('logged');
        $logged = $params['user']['logged'];
        if($logged){
            $tpl->set_block("'\\[not-logged\\](.*?)\\[/not-logged\\]'si","");
            $tpl->set('[logged]','');
            $tpl->set('[/logged]','');
            $tpl->set('{my-page-link}', '/u'.$user_info['user_id']);
            $tpl->set('{my-id}', $user_info['user_id']);
            $tpl->set('{my-name}', $user_info['user_search_pref']);

            //Заявки в друзья
            $tpl->set('{demands}', $params['demands']);
            $tpl->set('{requests-link}', $params['requests_link']);

            //Новости
            $tpl->set('{new-news}', $params['new_news']);
            $tpl->set('{news-link}', $params['news_link']);

            $tpl->set('{notify}',  $params['notify_count']);

            //Сообщения
            $tpl->set('{msg}', $params['user_pm_num']);

            //Поддержка
            $tpl->set('{new-support}', $params['support']);

            //Отметки на фото
            if($user_info['user_new_mark_photos']){
                $tpl->set('{my-id}', 'newphotos');
                $tpl->set('{new_photos}', $params['new_photos']);
            } else
                $tpl->set('{new_photos}', '');

            //UBM
            $tpl->set('{new-ubm}', $params['new_ubm']);
            $tpl->set('{ubm-link}', $params['gifts_link']);

            //Приглашения в сообщества
            $tpl->set('{groups-link}', $params['new_groups_lnk']);
            $tpl->set('{new_groups}', $params['new_groups']);




        }else {
            $tpl->set_block("'\\[logged\\](.*?)\\[/logged\\]'si","");
            $tpl->set('[not-logged]','');
            $tpl->set('[/not-logged]','');
            //	$tpl->set('{my-page-link}', '');
        }

        //$check_lang = Registry::get('check_lang');
        $check_lang = $params['lang']['check_lang'];
        //BUILD JS
        if($logged){

            // FOR MOBILE VERSION 1.0
            if($user_info['user_photo'])
                $tpl->set('{my-ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");
            else
                $tpl->set('{my-ava}', "/images/no_ava_50.png");

            $tpl->set('{my-name}', $user_info['user_search_pref']);

            $tpl->set('{js}', '<script type="text/javascript" src="/js/jquery.lib.js"></script>
            <script type="text/javascript" src="/js/'.$check_lang.'/lang.js"></script>
            <script type="text/javascript" src="/js/main.js"></script>
            <script type="text/javascript" src="/js/profile.js"></script>
            <script type="text/javascript" src="/js/ads.js"></script>');
        }else
            $tpl->set('{js}', '<script type="text/javascript" src="/js/jquery.lib.js"></script>
        <script type="text/javascript" src="/js/'.$check_lang.'/lang.js"></script>
        <script type="text/javascript" src="/js/main.js"></script>
        <script type="text/javascript" src="/js/auth.js"></script>');

        $tpl->set('{header}', $headers);
        $tpl->set('{info}', $tpl->result['info']);
        $tpl->set('{content}', $tpl->result['content']);

//        $check_smartphone = null;
//        if($check_smartphone) $tpl->set('{mobile-link}', '<a href="/change_mobile/">мобильная версия</a>');
//        else $tpl->set('{mobile-link}', '');

        //$rMyLang = Registry::get('myLang');
        $rMyLang = $params['lang']['mylang'];
        $tpl->set('{lang}', $rMyLang);
        $tpl->compile('main');
        echo $tpl->result['main'];

        $tpl->global_clear();

        $db = Db::getDB();
        $db->close();
        unset($tpl);
        unset($params);
        unset($db);

//        return die();
    }
}