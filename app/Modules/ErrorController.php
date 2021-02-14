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
        $params['title'] = 'err'.$lang['no_infooo'];
        $params['info'] = $lang['not_logged'];
        return view('info.info', $params);
 }
}