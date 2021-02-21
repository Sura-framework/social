<?php

declare(strict_types=1);

namespace App\Models;

use Sura\Libs\Db;
use Sura\Libs\Model;
use Sura\Libs\Registry;
use Sura\Time\Date;

class Bugs
{

    private \Sura\Database\Connection $database;

    /**
     * Profile constructor.
     */
    public function __construct()
    {
        $this->database = Model::getDB();
    }

    public static array $status = array(
        0 => array(
            'text' =>'Открыто',
            'color_class' => 'text-danger',
            'tooltip' => 'Мы проверяем ваше сообщение об ошибке.',
        ),
        1 => array(
            'text' =>'Открыто',
            'color_class' => 'text-danger',
            'tooltip' => 'Мы проверяем ваше сообщение об ошибке.',
        ),
        9 => array(
            'text' =>'В работе',
            'color_class' => 'text-muted',
            'tooltip' => 'Нам удалось воспроизвести эту ошибку. Она передана специалистам соответствующего продукта для дальнейшего изучения.',
        ),
        2 => array(
            'text' =>'Исправлено',
            'color_class' => 'text-success',
            'tooltip' => 'ttt',
        ),
        3 => array(
            'text' =>'Отклонено',
            'color_class' => 'text-muted',
            'tooltip' => 'ttt',
        ),
        4 => array(
            'text' =>'На рассмотрении',
            'color_class' => 'text-success',
            'tooltip' => 'Мы рассматриваем ваше сообщение с учетом предоставленной информации.',
        ),
        6 => array(
            'text' =>'Решено',
            'color_class' => 'text-muted',
            'tooltip' => 'Ошибка закрыта.',
        ),
        8 => array(
            'text' =>'Заблокировано',
            'color_class' => 'text-muted',
            'tooltip' => 'ttt',
        ),
        5 => array(
            'text' =>'Переоткрыто',
            'color_class' => 'text-warning',
            'tooltip' => 'ttt',
        ),
        10 => array(
            'text' =>'Не воспроизводится',
            'color_class' => 'text-warning',
            'tooltip' => 'Нам не удалось воспроизвести эту ошибку.',
        ),
        7 => array(
            'text' =>'Отложено',
            'color_class' => 'text-warning',
            'tooltip' => 'Отложено',
        ),
        11 => array(
            'text' =>'Требует корректировки',
            'color_class' => 'text-warning',
            'tooltip' => 'Нам нужна дополнительная информация от вас, чтобы воспроизвести ошибку, о которой вы сообщили.',
        ),
    );

    /**
     * @param $status
     * @return string
     */
    public static function getStatusData(int $status): string
    {
        if ($status == 0 || $status == 1){
            $response = '            
            <div class="_3t4q _3t4s">
                <div class="_3t4u" style="left: 25%; width: 50%;"></div>
                <div class="_3t4u _3t4v" style="left: 25%; width: 0%;"></div>
                <ul class="_3t51">
                    <li class="_3t4j active _71_e"
                     onmouseover="myhtml.title(\'0\', \'Мы проверяем ваше сообщение об ошибке.\', \'step\', 5)"
                     data-tooltip-content="Мы проверяем ваше сообщение об ошибке."
                        data-hover="tooltip" data-tooltip-position="above" data-tooltip-alignh="center" id="step0"
                        style="width: 50%;">
                        <span class="_3t4l"></span>
                        <label class="_3t4m">Открыто</label>
                    </li>
                    <li class="_3t4j _71_e" 
                    onmouseover="myhtml.title(\'1\', \'Ошибка закрыта.\', \'step\', 5)"
                    data-tooltip-content="Ошибка закрыта." data-hover="tooltip"
                        data-tooltip-position="above" data-tooltip-alignh="center" id="step1" style="width: 50%;">
                        <span class="_3t4l"></span>
                        <label class="_3t4m">Решено</label>
                    </li>
                </ul>
            </div>';
        }elseif($status == 8 || $status == 6 || $status == 3){
            $response = '
            <div class="_3t4q _3t4s">
                <div class="_3t4u" style="left: 25%; width: 50%;"></div>
                <div class="_3t4u _3t4v" style="left: 25%; width: 50%;"></div>
                <ul class="_3t51">
                    <li class="_3t4j active _71_e"
                     onmouseover="myhtml.title(\'0\', \'Мы проверяем ваше сообщение об ошибке.\', \'step\', 5)"
                     data-tooltip-content="Мы проверяем ваше сообщение об ошибке."
                        data-hover="tooltip" data-tooltip-position="above" data-tooltip-alignh="center" id="step0"
                        style="width: 50%;">
                        <span class="_3t4l"></span>
                        <label class="_3t4m">Открыто</label>
                    </li>
                    <li class="_3t4j active _71_e" 
                    onmouseover="myhtml.title(\'1\', \''.self::$status[$status]['tooltip'].'.\', \'step\', 5)"
                    data-tooltip-content="Ошибка закрыта." data-hover="tooltip"
                        data-tooltip-position="above" data-tooltip-alignh="center" id="step1" style="width: 50%;">
                        <span class="_3t4l"></span>
                        <label class="_3t4m">'.self::$status[$status]['text'].'</label>
                    </li>
                </ul>
            </div>';
        }elseif($status == 2 || $status == 4 || $status == 5 || $status == 7 || $status == 9 || $status == 10 || $status == 11){
            $response = '
            <div class="_3t4q _3t4s">
                <div class="_3t4u" style="left: 16.6667%; width: 66.6667%;"></div>
                <div class="_3t4u _3t4v" style="left: 16.6667%; width: 33.3333%;"></div>
                <ul class="_3t51">
                    <li class="_3t4j active _71_e" 
                    onmouseover="myhtml.title(\'0\', \'Мы проверяем ваше сообщение об ошибке.\', \'step\', 5)"
                    data-tooltip-content="Мы проверяем ваше сообщение об ошибке."
                        data-hover="tooltip" data-tooltip-position="above" data-tooltip-alignh="center" id="step0"
                        style="width: 33.3333%;">
                        <span class="_3t4l"></span>
                        <label class="_3t4m">Открыто</label>
                    </li>
                    <li class="_3t4j active _71_e"
                    onmouseover="myhtml.title(\'1\', \''.self::$status[$status]['tooltip'].'\', \'step\', 5)"
                        data-tooltip-content="Нам удалось воспроизвести эту ошибку. Она передана специалистам соответствующего продукта для дальнейшего изучения."
                        data-hover="tooltip" data-tooltip-position="above" data-tooltip-alignh="center" id="step1"
                        style="width: 33.3333%;">
                        <span class="_3t4l"></span>
                        <label class="_3t4m">'.self::$status[$status]['text'].'</label>
                    </li>
                    <li class="_3t4j active _71_e"
                     onmouseover="myhtml.title(\'2\', \'Ошибка закрыта.\', \'step\', 5)"
                     data-tooltip-content="Ошибка закрыта." data-hover="tooltip"
                        data-tooltip-position="above" data-tooltip-alignh="center" id="step2" style="width: 33.3333%;">
                        <span class="_3t4l"></span>
                        <label class="_3t4m">Решено</label>
                    </li>
                </ul>
            </div>';
        }else{
            $response = '            
            <div class="_3t4q _3t4s">
                <div class="_3t4u" style="left: 25%; width: 50%;"></div>
                <div class="_3t4u _3t4v" style="left: 25%; width: 50%;"></div>
                <ul class="_3t51">
                    <li class="_3t4j active _71_e" 
                    onmouseover="myhtml.title(\'0\', \'Мы проверяем ваше сообщение об ошибке.\', \'step\', 5)"
                    data-tooltip-content="Мы проверяем ваше сообщение об ошибке."
                        data-hover="tooltip" data-tooltip-position="above" data-tooltip-alignh="center" id="step0"
                        style="width: 50%;">
                        <span class="_3t4l"></span>
                        <label class="_3t4m">Открыто</label>
                    </li>
                    <li class="_3t4j active _71_e" 
                    onmouseover="myhtml.title(\'1\', \'Ошибка закрыта.\', \'step\', 5)"
                    data-tooltip-content="Ошибка закрыта." data-hover="tooltip"
                        data-tooltip-position="above" data-tooltip-alignh="center" id="step1" style="width: 50%;">
                        <span class="_3t4l"></span>
                        <label class="_3t4m">Решено</label>
                    </li>
                </ul>
            </div>';
        }
        return $response;
    }

    public function getData(array $sql_): array
    {
        $user_info = Registry::get('user_info');
        $user_id = $user_info['user_id'];

        if ($user_info['user_group'] < 5){
            $moderator = true;
        }else{
            $moderator = false;
        }

        foreach ($sql_ as $key => $row) {

            $sql_[$key]['title'] = stripslashes($row['title']);
            $sql_[$key]['text'] = stripslashes($row['text']);
            $sql_[$key]['date'] = Date::megaDate((int)Date::date_convert($row['date'], 'U'));
            $sql_[$key]['add_date'] = Date::megaDate((int)Date::date_convert($row['add_date'], 'U'));
            $sql_[$key]['datetime'] = Date::date_convert($row['add_date'], 'Y-m-d H:i:s');
            $sql_[$key]['id'] = $row['id'];
            $sql_[$key]['uid'] = $row['user_id'];
            $sql_[$key]['user_search_pref'] = $row['user_search_pref'];
            $sql_[$key]['user_id'] = $user_id;
            $sql_[$key]['moderator'] = $moderator;

//            if ($moderator == true || $row['user_id'] == $user_id){
//                $sql_[$key]['delete'] = '<a href="/" onClick="bugs.Delete(\' '.$row['id'].' \'); return false;" style="color: #000000">Удалить</a>';
//            }
            $sql_[$key]['status'] = '<span class="'.self::$status[$row['status']]['color_class'].'">'.self::$status[$row['status']]['text'].'</span>';
            $sql_[$key]['status_bug'] = self::getStatusData((int)$row['status']);
            $sql_[$key]['name'] = $row['user_search_pref'];

            if ($row['user_sex'] == 1) {
                $sql_[$key]['sex'] = 'добавил';
            } else {
                $sql_[$key]['sex'] = 'добавила';
            }

            if ($row['user_photo']) {
                $sql_[$key]['ava'] = '/uploads/users/' . $row['uids'] . '/50_' . $row['user_photo'];
            } else {
                $sql_[$key]['ava'] = '/images/no_ava_50.png';
            }

            $comments = $this->database->fetchALL("SELECT id, author_user_id, bug_id, text, add_date, status FROM `bugs_comments` WHERE bug_id = {$row['id']}  ORDER by `add_date`", true);
            foreach ($comments as $key2 => $comment){
                if ($comment['status'] > 0){
                    $comments[$key2]['status_info'] = 'Статус изменен на '.self::$status[$comment['status']]['text'];
                }else{
                    $comments[$key2]['status_info'] = '';
                }


            }
            $sql_[$key]['comments'] = $comments;

        }
        return $sql_;
    }

}