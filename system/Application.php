<?php


namespace System;


use System\Classes\Db;
use System\Classes\Templates;
use System\Libs\Registry;
use System\Libs\Router;

class Application
{
    const VERSION = '1.0';

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    function init(){

    }

    function routing($params){
        $router = Router::fromGlobals();
        require __DIR__ . '/../routes/web.php';
        if ($router->isFound()) {
            $router->executeHandler(
                $router->getRequestHandler(),
//                $router->getParams()
                $params
            );
        }else {
            echo 'error';
            http_response_code(404);
            die();
        }
    }

    function user_online($params){

        //$logged = Registry::get('logged');
        $logged = $params['user']['logged'];

        //Елси юзер залогинен то обновляем последнюю дату посещения на личной стр
        if($logged){
            //$user_info = Registry::get('user_info');
            $user_info = $params['user']['user_info'];
            $db = Db::getDB();
            //$db = $params['db'];

            //Начисления 1 убм.
            if(!$user_info['user_lastupdate']) $user_info['user_lastupdate'] = 1;

            $server_time = intval($_SERVER['REQUEST_TIME']);

            if(date('Y-m-d', $user_info['user_lastupdate']) < date('Y-m-d', $server_time))
                $sql_balance = ", user_balance = user_balance+1, user_lastupdate = '{$server_time}'";

            //Определяем устройство
            if(check_smartphone()){
                if($_SESSION['mobile'] != 2)
                    $config['temp'] = "mobile";
                $check_smartphone = true;
            }else{
                $check_smartphone = false;
            }

            if($check_smartphone) $device_user = 1;
            else $device_user = 0;
            if(($user_info['user_last_visit'] + 60) <= $server_time){
                $db->query("UPDATE LOW_PRIORITY `".PREFIX."_users` SET user_logged_mobile = '{$device_user}', user_last_visit = '{$server_time}' {$sql_balance} WHERE user_id = '{$user_info['user_id']}'");
            }


            return true;
        }
        return true;
    }

    function view(){
        $config = include __DIR__.'/data/config.php';
        $tpl = new Templates();
        $tpl->dir = __DIR__.'/../templates/'.$config['temp'];
        Registry::set('tpl', $tpl);
        return $tpl;
    }
}