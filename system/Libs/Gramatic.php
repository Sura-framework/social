<?php

namespace System\Libs;

use System\Contracts\GramaticInterface;

/**
 * Class Gramatic
 * @package System\Libs
 */
class Gramatic implements GramaticInterface
{

    //used to notify
    /**
     * @param string $date
     * @param bool $func
     * @param bool $full
     * @return string
     */
    public static function megaDateNoTpl2($date, $func = false, $full = false):string
    {
        $server_time = intval($_SERVER['REQUEST_TIME']);
        if(date('Y-m-d', $date) == date('Y-m-d', $server_time)) return $date = langdate('сегодня', $date);
        elseif(date('Y-m-d', $date) == date('Y-m-d', ($server_time-84600))) return $date = langdate('вчера', $date);
        else if($func == 'no_year') return $date = langdate('j M', $date);
        else if($full) return $date = langdate('j F Y', $date);
        else
        return langdate('j M Y', $date);
    }

    /*
    Использовать в любом файле php вот так
    DeclName($row_users['user_name'], 'rod');
    */
    /**
     * @param string $name
     * @param string $declination
     * @return string
     */
    public static function DeclName($name, $declination) :string
    {
        switch ($declination){
            case 'rod': // родительный - Кого? Чего?
                $end_declination = array('а' => 'ы', 'б' => 'ба', 'в' => 'ва', 'г' => 'га', 'д' => 'да', 'е' => 'е', 'ж' => 'жа', 'з' => 'за', 'и' => 'и', 'й' => 'я', 'к' => 'ка', 'л' => 'ла', 'м' => 'ма', 'н' => 'на', 'о' => 'о', 'п' => 'па', 'р' => 'ра', 'с' => 'са', 'т' => 'та', 'у' => 'у', 'ф' => 'фа', 'х' => 'ха', 'ч' => 'ча', 'ш' => 'ша', 'э' => 'э', 'ю' => 'ю', 'ь' => 'я', 'я' => 'и', 'ы' => 'ю');
                break;
            case 'dat': // дательный - Кому? Чему?
                $end_declination = array('а' => 'е', 'б' => 'бу', 'в' => 'ву', 'г' => 'гу', 'д' => 'ду', 'е' => 'е', 'ж' => 'жу', 'з' => 'зу', 'и' => 'и', 'й' => 'ю', 'к' => 'ку', 'л' => 'лу', 'м' => 'му', 'н' => 'ну', 'о' => 'о', 'п' => 'пу', 'р' => 'ру', 'с' => 'су', 'т' => 'ту', 'у' => 'у', 'ф' => 'фу', 'х' => 'ху', 'ч' => 'чу', 'ш' => 'шу', 'э' => 'э', 'ю' => 'ю', 'ь' => 'ю', 'я' => 'ю', 'ы' => 'у');
                break;
            case 'vin': // винительный - Кого? Что?
                $end_declination = array('а' => 'у', 'б' => 'ба', 'в' => 'ва', 'г' => 'га', 'д' => 'да', 'е' => 'е', 'ж' => 'жа', 'з' => 'за', 'и' => 'и', 'й' => 'я', 'к' => 'ка', 'л' => 'ла', 'м' => 'ма', 'н' => 'на', 'о' => 'о', 'п' => 'па', 'р' => 'ра', 'с' => 'са', 'т' => 'та', 'у' => 'у', 'ф' => 'фа', 'х' => 'ха', 'ч' => 'ча', 'ш' => 'ша', 'э' => 'э', 'ю' => 'ю', 'ь' => 'ю', 'я' => 'ю', 'ы' => 'ю');
                break;
            case 'tvo': // творительный - Кем? Чем?
                $end_declination = array('а' => 'ой', 'ич' => 'чем', 'б' => 'бом', 'в' => 'вом', 'г' => 'гом', 'д' => 'дом', 'е' => 'е', 'ж' => 'жом', 'з' => 'зом', 'и' => 'и', 'й' => 'ем', 'к' => 'ком', 'л' => 'лом', 'м' => 'мом', 'н' => 'ном', 'о' => 'о', 'п' => 'пом', 'р' => 'ром', 'с' => 'сом', 'т' => 'том', 'у' => 'у', 'ф' => 'фом', 'х' => 'хом', 'ч' => 'чем', 'ш' => 'шом', 'э' => 'э', 'ю' => 'ю', 'ь' => 'ьей', 'я' => 'ей', 'ы' => 'ей');
                break;
            case 'pre': // предложный - О ком? О чём?
                $end_declination = array('а' => 'е', 'б' => 'бе', 'в' => 'ве', 'г' => 'ге', 'д' => 'де', 'е' => 'е', 'ж' => 'же', 'з' => 'зе', 'и' => 'и', 'й' => 'е', 'к' => 'ке', 'л' => 'ле', 'м' => 'ме', 'н' => 'не', 'о' => 'о', 'п' => 'пе', 'р' => 'ре', 'с' => 'се', 'т' => 'те', 'у' => 'у', 'ф' => 'фе', 'х' => 'хе', 'ч' => 'че', 'ш' => 'ше', 'э' => 'э', 'ю' => 'ю', 'ь' => 'и', 'я' => 'е', 'ы' => 'и');
                break;
            default:
                $end_declination = array();
        }
            $srt_count = strlen($name);
            $srt_end = $name[$srt_count-2] .$name[$srt_count-1];
            $srt_name = substr($name, 0, $srt_count-2);
            return $srt_name . $end_declination[$srt_end];
    }

    /**
     * @param string $var
     * @param bool $lower
     * @param bool $punkt
     * @return string
     */
    public static function totranslit($var, $lower = true, $punkt = true):string
    {
        // global $langtranslit;
        if ( is_array($var) ) return "";

        $langtranslit = null;
        if (!is_array ( $langtranslit ) OR !count( $langtranslit ) ) {

            $langtranslit = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v',
            'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
            'и' => 'i', 'й' => 'y', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            "ї" => "yi", "є" => "ye",

            'А' => 'A', 'Б' => 'B', 'В' => 'V',
            'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
            'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
            'И' => 'I', 'Й' => 'Y', 'К' => 'K',
            'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R',
            'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
            'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
            "Ї" => "yi", "Є" => "ye",
            );

        }

        $var = str_replace( ".php", "", $var );
        $var = trim( strip_tags( $var ) );
        $var = preg_replace( "/\s+/ms", "-", $var );

        $var = strtr($var, $langtranslit);

        if ( $punkt ) $var = preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var );
        else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );

        $var = preg_replace( '#[\-]+#i', '-', $var );

        if ( $lower ) $var = strtolower( $var );

        if( strlen( $var ) > 200 ) {

            $var = substr( $var, 0, 200 );

            if( ($temp_max = strrpos( $var, '-' )) ) $var = substr( $var, 0, $temp_max );

        }

        return $var;
    }

    /**
     * @param int $number
     * @param array $titles
     * @return mixed
     */
    public static function declOfNum($number, $titles)
    {
        $cases = array(2, 0, 1, 1, 1, 2);
        return $titles[ ($number % 100 > 4 AND $number % 100 < 20)? 2 : $cases[min($number % 10, 5)] ];
    }

    /**
     * @param string $name
     * @return string
     */
    public static function gramatikName($name):string
    {
        $name_u_gram = $name;
        $str_1_name = strlen($name_u_gram);
        $str_2_name = $str_1_name-2;
        $str_3_name = substr($name_u_gram, $str_2_name, $str_1_name);
        $str_5_name = substr($name_u_gram, 0, $str_2_name);
        $str_4_name = strtr($str_3_name,array(
                        'ай' => 'ая',
                        'ил' => 'ила',
                        'др' => 'дра',
                        'ей' => 'ея',
                        'кс' => 'кса',
                        'ша' => 'ши',
                        'на' => 'ны',
                        'ка' => 'ки',
                        'ад' => 'ада',
                        'ма' => 'мы',
                        'ля' => 'ли',
                        'ня' => 'ни',
                        'ин' => 'ина',
                        'ик' => 'ика',
                        'ор' => 'ора',
                        'им' => 'има',
                        'ём' => 'ёма',
                        'ий' => 'ия',
                        'рь' => 'ря',
                        'тя' => 'ти',
                        'ся' => 'си',
                        'из' => 'иза',
                        'га' => 'ги',
                        'ур' => 'ура',
                        'са' => 'сы',
                        'ис' => 'иса',
                        'ст' => 'ста',
                        'ел' => 'ла',
                        'ав' => 'ава',
                        'он' => 'она',
                        'ра' => 'ры',
                        'ан' => 'ана',
                        'ир' => 'ира',
                        'рд' => 'рда',
                        'ян' => 'яна',
                        'ов' => 'ова',
                        'ла' => 'лы',
                        'ия' => 'ии',
                        'ва' => 'вой',
                        'ыч' => 'ыча',
                        'ич' => 'ича'
                        ));
        return $str_5_name.$str_4_name;
    }

    //used to apps
    /**
     * @param int $num
     * @param $a
     * @param $b
     * @param $c
     * @param bool $t
     * @return string
     */
    public static function newGram($num, $a, $b, $c, $t = false):string
    {
        if($t)
            return declOfNum($num, array(sprintf($a,  $num), sprintf($b,  $num), sprintf($c, $num)));
        else
            return declOfNum($num, array(sprintf("%d {$a}",  $num), sprintf("%d {$b}",  $num), sprintf("%d {$c}", $num)));
    }



}
