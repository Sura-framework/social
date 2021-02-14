<?php

declare(strict_types=1);

namespace App\Modules;

use Exception;
use JsonException;
use Sura\Libs\Request;
use Sura\Libs\Tools;
use Sura\Libs\Validation;
use Throwable;

class HomeController extends Module{
    public int $counter;

    /**
     * Главная страница
     *
     * @param $params
     * @return int
     */
    public function index($params): int
    {
        $logged = $this->logged();
        if ($logged === true){
            $params['title'] = 'Новости'.' | Sura';
            try {
                return (new FeedController)->feed();
            } catch (Exception $e) {
                echo $e->getMessage();
                return 1;
                // Session::set('error', $e->getMessage());
            } catch (Throwable $e) {
                echo $e->getMessage();
                return 1;
            }
        }else{
            $params['title'] = 'Sura';
            return view('reg', $params);
        }
    }

    /**
     * @return int
     * @throws JsonException
     */
    public function Theme(): int
    {
        $request = (Request::getRequest()->getGlobal());
        if ($request['set_theme'] > 0 || $request['set_theme'] !== 0){
            if ($request['theme'] == 'dark' || $request['theme'] == 1){
                Tools::set_cookie("theme", '1', 30);
                $data = '<link media="screen" href="/style/dark.css" type="text/css" rel="stylesheet" />';
            }else{
                Tools::set_cookie("theme", '0', 30);
                $data = '';
            }
        }elseif ($request['theme'] == 0 || $request['theme'] == "0"){
            Tools::set_cookie("theme", '0', 30);
                $data = '';
        }else{
            Tools::set_cookie("theme", '0', 30);
            $data = '';
        }
        return  _e( json_encode(array(
            'res' => $data,
            'status' => 1,
        ), JSON_THROW_ON_ERROR) );
    }

    /**
     * Test page
     *
     */
    public function Test(): int
    {

        return 1;
    }

    /**
     * Alias
     * (Profile OR Group)
     *
     * @param $params
     * @return int
     */
    public function alias($params): int
    {
        $db = $this->db();
        $server = Request::getRequest()->server;
        $path = explode('/', $server['REQUEST_URI']);
        $alias = $path['1'];

        $params = (array)$params;
        if($alias){
            $alias_public = $db->super_query("SELECT id,title FROM `communities` WHERE adres = '".$alias."' "); //Проверяем адреса у публичных страниц
            if($alias_public) {
                $params['alias'] = $alias_public['id'];
                try {
                    return (new PublicController())->index($params);
                } catch (Throwable) {
                    echo 'err';
                }
//                return _e( 'Доменное имя <b>'.$alias.'</b> занято. Группа');
            }

            $alias_user = $db->super_query("SELECT user_id, user_search_pref FROM `users` WHERE alias = '".$alias."'"); // Проверяем адреса у пользователей
            if ($alias_user) {
                $params['alias'] = $alias_user['user_id'];
                try {
                    return (new ProfileController())->index($params);
                } catch (Throwable) {
                    echo 'err';
                }
            }
            $params['adress'] = 'false';
            $params['params'] = '';
            $params = array($params);

            http_response_code(404);
            $class = 'App\Modules\ErrorController';
            $foo = new $class();
            $string =  call_user_func_array(array($foo, $action = 'Index'), $params);
            return _e((string)$string);
        }else{
            return 0;
        }
    }
}
