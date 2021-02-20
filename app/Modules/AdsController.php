<?php

namespace App\Modules;

use Intervention\Image\ImageManager;
use Sura\Libs\Registry;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;

/**
 * Class AdsController
 * @package App\Modules
 */
class AdsController extends Module{

    /**
     */
    public function clickgo(){
        $user_info = Registry::get('user_info');
        $db = $this->db();
        $logged = Registry::get('logged');

        Tools::NoAjaxRedirect();

        if(isset($logged)){
//            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

            $aid = intval($_POST['aid']);
            $ads = $db->super_query("SELECT typepay, click, active, price, link FROM `ads` WHERE id = '{$aid}'");
            if($ads){
                $db->query("UPDATE `ads` SET click = click+1 WHERE id = '{$aid}'");
                if($ads['typepay'] == 1){
                    $db->query("UPDATE `ads` SET price = price-1 WHERE id = '{$aid}'");
                    if($ads['price']-1 == 0){
                        $db->query("UPDATE `ads` SET active = 0 WHERE id = '{$aid}'");
                    }
                }
                echo $ads['link'];
            }
        }
    }

    /**
     * @param $params
     */
    public function status_ad(){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){

            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

            $aid = intval($_POST['aid']);
            $row = $db->super_query("SELECT typepay, active FROM `ads` WHERE author = '{$user_id}' AND id = '{$aid}'");
            if($row){
                if($row['active'] == 1){
                    $newactive = 0;
                    $texta = 'остановлено.';
                } else {
                    $newactive = 1;
                    $texta = 'запущено.';
                }
                $mybalance = $db->super_query("SELECT balance_rub FROM `users` WHERE user_id = '{$user_id}'");
                if($row['typepay'] == 1){
                    if($mybalance['balance_rub'] > 4){
                        $db->query("UPDATE `ads` SET active = '{$newactive}' WHERE id = '{$aid}'");
                        $result = 'Успешно '.$texta;
                    } else {
                        $result = 'Не хватает баланса.';
                    }
                } else {
                    if($mybalance['balance_rub'] > 0){
                        $db->query("UPDATE `ads` SET active = '{$newactive}' WHERE id = '{$aid}'");
                        $result = 'Успешно '.$texta;
                    } else {
                        $result = 'Не хватает баланса.';
                    }
                }
            } else {
                $result = 'Ошибка';
            }
            echo $result;
        }
    }

    /**
     * @param $params
     * @return bool
     */
    public function cabinet(){
        $tpl = $params['tpl'];
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){
            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

            $mybalance = $db->super_query("SELECT balance_rub FROM `users` WHERE user_id = '{$user_id}'");
            $myads = $db->super_query("SELECT COUNT(*) AS cnt FROM `ads` WHERE author = '{$user_id}'");
            $tpl->load_template('ads/cabinet.tpl');
            $tpl->set('{balance}', $mybalance['balance_rub']);
            if($myads['cnt']){
                $sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS * FROM `ads` WHERE author = '{$user_id}' ORDER by `views` DESC", 1);
                $myads2 = '';
                foreach($sql_ as $row){
                    if($row['active'] == '1'){
                        $active = 'status_on';
                    } else {
                        $active = 'status_off';
                    }
                    $myads2 .= <<<HTML
                            <tr class="paginated_table_row">
                            <td class="paginated_table_cell first_column cb_cell">
                            <a id="cb_row_0_4" class="ads_lite_cb"></a>
                            </td>
                            <td id="cell_0_0" class="paginated_table_cell column_name_view" style="white-space: nowrap;">
                            <div class="ads_paginated_table_name"><a href="{$row['link']}" target="_blanck">{$row['text']}</a></div>
                            </td>
                            <td id="cell_0_1" class="paginated_table_cell column_status_view pt_align_center" style="white-space: nowrap;">
                            <img id="status_progress" src="/images/upload.gif" style="display: none;">
                            <span id="status_selector_box" class="ads_status_selector_box  pnt" style="white-space: nowrap;" >
                            <span class="ads_status_image_span {$active}" id="status_{$row['id']}" onclick="ads.status_ad('{$row['id']}')"></span>
                            </span>
                            </td>
                            <td id="cell_0_4" class="paginated_table_cell column_money_amount_view" style="white-space: nowrap;">{$row['balance']} руб.</td>
                            <td id="cell_0_6" class="paginated_table_cell column_clicks_count_view" style="white-space: nowrap;">{$row['click']}</td>
                            <td id="cell_0_7" class="paginated_table_cell last_column column_views_count_view" style="white-space: nowrap;">{$row['views']}</td>
                            </tr>
                            HTML;
                }
                $tpl->set('{myads}', '<div class="paginated_table"><table class="ads_unions_table"><tbody>

                        <tr class="paginated_table_header">
                        <th class="paginated_table_cell first_column cb_cell">
                        <a id="total_cb_4" class="ads_lite_cb"></a>
                        </th>
                        <th class="paginated_table_cell column_name_view" style="white-space: nowrap;">
                        <div><span class="table_header_upper_span">Название</span></div>
                        </th>
                        <th class="paginated_table_cell column_status_view pt_align_center" style="white-space: nowrap;">
                        <div><span class="table_header_upper_span">Статус</span></div>
                        </th>
                        <th class="paginated_table_cell column_money_amount_view" style="white-space: nowrap;">
                        <div><span class="table_header_upper_span">Потрачено</span></div>
                        </th>
                        <th class="paginated_table_cell column_clicks_count_view" style="white-space: nowrap;">
                        <div><span class="table_header_upper_span">Переходы</span></div>
                        </th>
                        <th class="paginated_table_cell last_column column_views_count_view" style="white-space: nowrap;">
                        <div><span class="table_header_upper_span">Показы</span></div>
                        </th>
                        </tr>
                           '.$myads2.'
                            
                        <tr class="paginated_table_footer">
                        <td class="paginated_table_cell column_name_view" colspan="2" style="white-space: nowrap;"></td>
                        <td class="paginated_table_cell column_status_view pt_align_center" style="white-space: nowrap;"></td>
                        <td class="paginated_table_cell column_money_amount_view" style="white-space: nowrap;"></td>
                        <td class="paginated_table_cell column_clicks_count_view" style="white-space: nowrap;"></td>
                        <td class="paginated_table_cell last_column column_views_count_view" style="white-space: nowrap;"></td>
                        </tr>
                        
                        </tbody></table></div>');
            } else {
                $tpl->set('{myads}', '<div class="info_center">У Вас нет объявлений. Вы можете создавать объявления и следить за их статистикой.</div>');
            }
            $tpl->compile('content');

            $params['tpl'] = $tpl;
            Page::generate();
            return true;
        }
    }

    /**
     * @param $params
     * @return bool
     */
    public function create(){
        $tpl = $params['tpl'];
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){
            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

            $tpl->load_template('ads/create.tpl');

            $tpl->compile('content');

            $params['tpl'] = $tpl;
            Page::generate();
            return true;
        }
    }

    /**
     * @param $params
     */
    public function optionad(){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){
            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

            $whad = $_POST['whad'];
            if($whad == 'url'){
                $res = '<div class="texta"></div><input type="text" id="url" class="inpst" style="width:400px;" placeholder="Введите ссылку на рекламируемый объект" /><div class="button_div fl_r"><button onClick="ads.checkurl(); return false" id="checkurl">Проверить домен</button></div><div class="mgclr"></div><div id="domainch"></div>';
            } elseif($whad == 'app'){
                $sql_app = $db->super_query("SELECT tb1.game_id, tb2.title, traf, poster FROM `games_users` tb1, `games` tb2 WHERE tb1.game_id = tb2.id AND tb1.user_id = '{$user_id}' ORDER by `lastdate` DESC LIMIT 0, 50", 1);
                $res = "<div class=\"texta\"></div><div id=\"container19\" class=\"selector_container dropdown_container fl_l selector_focused editpr_fieldlist\"><table cellspacing=\"0\" cellpadding=\"0\" class=\"selector_table\"><tbody><tr><td class=\"selector\"><span class=\"selected_items\"></span><input type=\"text\" class=\"selector_input selected\" readonly=\"true\"  value=\"Выберите приложение, которое будете рекламировать\" style=\"color: rgb(0, 0, 0); width: 322px; \" id=\"container19\" ><input type=\"hidden\"  name=\"appid\" id=\"appid\" value=\"0\" class=\"resultField\" ></td><td id=\"container19\" class=\"selector_dropdown\" style=\"width: 25px; \">&nbsp;</td></tr></tbody></table><div class=\"results_container\" style=\"display:none\"><div class=\"result_list\" style=\"opacity: 1; width: 365px; height: 161px; bottom: auto; visibility: visible;overflow-x: hidden; overflow-y: visible;\"><ul>";
                if($sql_app){
                    $s++;
                    foreach($sql_app as $row) {
                        if($row['poster']){
                            $poster = '/uploads/apps/'.$row['game_id'].'/'.$row['poster'];
                        } else {
                            $poster = '/uploads/no_app.gif';
                        }
                        $res .= '<li onmousemove="Select.itemMouseMove(19, '.$s.')" val="'.$row['game_id'].'" class="" style="height:28px"><img src="'.$poster.'" height="25px" style="position:absolute"><span style="margin-left:35px">'.$row['title'].'</span></li>';
                    }
                }
                $res .= "</ul></div><div class=\"result_list_shadow\" style=\"width: 365px; margin-top: 161px; \" ><div class=\"shadow1\"></div><div class=\"shadow2\"></div></div></div></div><div class=\"mgclr\"></div><div class=\"texta\"></div><div class=\"button_div fl_l\"><button onClick=\"ads.ads_continue('app'); return false\" id=\"continue\">Продолжить</button></div><div id=\"nextcreate\"></div><div class=\"mgclr\"></div";
            } elseif($whad == 'pub'){
                $sql_groups = $db->super_query("SELECT SQL_CALC_FOUND_ROWS id, title, traf, photo, ulist FROM `communities` WHERE ulist regexp '[[:<:]]({$user_id})[[:>:]]' ORDER by `traf` DESC LIMIT 0, 50", 1);
                $res = "<div class=\"texta\"></div><div id=\"container9\" class=\"selector_container dropdown_container fl_l selector_focused editpr_fieldlist\"><table cellspacing=\"0\" cellpadding=\"0\" class=\"selector_table\"><tbody><tr><td class=\"selector\"><span class=\"selected_items\"></span><input type=\"text\" class=\"selector_input selected\" readonly=\"true\"  value=\"Выберите сообщество, которое будете рекламировать\" style=\"color: rgb(0, 0, 0); width: 322px; \" id=\"container9\" ><input type=\"hidden\"  name=\"publicid\" id=\"publicid\" value=\"0\" class=\"resultField\" ></td><td id=\"container9\" class=\"selector_dropdown\" style=\"width: 25px; \">&nbsp;</td></tr></tbody></table><div class=\"results_container\" style=\"display:none\"><div class=\"result_list\" style=\"opacity: 1; width: 365px; height: 161px; bottom: auto; visibility: visible;overflow-x: hidden; overflow-y: visible;\"><ul>";
                if($sql_groups){
                    $s++;
                    foreach($sql_groups as $row) {
                        $res .= '<li onmousemove="Select.itemMouseMove(9, '.$s.')" val="'.$row['id'].'" class="" style="height:28px"><img src="/uploads/groups/'.$row['id'].'/50_'.$row['photo'].'" height="25px" style="position:absolute"><span style="margin-left:35px">'.$row['title'].'</span></li>';
                    }
                }
                $res .= "</ul></div><div class=\"result_list_shadow\" style=\"width: 365px; margin-top: 161px; \" ><div class=\"shadow1\"></div><div class=\"shadow2\"></div></div></div></div><div class=\"mgclr\"></div><div class=\"texta\"></div><div class=\"button_div fl_l\"><button onClick=\"ads.ads_continue('pub'); return false\" id=\"continue\">Продолжить</button></div><div id=\"nextcreate\"></div><div class=\"mgclr\"></div";
            } else {
                $res = 'Неизвестная ошибка.';
            }
            echo $res;
            die();

        }
    }

    /**
     * @param $params
     */
    public function checkurl(){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){
            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

            $url = $_POST['url'];
            if (strpos($url, 'https://') !== false){
                $domain = parse_url($url);
                $res = "<div class=\"texta\"></div><input type=\"text\" id=\"domain\" disabled class=\"inpst\" style=\"width:400px;\" value=\"{$domain["host"]}\"/><div class=\"mgclr\"></div><div class=\"texta\"></div><div class=\"button_div fl_l\"><button onClick=\"ads.ads_continue('url'); return false\" id=\"continue\">Продолжить</button></div><div class=\"mgclr\"></div><div id=\"nextcreate\"></div>";
            } else {
                $res = '<div class="texta"></div><input type="text" id="domain" disabled class="inpst" style="width:400px;" value="Домен не определен. Попробуйте еще раз."/><div class="mgclr"></div>';
            }
            echo $res;
            die();

        }
    }

    /**
     * @param $params
     */
    public function bigtype(){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){
            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

            $type = intval($_POST['type']);
            $publicid = intval($_POST['publicid']);
            $appid = intval($_POST['appid']);

            $result = '';

            if($type == 3){
                $public = $db->super_query("SELECT title, traf, photo FROM `communities` WHERE id = '{$publicid}'");
                if($public){
                    $ava = $public['photo'] ? 'uploads/groups/'.$publicid.'/100_'.$public['photo'] : 'images/ads/ads_size_p.png';
                    $result = array(
                        'ava' => $ava,
                        'title' => $public['title'],
                        'traf' => $public['traf']
                    );
                }
                echo json_encode($result);
            } elseif($type == 4){
                $app = $db->super_query("SELECT title, poster, traf FROM `games` WHERE id = '{$appid}'");
                if($app){
                    $ava = $app['poster'] ? 'uploads/apps/'.$appid.'/'.$app['poster'] : 'images/ads/ads_size_p.png';
                    $result = array(
                        'ava' => $ava,
                        'title' => $app['title'],
                        'traf' => $app['traf']
                    );
                }
                echo json_encode($result);
            }
            die();

        }
    }

    /**
     * @param $params
     */
    public function nextcreate(){
        $tpl = $params['tpl'];
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){
            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

//            Tools::NoAjaxQuery();
            $type = $_POST['type'];
            if($type == 'url' || $type == 'app' || $type = 'pub'){
                $tpl->load_template('ads/create/'.$type.'.tpl');
                $tpl->set('{country}', $countrysl);
                $tpl->set('{country_id}', $id_countrysl);
                $tpl->set('{country_name}', $name_countrysl);
                $tpl->set('{sex}', InstallationSelectedNew(0, '<li onmousemove="Select.itemMouseMove(2, 0)" val="0" class="">- Не выбрано -</li><li onmousemove="Select.itemMouseMove(2, 1)" val="1" class="">Мужской</li><li onmousemove="Select.itemMouseMove(2, 2)" val="2" class="">Женский</li>'));
                for($age = 14; $age <= 80; $age++){
                    $textage .= '<li onmousemove="Select.itemMouseMove(3, '.$age.')" val="'.$age.'" class="">От '.$age.'</li>';
                }
                $tpl->set('{age}', InstallationSelectedNew(0, '<li onmousemove="Select.itemMouseMove(3, 0)" val="0" class="">Любой</li>'.$textage));
                $tpl->compile('content');
            }
        }
    }

    /**
     * @param $params
     */
    public function loadage(){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){
            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

            $agefirst = intval($_POST['agefirst']);
            if($agefirst){
                $textage = '<div id="container4" class="selector_container dropdown_container fl_l selector_focused editpr_fieldlist"><table cellspacing="0" cellpadding="0" class="selector_table"><tbody><tr><td class="selector"><span class="selected_items"></span><input type="text" class="selector_input selected" readonly="true"  value="Любой" style="color: rgb(0, 0, 0); width: 122px; " id="container4" ><input type="hidden"  name="agelast" id="agelast" value="0" class="resultField" ></td><td id="container4" class="selector_dropdown" style="width: 25px; ">&nbsp;</td></tr></tbody></table><div class="results_container" style="display:none"><div class="result_list" style="opacity: 1; width: 165px; height: 161px; bottom: auto; visibility: visible;overflow-x: hidden; overflow-y: visible;"><ul>';
                for($age = $agefirst; $age <= 80; $age++){
                    $textage .= '<li onmousemove="Select.itemMouseMove(4, '.$age.')" val="'.$age.'" class="">До '.$age.'</li>';
                }
                $textage .= '</ul></div><div class="result_list_shadow" style="width: 165px; margin-top: 161px; " ><div class="shadow1"></div><div class="shadow2"></div></div></div></div>';
                echo $textage;
            }
            die();

        }
    }

    /**
     * @param $params
     */
    public function uploadimg(){
        $tpl = $params['tpl'];
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){
            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

//            Tools::NoAjaxQuery();
            $tpl->load_template('ads/uploadimg.tpl');
            $tpl->compile('content');
        }
    }

    /**
     * @param $params
     */
    public function upload(){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){
            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

//            Tools::NoAjaxQuery();
            //Разришенные форматы
            $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

            //Получаем данные о фотографии
            $image_tmp = $_FILES['uploadfile']['tmp_name'];
            $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
            $server_time = \Sura\Time\Date::time();
            $image_rename = substr(md5($server_time+rand(1,100000)), 0, 20); // имя фотографии
            $image_size = $_FILES['uploadfile']['size']; // размер файла
            $array = explode(".", $image_name);
            $type = end($array); // формат файла

            //Проверям если, формат верный то пропускаем
            if(in_array(strtolower($type), $allowed_files)){
                if($image_size < 5000000){
                    $res_type = strtolower('.'.$type);

                    $upload_dir = __DIR__."/../../public/uploads/ads/";

                    if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){
                        $manager = new ImageManager(array('driver' => 'gd'));
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(140, 230);
                        $image->save($upload_dir.$image_rename.'.webp', 90);
                        unlink($upload_dir.$image_rename.$res_type);
                        $res_type = '.webp';

                        //Результат для ответа
                        echo $image_rename.$res_type;

                        Cache::mozg_clear_cache_folder('ads');
                    } else
                        echo 'big_size';
                } else
                    echo 'big_size';
            } else
                echo 'bad_format';
            die();

        }
    }

    /**
     * @param $params
     */
    public function createad(){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if(isset($logged)){
            $user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

            $text = $_POST['name'];
            $description = $_POST['description'];
            $whads = $_POST['whad'];
            $type = intval($_POST['type']);
            $typepay = intval($_POST['pay']);
            $image = $_POST['photo'];
            $link = $_POST['url'];
            $price = intval($_POST['price']);
            if($typepay == 2){
                $views = $price;
                $click = 0;
            } else {
                $views = 0;
                $click = $price;
            }
            /* Settings */
            $country = intval($_POST['country']);
            $sex = intval($_POST['sex']);
            $agefrom = intval($_POST['agefrom']);
            $agelast = intval($_POST['agelast']);
            $sp = intval($_POST['sp']);
            $mybalance = $db->super_query("SELECT balance_rub FROM `users` WHERE user_id = '{$user_id}'");
            if($typepay == 1){
                $bal = $price*5;
            } else {
                $bal = $price;
            }

            $result = '';

            if($text && $whads && $type && $typepay && $link && $mybalance['balance_rub'] >= $bal){
                if($whads == 'url'){
                    $whads = 1;
                    $db->query("INSERT INTO `ads` SET `text` = '{$text}', `description` = '{$description}', `whads` = '{$whads}', `type` = '{$type}', `typepay` = '{$typepay}', `image` = '{$image}', `link` = '{$link}', `price` = '{$price}', `author` = '{$user_id}', `balance` = '{$bal}'");
                    $adid = $db->insert_id();
                    $db->query("INSERT INTO `ads_settings` SET `idad` = '{$adid}', `country` = '{$country}', `sex` = '{$sex}', `agef` = '{$agefrom}', `agel` = '{$agelast}', `sp` = '{$sp}'");
                    $result = 'success';
                } elseif($whads == 'pub'){
                    $whads = 3;
                    $link = 'public'.intval($_POST['url']);
                    $db->query("INSERT INTO `ads` SET `text` = '{$text}', `description` = '{$description}', `whads` = '{$whads}', `type` = '{$type}', `typepay` = '{$typepay}', `image` = '{$image}', `link` = '{$link}', `price` = '{$price}', `author` = '{$user_id}', `balance` = '{$bal}'");
                    $adid = $db->insert_id();
                    $db->query("INSERT INTO `ads_settings` SET `idad` = '{$adid}', `country` = '{$country}', `sex` = '{$sex}', `agef` = '{$agefrom}', `agel` = '{$agelast}', `sp` = '{$sp}'");
                    $result = 'success';
                } elseif($whads == 'app'){
                    $whads = 2;
                    $link = 'app'.intval($_POST['url']);
                    $db->query("INSERT INTO `ads` SET `text` = '{$text}', `description` = '{$description}', `whads` = '{$whads}', `type` = '{$type}', `typepay` = '{$typepay}', `image` = '{$image}', `link` = '{$link}', `price` = '{$price}', `author` = '{$user_id}', `balance` = '{$bal}'");
                    $adid = $db->insert_id();
                    $db->query("INSERT INTO `ads_settings` SET `idad` = '{$adid}', `country` = '{$country}', `sex` = '{$sex}', `agef` = '{$agefrom}', `agel` = '{$agelast}', `sp` = '{$sp}'");
                    $result = 'success';
                }
                $db->query("UPDATE `users` SET balance_rub = balance_rub-{$bal} WHERE user_id = '{$user_id}'");
            }
            echo $result;
            die();

        }
    }

    /**
     * @param $params
     * @return bool
     */
    public function index()
    {
        $tpl = $params['tpl'];

        $db = $this->db();
        //$user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        if($logged){
            //$act = $_GET['act'];
            //$user_id = $user_info['user_id'];
            $params['title'] = 'Реклама';

            $row = null; //!NB bug
            $c = null; //!NB bug

            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `id` ASC", 1);
            $csl = $row['user_countrysl'];
            if($c == 0){
                $class_on = 'active';
                $name_countrysl = 'Выбор страны';
                $id_countrysl = '0';
            }
            $countrysl = Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, 0)" val="0" class="">- Не выбрано -</li>');
            foreach($sql_country as $sql1) {
                if($csl == $sql1['id']) {
                    $class = 'active';
                    $name_countrysl = $sql1['name'];
                    $id_countrysl = $sql1['id'];
                }
                $countrysl .= Tools::InstallationSelectedNew($row['user_countrysl'],'<li onmousemove="Select.itemMouseMove(1, '.$sql1['id'].')" val="'.$sql1['id'].'" class="">'.$sql1['name'].'</li>');
            }

            $tpl->load_template('ads/ads_target.tpl');
            $tpl->compile('content');
            $tpl->clear();
            $db->free();
        } else {
            $lang = $this->get_langs();
            $user_speedbar = $lang['no_infooo'];
            msg_box( $lang['not_logged'], 'info');
        }

        $params['tpl'] = $tpl;
        Page::generate();
        return true;
    }

    /**
     * @param $params
     */
    public function view_ajax(){
        $user_info = $this->user_info();
        $logged = $this->logged();
        $db = $this->db();
        $tpl = $params['tpl'];

        Tools::NoAjaxRedirect();

        if(isset($logged)){

            $user_sex = $user_info['user_sex'];
            $user_birthday = explode('-', $user_info['user_birthday']);
            $user_age = \App\Libs\Profile::user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]);
            //$ad = $db->super_query("SELECT * FROM `ads` WHERE price != '0' and active = '1' ORDER BY RAND() LIMIT 5");
            $ad = $db->super_query("SELECT tb1.whads, id, link, image, text, description, type, views, price, active, tb2.idad, country, city, sex, agef, agel, sp FROM `ads` tb1, `ads_settings` tb2 WHERE (tb1.price != '0' and tb1.active = '1' AND tb1.id = tb2.idad) AND ((tb2.country = '{$user_info['user_country']}' OR tb2.country = 0) AND (tb2.sex = 0 OR tb2.sex = '{$user_sex}') AND (tb2.agef >= '{$user_age}' OR tb2.agef = 0) AND (tb2.agel <= '{$user_age}' OR tb2.agel = 0)) ORDER BY RAND() LIMIT 5");
            if($ad){
                $traf = '';
                $temp = '';
                if($ad['whads'] == 1){
                    $temp = 'url';
                } elseif($ad['whads'] == 3){
                    $temp = 'pub';
                    $pid = substr($ad['link'], 6);
                    $row = $db->super_query("SELECT traf FROM `communities` WHERE id = '{$pid}'");
                    $traf = $row['traf'];
                } elseif($ad['whads'] == 2){
                    $temp = 'app';
                    $aid = substr($ad['link'], 3);
                    $row = $db->super_query("SELECT traf FROM `games` WHERE id = '{$aid}'");
                    $traf = $row['traf'];
                }
                $db->query("UPDATE `ads` SET views = views+1 WHERE id = '{$ad['id']}'");
                if($ad['typepay'] == 2){
                    $db->query("UPDATE `ads` SET price = price-1 WHERE id = '{$ad['id']}'");
                    if($ad['price']-1 == 0){
                        $db->query("UPDATE `ads` SET active = 0 WHERE id = '{$ad['id']}'");
                    }
                }
                $tpl->load_template('ads/view_ajax/'.$temp.'_'.$ad['type'].'.tpl');
                $tpl->set('{url}', $ad['link']);
                $tpl->set('{image}', $ad['image']);
                $tpl->set('{name}', $ad['text']);
                $tpl->set('{descr}', $ad['description']);
                $click = "ads.clickgo('{$ad['id']}');";
                $tpl->set('{click}', $click);
                $titles = array('участник', 'участника', 'участников');
                $tpl->set('{traf}', $traf.' '.Gramatic::declOfNum($traf, $titles));
                $tpl->compile('content');
            }

            die();
        }else{
            $user_sex = 0;
            //$user_birthday = explode('-', '0-0-0');
            //$user_age = 0;
            $user_age_f = 0;
            $user_age_l = 100;
            $user_info['user_country'] = 0;



            $ad = $db->super_query("SELECT tb1.whads, id, link, image, text, description, type, views, price, active, tb2.idad, country, city, sex, agef, agel, sp FROM `ads` tb1, `ads_settings` tb2 WHERE (tb1.price != '0' and tb1.active = '1' AND tb1.id = tb2.idad) AND ((tb2.country = '{$user_info['user_country']}' OR tb2.country = 0) AND (tb2.sex = 0 OR tb2.sex = '{$user_sex}') AND (tb2.agef >= '{$user_age_f}' OR tb2.agef = 0) AND (tb2.agel <= '{$user_age_l}' OR tb2.agel = 0)) ORDER BY RAND() LIMIT 5");
            if($ad){
                $temp = '';
                if($ad['whads'] == 1){
                    $temp = 'url';
                } elseif($ad['whads'] == 3){
                    $temp = 'pub';
                    $pid = substr($ad['link'], 6);
                    $row = $db->super_query("SELECT traf FROM `communities` WHERE id = '{$pid}'");
                    $traf = $row['traf'];
                    $tpl->set('{traf}', $traf.' '.Gramatic::declOfNum($traf, array('участник', 'участника', 'участников')));
                } elseif($ad['whads'] == 2){
                    $temp = 'app';
                    $aid = substr($ad['link'], 3);
                    $row = $db->super_query("SELECT traf FROM `games` WHERE id = '{$aid}'");
                    $traf = $row['traf'];
                    $tpl->set('{traf}', $traf.' '.Gramatic::declOfNum($traf, array('участник', 'участника', 'участников')));
                }
                $db->query("UPDATE `ads` SET views = views+1 WHERE id = '{$ad['id']}'");
                if(!empty($ad['typepay']) AND $ad['typepay'] == 2){
                    $db->query("UPDATE `ads` SET price = price-1 WHERE id = '{$ad['id']}'");
                    if($ad['price']-1 == 0){
                        $db->query("UPDATE `ads` SET active = 0 WHERE id = '{$ad['id']}'");
                    }
                }
                $tpl->load_template('ads/view_ajax/'.$temp.'_'.$ad['type'].'.tpl');
                $tpl->set('{url}', $ad['link']);
                $tpl->set('{image}', $ad['image']);
                $tpl->set('{name}', $ad['text']);
                $tpl->set('{descr}', $ad['description']);
                $tpl->set('{click}', "ads.clickgo('{$ad['id']}');");
                $tpl->compile('content');
            }

        }


    }
}
