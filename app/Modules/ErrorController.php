<?php


namespace App\Modules;


class ErrorController extends Module
{
    /**
     * @param $params
     * @return int
     */
    public function Index($params): int
    {
        var_dump($params);
//        $params = array();
        $lang = $this->get_langs();

        if (!isset($lang['not_logged'])){
            $lang['not_logged'] = "not_logged";
        }
//        $params['title'] = 'err';
//        $params['title'] = 'err'.$lang['no_infooo'];
        if (isset($params['error'])){
            $params['info'] = '#'.$params['error'].' '.$params['error_name'];
        }else{
            $params['info'] = 'not_logged';
//            $params['info'] = $lang['not_logged'];
        }
        return view('info.info', $params);
 }
}