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
        $lang = $this->get_langs();

        if (!isset($lang['not_logged'])){
            $lang['not_logged'] = "not_logged";
        }
        if (isset($params['error'])){
            $params['info'] = '#'.$params['error'].' '.$params['error_name'];
        }else{
            $params['info'] = 'not_logged';
        }
        return view('info.info', $params);
 }
}