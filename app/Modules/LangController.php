<?php

declare(strict_types=1);

namespace App\Modules;

use Sura\Libs\Langs;
use Sura\Libs\Tools;

class LangController extends Module{

    /**
     * @return int
     * @throws \JsonException
     */
    public static function index(): int
    {
//        Tools::NoAjaxRedirect();

        if (isset($_COOKIE['lang']) AND $_COOKIE['lang'] > 0) {
            $useLang = (int)$_COOKIE['lang'];
        }
        else {
            $useLang = 0;
        }

        $langs = Langs::lang_list();
        $num_Lang = count($langs);

        $lang = '';
        foreach($langs as $key => $value){
            if($useLang == $key OR $num_Lang == 0) {
                $lang .= "<div class=\"lang_but lang_selected\">".$langs[$key]['flag'] .$langs[$key]['name']."</div>";
            }else{
                $lang .= "<a href=\"/lang/change/{$key}/\"><div class=\"lang_but\">".$langs[$key]['flag'].$langs[$key]['name']."</div></a>";
            }
        }
        $params['lang'] = $lang;

        $langs = view_data('lang', $params);

        return _e_json(array(
            'content' => $langs,
        ) );
    }

    /**
     * Меняем язык
     */
    public function change_lang(): int
    {
        //Смена языка
        $path = explode('/', $_SERVER['REQUEST_URI']);
        $langId = $path['3'];
        $num_lang = count(Langs::lang_list());
        if($langId < $num_lang){
            Tools::set_cookie("lang", $langId, 365);
        }
        $langReferer = $_SERVER['HTTP_REFERER'];

        header("Location: {$langReferer}");
        return 1;

    }
}