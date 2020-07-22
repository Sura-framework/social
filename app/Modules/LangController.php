<?php

namespace App\Modules;

use Sura\Libs\Langs;
use Sura\Libs\Page;
use Sura\Libs\Registry;
use Sura\Libs\Settings;
use Sura\Libs\Tools;

class LangController extends Module{

    public static function index($params)
    {
        $tpl = $params['tpl'];

        //$lang = $this->get_langs();
        $config = Settings::loadsettings();
//        $db = Registry::get('db');
//        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');

        Tools::NoAjaxQuery();

        $tpl->load_template('lang/main.tpl');

        $useLang = intval($_COOKIE['lang']);
        if($useLang <= 0) $useLang = 1;
        $config['lang_list'] = nl2br($config['lang_list']);
        $expLangList = explode('<br />', $config['lang_list']);
        $numLangs = count($expLangList);
        $cil = 0;
        $langs = '';
        foreach($expLangList as $expLangData){

            $expLangName = explode(' | ', $expLangData);

            if($expLangName[0]){

                $cil++;

                if($cil == $useLang OR $numLangs == 1) {
                    $langs .= "<div class=\"lang_but lang_selected\">{$expLangName[0]}</div>";
                }
                else
                    $langs .= "<a href=\"/index.php?act=chage_lang&id={$cil}\" style=\"text-decoration:none\"><div class=\"lang_but\">{$expLangName[0]}</div></a>";

            }

        }

        if(!$checkLang) $checkLang = 'Russian';

        $tpl->set('{langs}', $langs);

        $tpl->compile('content');

        Tools::AjaxTpl($tpl);

//        $params['tpl'] = $tpl;
//        Page::generate($params);
        return true;
    }

    public static function change_lang(){
        //Смена языка
//        if($_GET['act'] == 'chage_lang'){
        $langId = intval($_GET['id']);
        $config = include __DIR__.'/data/config.php';
        $config['lang_list'] = nl2br($config['lang_list']);
        $expLangList = explode('<br />', $config['lang_list']);
        $numLangs = count($expLangList);
        if($langId > 0 AND $langId <= $numLangs){
            //Меняем язык
            Tools::set_cookie("lang", $langId, 365);
        }
        $langReferer = $_SERVER['HTTP_REFERER'];

        return  header("Location: {$langReferer}");
//        }

    }
}