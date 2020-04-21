<?php

namespace System\Libs;

use phpDocumentor\Reflection\Types\Integer;

/**
 * Class Langs
 * @package System\Libs
 */
class Langs {

    /**
     *
     */
    public static function setlocale()
    {
        $checkLang = self::checkLang();
        if($checkLang == 'Russian'){
            setlocale(LC_ALL, "ru");
        }else{
            setlocale(LC_ALL, "ru");
        }
    }

    /**
     * @return string
     */
    public static function checkLang():string
    {
        $config = include __DIR__.'/../data/config.php';

        $config['lang_list'] = nl2br($config['lang_list']);
        $expLangList = explode('<br />', $config['lang_list']);
        $numLangs = count($expLangList);

        //lang
        $config['lang_list'] = nl2br($config['lang_list']);
        $expLangList = explode('<br />', $config['lang_list']);
        $numLangs = count($expLangList);
        //$useLang = intval($_COOKIE['lang']);//bug
        if (!empty($_COOKIE['lang'])){
            $useLang = intval($_COOKIE['lang']);
        }else{
            $useLang = 0;
        }

        if($useLang <= 0) $useLang = 1;
        $cil = 0;
        foreach($expLangList as $expLangData){
            $cil++;
            $expLangName = explode(' | ', $expLangData);
            if($cil == $useLang AND $expLangName[0]){
                $rMyLang = $expLangName[0];
                $checkLang = $expLangName[1];
            }
        }
        if(!$checkLang){
            $rMyLang = 'Русский';
            $checkLang = 'Russian';
        }
        return $checkLang;
    }

    /**
     * @return array
     */
    public static function get_langs():array
    {
        $checkLang = self::checkLang() ? self::checkLang() :'Russian';
        return include __DIR__.'/../../lang/'.$checkLang.'/langs.lng';
    }

    /**
     * @return array
     */
    public static function get_langdate():array
    {
        $checkLang = self::checkLang() ? self::checkLang() :'Russian';
        return include __DIR__.'/../../lang/'.$checkLang.'/date.lng';
    }

    /**
     * @return array
     */
    public static function check_lang(){
        //lang
        $config = include __DIR__.'/../data/config.php';
        $config['lang_list'] = nl2br($config['lang_list']);
        $expLangList = explode('<br />', $config['lang_list']);
        $numLangs = count($expLangList);
        if (!empty($_COOKIE['lang'])){
            $useLang = intval($_COOKIE['lang']);
        }else{
            $useLang = 0;
        }

        if($useLang <= 0) $useLang = 1;
        $cil = 0;
        $checkLang = 'Russian';
        $rMyLang = 'Русский';
        foreach($expLangList as $expLangData){
            $cil++;
            $expLangName = explode(' | ', $expLangData);
            if($cil == $useLang AND $expLangName[0]){
                $rMyLang = $expLangName[0];
                $checkLang = $expLangName[1];
            }
        }


        Registry::set('myLang', $rMyLang);
        Registry::set('check_lang', $checkLang);

        return array('mylang' => $rMyLang, 'check_lang' => $checkLang);

    }
}