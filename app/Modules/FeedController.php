<?php
declare(strict_types = 1);
namespace App\Modules;

use App\Libs\Wall;
use App\Libs\Wall2;
use App\Models\News;
use Exception;
use Sura\Libs\Request;
use Sura\Libs\Status;


/**
 * Новости
 *
 * Class FeedController
 */
class FeedController extends Module
{

    /** @var int  */
    private int $limit = 20;

    /**
     * Показ предыдущих записей
     *
     * @return int
     * @throws Exception|\Throwable
     */
    public function next(): int
    {
//        $lang = $this->get_langs();
        $logged = $this->logged();
        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];

        $request = (Request::getRequest()->getGlobal());
        $News = new News;

//        Tools::NoAjaxRedirect();

        //Если вызваны предыдущие новости
        if($logged && $request['page_cnt']) {

//                $limit = 20;
            $page = (int)$request['page_cnt'] *$this->limit;

            $sql_ = $News->load_news($user_id, $page);

//            $sql_ = Wall::build($sql_);
            $sql_ = Wall2::build_news($sql_);
            $news_count = count($sql_);
            if ($news_count > 0){
                $status = Status::OK;
                $res =  view('news.one_record', array('news' => $sql_));
            }else{
                $status = Status::NOT_FOUND;
                $res = '';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $res = '';
        }
        return _e( json_encode(array(
            'status' => $status,
            'res' => $res,
        ), JSON_THROW_ON_ERROR) );
    }

    /**
     * Вывод новостей
     *
     * @return int
     * @throws \Throwable
     */
    public function feed(): int
    {
        if(!isset($params['title'])) {
            $params['title'] = 'Новости' . ' | Sura';
        }

        $lang = $this->get_langs();
        $logged = $this->logged();
//        $logged = $params['user']['logged'];

        if ($logged){
//            $request = (Request::getRequest()->getGlobal());
//            $user_info = $params['user']['user_info'];
//            $user_id = $params['user']['user_info']['user_id'];

            $user_info = $this->user_info();
            $user_id = $user_info['user_id'];
            $params['stories'] = (new \App\Models\Stories)->all($user_id);
            $params['user_id'] = $user_id;

            //Сообщения
            if (isset($params['user_pm_num'])){
                $params['msg'] = $params['user_pm_num'];
            }else{
                $params['msg'] = '';
            }

            //Заявки в друзья
            $params['requests_link'] = $params['requests_link'] ?? '';
            $params['demands'] = $params['demands'] ?? '';

            //Отметки на фото
            if($user_info['user_new_mark_photos']){
                $params['my_id'] =  'newphotos';
            } else{
                $params['my_id'] =  $user_id;
                $params['new_photos'] = '';
            }

            //Приглашения в сообщества
            $params['new_groups_lnk'] = $params['new_groups_lnk'] ?? '/groups/';
            $params['new_groups'] = $params['new_groups'] ?? '';
            //Новости
            $params['new_news'] = $params['new_news'] ?? '';
            $params['news_link'] = $params['news_link'] ?? '';
            //Поддержка
            $params['support'] = $params['support'] ?? '';
            //UBM
            $params['new_ubm'] = $params['new_ubm'] ?? '';
            $params['gifts_link'] = $params['gifts_link'] ?? '/balance/';

            $News = new News;
            $sql_ = $News->load_news($user_id, 0);

//            $limit = 20;
//            $count_all = 100;
/*
            $page = $request['page'] ?? 1;
            $page_num = $page ?? 1;
            $navi = new Navigation( "/" );
            $navi->tpl = "{page}/";
            $navi->spread = 4;
            $params['nav'] = $navi->build( $limit, $count_all, $page_num );
*/

            if($sql_){

//                $params['news'] = $sql_;
                $params['news'] = Wall2::build_news($sql_);

                //Выводи низ, если новостей больше 20
//                if($c > 19 AND !$_POST['page_cnt']){
                    $params['bottom'] = true;

//                }

                return view('news.news', $params);
            }

//            $params['title'] = 'no news'.$lang['no_infooo'];

            $params['news'] = $sql_;
            return view('news.news', $params);
        }
        $params['title'] = 'no_news '.$lang['no_infooo'];
        $params['info'] = $lang['not_logged'];
        return view('info.info', $params);
    }
}