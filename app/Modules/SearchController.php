<?php

namespace App\Modules;

use App\Services\Cache;
use Sura\Libs\Langs;
use Sura\Libs\Page;
use Sura\Libs\Registry;
use Sura\Libs\Settings;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;

class SearchController extends Module{

    /**
     * @param $params
     * @return string
     * @throws \Exception
     */
    public function index($params){
//        Tools::NoAjaxRedirect();

        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $config = Settings::loadsettings();

        if($logged){
            $params['title'] = $lang['search'].' | Sura';

            $_SERVER['QUERY_STRING'] = strip_tags($_SERVER['QUERY_STRING']);
            $query_string = preg_replace("/&page=[0-9]+/i", '', $_SERVER['QUERY_STRING']);
            $user_id = $user_info['user_id'];

            if(isset($_GET['page']) AND $_GET['page'] > 0)
                $page = intval($_GET['page']);
            else
                $page = 1;
            $gcount = 20;
            $limit_page =($page-1)*$gcount;

            if (isset($_GET['query'])){
//                $query = $db->safesql(Validation::ajax_utf8(Validation::strip_data(urldecode($_GET['query']))));
                $query = Validation::strip_data(urldecode($_GET['query']));
                if(isset($_GET['n']) AND $_GET['n'])
                    $query = Validation::strip_data(urldecode($_GET['query']));
                //Замеянем пробелы на проценты чтоб тоиск был точнее
                $query = strtr($query, array(' ' => '%'));
            }else
                $query = false;


            if (isset($_GET['type'])){
                $type = intval($_GET['type']);
            }else{
                $type = 1;
            }

            //Задаём параметры сортировки
            $sql_sort = '';
            if(isset($_GET['sex'])) {
                $sex = intval($_GET['sex']);
                $sql_sort .= "AND user_sex = '{$sex}'";
            }else{
                $sex = '';
            }

            if(isset($_GET['day'])) {
                $day = intval($_GET['day']);
                $sql_sort .= "AND user_day = '{$day}'";
            }else{
                $day = '';
            }

            if(isset($_GET['month'])) {
                $month = intval($_GET['month']);
                $sql_sort .= "AND user_month = '{$month}'";
            }else{
                $month = '';
            }

            if(isset($_GET['year'])) {
                $year = intval($_GET['year']);
                $sql_sort .= "AND user_year = '{$year}'";
            }else{
                $year = '';
            }

            if(isset($_GET['country'])) {
                $country = intval($_GET['country']);
                $sql_sort .= "AND user_country = '{$country}'";
            }else{
                $country = 0;
            }

            if(isset($_GET['city'])) {
                $city = intval($_GET['city']);
                $sql_sort .= "AND user_city = '{$city}'";
            }else{
                $city = '';
            }

            if(isset($_GET['online'])) {
                $online = intval($_GET['online']);
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $online_time = $server_time - 60;
                $sql_sort .= "AND user_last_visit >= '{$online_time}'";
            }else{
                $online = '';
            }

            if(isset($_GET['user_photo'])) {
                $user_photo = intval($_GET['user_photo']);
                $sql_sort .= "AND user_photo != ''";
            }else
                $user_photo = '';

            if(isset($_GET['sp'])) {
                $sp = intval($_GET['sp']);
                $sql_sort .= "AND SUBSTRING(user_sp, 1, 1) regexp '[[:<:]]({$sp})[[:>:]]'";
            }else{
                $sp = '';
            }

            $where_sql_gen = null;//bug: undefined

            if($query OR $sql_sort)
                $where_sql_gen = "WHERE user_search_pref LIKE '%{$query}%' AND user_delet = '0' AND user_ban = '0'";

            if(!$where_sql_gen)
                $where_sql_gen = "WHERE user_delet = '0' AND user_ban = '0'";

            //Делаем SQL Запрос в БД на вывод данных
            if($type == 1){ //Если критерий поиск "по людям"
                $sql_query = "SELECT user_id, user_search_pref, user_photo, user_birthday, user_country_city_name, user_last_visit, user_logged_mobile FROM `users` {$where_sql_gen} {$sql_sort} ORDER by `user_rating` DESC LIMIT {$limit_page}, {$gcount}";
                $sql_count = "SELECT COUNT(*) AS cnt FROM `users` {$where_sql_gen} {$sql_sort}";
            } elseif($type == 2 AND $config['video_mod'] == 'yes' AND $config['video_mod_search'] == 'yes'){ //Если критерий поиск "по видеозаписям"
                $sql_query = "SELECT id, photo, title, add_date, comm_num, owner_user_id FROM `videos` WHERE title LIKE '%{$query}%' AND privacy = 1 ORDER by `add_date` DESC LIMIT {$limit_page}, {$gcount}";
                $sql_count = "SELECT COUNT(*) AS cnt FROM `videos` WHERE title LIKE '%{$query}%' AND privacy = 1";
            } elseif($type == 3){ //Если критерий поиск "по заметкам"
                $sql_query = "SELECT notes.id, title, full_text, owner_user_id, date, comm_num, users.user_photo, user_search_pref FROM notes LEFT JOIN users ON notes.owner_user_id = users.user_id WHERE title LIKE '%{$query}%' OR full_text LIKE '%{$query}%' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}";
                $sql_count = "SELECT COUNT(*) AS cnt FROM `notes` WHERE title LIKE '%{$query}%' OR full_text LIKE '%{$query}%'";
            } elseif($type == 4){ //Если критерий поиск "по сообщества"
                $sql_query = "SELECT id, title, photo, traf, adres FROM `communities` WHERE title LIKE '%{$query}%' AND del = '0' AND ban = '0' ORDER by `traf` DESC, `photo` DESC LIMIT {$limit_page}, {$gcount}";
                $sql_count = "SELECT COUNT(*) AS cnt FROM `communities` WHERE title LIKE '%{$query}%' AND del = '0' AND ban = '0'";
            } elseif($type == 5 AND $config['audio_mod'] == 'yes' AND $config['audio_mod_search'] == 'yes'){ //Если критерий поиск "по аудиозаписи"
                $sql_query = "SELECT audio.id, url, artist, title, oid, duration,users.user_search_pref FROM audio LEFT JOIN users ON audio.oid = users.user_id WHERE MATCH (title, artist) AGAINST ('%{$query}%') OR artist LIKE '%{$query}%' OR title LIKE '%{$query}%' ORDER by `add_count` DESC LIMIT {$limit_page}, {$gcount}";
                $sql_count = "SELECT COUNT(*) AS cnt FROM `audio` WHERE MATCH (title, artist) AGAINST ('%{$query}%') OR artist LIKE '%{$query}%' OR title LIKE '%{$query}%'";
            } else {
                $sql_query = false;
                $sql_count = false;
            }

            if($sql_query){
                $sql_ = $db->super_query($sql_query, 1);
                if ($sql_){
                    $count = $db->super_query($sql_count);
                }
            }else{
                $sql_ = null;
                $count = null;
            }

            //Head поиска
//            $tpl->load_template('search/head.tpl');

            if($query)
                $params['query_search'] = stripslashes(stripslashes(strtr($query, array('%' => ' '))));
            else
                $params['query_search'] = '';


//            $tpl->set('{query-people}', );
            $params['query_people'] = str_replace(array('&type=2', '&type=3', '&type=4', '&type=5'), '&type=1', $query);
            $params['query_videos'] = str_replace(array('&type=1', '&type=3', '&type=4', '&type=5'), '&type=2', $query);
            $params['query_groups'] = str_replace(array('&type=1', '&type=2', '&type=4', '&type=5'), '&type=4', $query);
            $params['query_audios'] = str_replace(array('&type=1', '&type=2', '&type=3', '&type=4'), '&type=5', $query);
//            $tpl->set('{query-videos}', );
//            $params['query_videos'] = '&type=2&query='.$query;
//            $tpl->set('{query-groups}', );
//            $params['query_groups'] = '&type=4&query='.$query;
//            $tpl->set('{query-audios}', );
//            $params['query_audios'] = '&type=5&query='.$query;

            if($online)
                $params['checked_online'] = 'checked';
            else
                $params['checked_online'] = '';

            if($user_photo)
                $params['checked_user_photo'] = 'checked';
            else
                $params['checked_user_photo'] = '';

//            $tpl->set("{activetab-{$type}}", 'buttonsprofileSec');
//            $tpl->set("{type}", );
                $params['type'] = $type;

//            $tpl->set('{sex}', );
                $params['sex'] = installationSelected($sex, '<option value="1">Мужской</option><option value="2">Женский</option>');
//            $tpl->set('{day}', );
                $params['day'] = installationSelected($day, '<option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>');
//            $tpl->set('{month}', );
                $params['month'] = installationSelected($month, '<option value="1">Января</option><option value="2">Февраля</option><option value="3">Марта</option><option value="4">Апреля</option><option value="5">Мая</option><option value="6">Июня</option><option value="7">Июля</option><option value="8">Августа</option><option value="9">Сентября</option><option value="10">Октября</option><option value="11">Ноября</option><option value="12">Декабря</option>');
//            $tpl->set('{year}', );
                $params['year'] = installationSelected($year, '<option value="1930">1930</option><option value="1931">1931</option><option value="1932">1932</option><option value="1933">1933</option><option value="1934">1934</option><option value="1935">1935</option><option value="1936">1936</option><option value="1937">1937</option><option value="1938">1938</option><option value="1939">1939</option><option value="1940">1940</option><option value="1941">1941</option><option value="1942">1942</option><option value="1943">1943</option><option value="1944">1944</option><option value="1945">1945</option><option value="1946">1946</option><option value="1947">1947</option><option value="1948">1948</option><option value="1949">1949</option><option value="1950">1950</option><option value="1951">1951</option><option value="1952">1952</option><option value="1953">1953</option><option value="1954">1954</option><option value="1955">1955</option><option value="1956">1956</option><option value="1957">1957</option><option value="1958">1958</option><option value="1959">1959</option><option value="1960">1960</option><option value="1961">1961</option><option value="1962">1962</option><option value="1963">1963</option><option value="1964">1964</option><option value="1965">1965</option><option value="1966">1966</option><option value="1967">1967</option><option value="1968">1968</option><option value="1969">1969</option><option value="1970">1970</option><option value="1971">1971</option><option value="1972">1972</option><option value="1973">1973</option><option value="1974">1974</option><option value="1975">1975</option><option value="1976">1976</option><option value="1977">1977</option><option value="1978">1978</option><option value="1979">1979</option><option value="1980">1980</option><option value="1981">1981</option><option value="1982">1982</option><option value="1983">1983</option><option value="1984">1984</option><option value="1985">1985</option><option value="1986">1986</option><option value="1987">1987</option><option value="1988">1988</option><option value="1989">1989</option><option value="1990">1990</option><option value="1991">1991</option><option value="1992">1992</option><option value="1993">1993</option><option value="1994">1994</option><option value="1995">1995</option><option value="1996">1996</option><option value="1997">1997</option><option value="1998">1998</option><option value="1999">1999</option><option value="2000">2000</option><option value="2001">2001</option><option value="2002">2002</option><option value="2003">2003</option><option value="2004">2004</option><option value="2005">2005</option><option value="2006">2006</option><option value="2007">2007</option>');

            if(isset($count['cnt'])){
//                $tpl->set('[yes]', '');
//                $tpl->set('[/yes]', '');
//                $params['yes'] = true;

                // FOR MOBILE VERSION 1.0
                if($online == 1){
//                    $tpl->set_block("'\\[no-online\\](.*?)\\[/no-online\\]'si","");
                    $params['no_online'] = false;
//                    $tpl->set('[online]', '');
//                    $tpl->set('[/online]', '');
                    $params['online'] = true;
                } else {
//                    $tpl->set_block("'\\[online\\](.*?)\\[/online\\]'si","");
                    $params['online'] = false;
//                    $tpl->set('[no-online]', '');
//                    $tpl->set('[/no-online]', '');
                    $params['no_online'] = false;
                }

                if($type == 1){//Если критерий поиск "по людям"
                    $titles = array('человек', 'человека', 'человек');//fave
//                    $tpl->set('{count}', );
                    $params['count'] = $count['cnt'].' '.Gramatic::declOfNum($count['cnt'], $titles);
                }elseif($type == 2 AND $config['video_mod'] == 'yes'){//Если критерий поиск "по видеозаписям"
                    $titles = array('видеозапись', 'видеозаписи', 'видеозаписей');//videos
//                    $tpl->set('{count}', );
                    $params['count'] = $count['cnt'].' '.Gramatic::declOfNum($count['cnt'], $titles);
                }elseif($type == 3){ //Если критерий поиск "по заметкам"
                    $titles = array('заметка', 'заметки', 'заметок');//notes
//                    $tpl->set('{count}', );
                    $params['count'] = $count['cnt'].' '.Gramatic::declOfNum($count['cnt'], $titles);
                }elseif($type == 4){ //Если критерий поиск "по сообществам"
                    $titles = array('сообщество', 'сообщества', 'сообществ');//se_groups
//                    $tpl->set('{count}', );
                    $params['count'] = $count['cnt'].' '.Gramatic::declOfNum($count['cnt'], $titles);
                }elseif($type == 5){ //Если критерий поиск "по аудиозаписям"
                    $titles = array('песня', 'песни', 'песен');//audio
//                    $tpl->set('{count}', );
                    $params['count'] = $count['cnt'].' '.Gramatic::declOfNum($count['cnt'], $titles);
                }
            } else
//                $tpl->set_block("'\\[yes\\](.*?)\\[/yes\\]'si","");
//                $params['yes'] = false;

            if($type == 1){
//                $tpl->set('[search-tab]', '');
//                $tpl->set('[/search-tab]', '');
                $params['search_tab'] = true;
            } else
//                $tpl->set_block("'\\[search-tab\\](.*?)\\[/search-tab\\]'si","");
                $params['search_tab'] = false;

                    /**
                     * Загружаем Страны
                     */
            $Cache = Cache::initialize();
            try {
                $item = $Cache->get("system/all_country", $default = null);
                $item = unserialize($item);
            }catch (\Exception $e){
                $item = $db->super_query("SELECT * FROM `country` ORDER by `name` ASC", true);
                $value = serialize($item);
                $Cache->set("system/all_country", $value);
            }
            $all_country = '';
            foreach($item as $row_country){
                $all_country .= '<option value="'.$row_country['id'].'">'.stripslashes($row_country['name']).'</option>';
            }

//            $tpl->set('{country}', );
                $params['country'] = installationSelected($country, $all_country);

            //################## Загружаем Города ##################//
            if($type == 1){
//                $key = "system/all_city_".$country;
                $Cache2 = Cache::initialize();
                try {
                    $item = $Cache2->get("system/all_city_".$country, $default = null);
                    $item = unserialize($item);
                }catch (\Exception $e){
                    $item = $db->super_query("SELECT id, name FROM `city` WHERE id_country = '{$country}' ORDER by `name` ASC", true);
                    $value = serialize($item);
//                    $Cache2->set($key, $value);
                }
                $all_city = '';
                foreach($item as $row2)
                    $all_city .= '<option value="'.$row2['id'].'">'.stripslashes($row2['name']).'</option>';

//                $tpl->set('{city}', );
                $params['city'] = installationSelected($city, $all_city);
            }

//            $tpl->compile('info');

            //Загружаем шаблон на вывод если он есть одного юзера и выводим
            if($sql_query AND $sql_){

                //Если критерий поиск "по людям"
                if($type == 1){
//                    $tpl->load_template('search/result_people.tpl');
                    foreach($sql_ as $key => $row){
//                        $tpl->set('{user-id}', );
                        $sql_[$key]['user_id'] = $row['user_id'];
//                        $tpl->set('{name}', );
                        $sql_[$key]['name'] = $row['user_search_pref'];
                        if($row['user_photo'])
//                            $tpl->set('{ava}', );
                            $sql_[$key]['ava'] = $config['home_url'].'uploads/users/'.$row['user_id'].'/100_'.$row['user_photo'];
                        else
//                            $tpl->set('{ava}', );
                            $sql_[$key]['ava'] = '/images/100_no_ava.png';
                                //Возраст юзера
                        $user_birthday = explode('-', $row['user_birthday']);
//                        $tpl->set('{age}', );
                        $sql_[$key]['age'] = user_age($user_birthday['0'], $user_birthday['1'], $user_birthday['2']);

                        $user_country_city_name = explode('|', $row['user_country_city_name']);
//                        $tpl->set('{country}', );
                        $sql_[$key]['country'] = $user_country_city_name['0'];
                        if(isset($user_country_city_name['1']))
//                            $tpl->set('{city}', );
                            $sql_[$key]['city'] = ', '.$user_country_city_name['1'];
                        else
//                            $tpl->set('{city}', '');
                            $sql_[$key]['city'] = '';

                        if($row['user_id'] != $user_id){
//                            $tpl->set('[owner]', '');
//                            $tpl->set('[/owner]', '');
                            $sql_[$key]['owner'] = true;
                        } else
//                            $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                            $sql_[$key]['owner'] = false;

                            $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
//                        $tpl->set('{online}', );
                        $online = Tools::Online($row['user_last_visit']);
                        if ($online){
                            $sql_[$key]['online'] = $lang['online'];
                            $sql_[$key]['ava_online'] = 'avatar-online';
                        }else{
//                            $sql_[$key]['ava_online'] = 'avatar-offline';
                            $sql_[$key]['ava_online'] = '';
                            $sql_[$key]['online'] = '';
                        }
//                        if($row['user_id'] == 7)
////                            $tpl->set('{group}', '<span color="#f87d7d">Модератор</span>');
//                            $params['group'] =
//                        else
//// $tpl->set('{group}', '');
//                            $params['group'] =

//                        $tpl->compile('content');
                    }

                    //Если критерий поиск "по видеозаписям"
                }
                elseif($type == 2){
//                    $tpl->load_template('search/result_video.tpl');
                    foreach($sql_ as $key => $row){
//                        $tpl->set('{photo}', );
                        $sql_[$key]['photo'] = $row['photo'];
//                        $tpl->set('{title}', );
                        $sql_[$key]['title'] = stripslashes($row['title']);
//                        $tpl->set('{user-id}', );
                        $sql_[$key]['user_id'] = $row['owner_user_id'];
//                        $tpl->set('{id}', );
                        $sql_[$key]['id'] = $row['id'];
//                        $tpl->set('{close-link}', );
                        $sql_[$key]['close_link'] = '/index.php?'.$query_string.'&page='.$page;
                        $titles = array('комментарий', 'комментария', 'комментариев');//comments
//                        $tpl->set('{comm}', );
                        $sql_[$key]['comm'] = $row['comm_num'].' '.Gramatic::declOfNum($row['comm_num'], $titles);

                        $date = megaDate(strtotime($row['add_date']), 1, 1);
//                        $tpl->set('{date}', );
                        $sql_[$key]['date'] = $date;
//                        $tpl->compile('content');
                    }

                }
                /**
                 * Deprecated
                 */
                //Если критерий поиск "по заметкам"
                elseif($type == 3){
//                    $tpl->load_template('search/result_note.tpl');
//                    foreach($sql_ as $row){
//                        if($row['user_photo'])
////                            $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['owner_user_id'].'/50_'.$row['user_photo']);
//                            $params['ava'] =
//                        else
////                            $tpl->set('{ava}', '/images/no_ava_50.png');
//                            $params['ava'] =
//
////                        $tpl->set('{user-id}', $row['owner_user_id']);
//                            $params['user_id'] =
////                        $tpl->set('{short-text}', stripslashes(strip_tags(iconv_substr($row['full_text'], 0, 200, 'utf-8'))).'...');
//                            $params['short_text'] =
////                        $tpl->set('{title}', stripslashes($row['title']));
//                            $params['title'] =
////                        $tpl->set('{name}', $row['user_search_pref']);
//                            $params['name'] =
////                        $tpl->set('{note-id}', $row['id']);
//                            $params['note_id'] =
//
//                            $date = megaDate(strtotime($row['date']), 1, 1);
////                        $tpl->set('{date}', $date);
//                        $params['date'] =
//                        if($row['comm_num']){
//                            $titles = array('комментарий', 'комментария', 'комментариев');//comments
////                            $tpl->set('{comm-num}', $row['comm_num'].' '.Gramatic::declOfNum($row['comm_num'], $titles));
//                            $params['comm_num'] =
//                        }else
////                            $tpl->set('{comm-num}', $lang['note_no_comments']);
//                            $params['comm_num'] =
////                        $tpl->compile('content');
//                    }

                    //Если критерий поиск "по сообещствам"
                }
                elseif($type == 4){
//                    $tpl->load_template('search/result_groups.tpl');
                    foreach($sql_ as $key => $row){
                        if($row['photo'])
//                            $tpl->set('{ava}', );
                            $sql_[$key]['ava'] ='/uploads/groups/'.$row['id'].'/100_'.$row['photo'];
                        else
//                            $tpl->set('{ava}', );
                            $sql_[$key]['ava'] = '/images/no_ava_groups_100.gif';

//                        $tpl->set('{public-id}', );
                        $sql_[$key]['public_id'] = $row['id'];
//                        $tpl->set('{name}', );
                        $sql_[$key]['name'] = stripslashes($row['title']);
////                        $tpl->set('{note-id}', $row['id']);
//                            $params['note_id'] =
                            $titles = array('участник', 'участника', 'участников');//groups_users
//                        $tpl->set('{traf}', );
                        $sql_[$key]['traf'] = $row['traf'].' '.Gramatic::declOfNum($row['traf'], $titles);
                        if($row['adres'])
//                            $tpl->set('{adres}', );
                            $sql_[$key]['adres'] = $row['adres'];
                        else
//                            $tpl->set('{adres}', );
                            $sql_[$key]['adres'] = 'public'.$row['id'];
//                        $tpl->compile('content');
                    }

                    //Если критерий поиск "по аудизаписям"
                }
                elseif($type == 5){
                    foreach($sql_ as $key => $row){
                        $stime = gmdate("i:s", $row['duration']);
                        if(!$row['artist'])
                            $sql_[$key]['artist'] = 'Неизвестный исполнитель';

                        if(!$row['title'])
                            $sql_[$key]['title'] = 'Без названия';
//                        $plname = 'search';
//                        $tpl->result['content'] .= <<<HTML
//                        <div class="audioPage audioElem search search_item"
//                        id="audio_{$row['id']}_{$row['oid']}_{$plname}"
//                        onclick="playNewAudio('{$row['id']}_{$row['oid']}_{$plname}', event);">
//                        <div class="area">
//                        <table cellspacing="0" cellpadding="0" width="100%">
//                        <tbody>
//                        <tr>
//                        <td>
//                        <div class="audioPlayBut new_play_btn"><div class="bl"><div class="figure"></div></div></div>
//                        <input type="hidden" value="{$row['url']},{$row['duration']},page"
//                        id="audio_url_{$row['id']}_{$row['oid']}_{$plname}">
//                        </td>
//                        <td class="info">
//                        <div class="audioNames"><b class="author" onclick="Page.Go('/?go=search&query=&type=5&q='+this.innerHTML);" id="artist">{$row['artist']}</b> – <span
//                        class="name" id="name">{$row['title']}</span> <div class="clear"></div></div>
//                        <div class="audioElTime" id="audio_time_{$row['id']}_{$row['oid']}_{$plname}">{$stime}</div>
//                        <div class="vk_audio_dl_btn cursor_pointer fl_l" href="{$row['url']}" style="
//                        position: absolute;
//                        right: 28px;
//                        top: 9px;
//                        display: none;
//                        " onclick="vkDownloadFile(this,'{$row['artist']} - {$row['title']} - kalibri.co.ua');
//                        cancelEvent(event);" onMouseOver="myhtml.title('{$row['id']}', 'Скачать песню', 'ddtrack_', 4)"
//                        id="ddtrack_{$row['id']}"></div>
//                        <div class="audioSettingsBut"><li class="icon-plus-6"
//                        onClick="gSearch.addAudio('{$row['id']}_{$row['oid']}_{$plname}')"
//                        onmouseover="showTooltip(this, {text: 'Добавить в мой список', shift: [6,5,0]});"
//                        id="no_play"></li><div class="clear"></div></div>
//                        </td>
//                        </tr>
//                        </tbody>
//                        </table>
//                        <div id="player{$row['id']}_{$row['oid']}_{$plname}" class="audioPlayer" border="0"
//                        cellpadding="0">
//                        <table cellspacing="0" cellpadding="0" width="100%">
//                        <tbody>
//                        <tr>
//                        <td style="width: 100%;">
//                        <div class="progressBar fl_l" style="width: 100%;" onclick="cancelEvent(event);"
//                        onmousedown="audio_player.progressDown(event, this);" id="no_play"
//                        onmousemove="audio_player.playerPrMove(event, this)"
//                        onmouseout="audio_player.playerPrOut()">
//                        <div class="audioTimesAP" id="main_timeView"><div
//                        class="audioTAP_strlka">100%</div></div>
//                        <div class="audioBGProgress"></div>
//                        <div class="audioLoadProgress"></div>
//                        <div class="audioPlayProgress" id="playerPlayLine"><div class="audioSlider"></div></div>
//                        </div>
//                        </td>
//                        <td>
//                        <div class="audioVolumeBar fl_l" onclick="cancelEvent(event);"
//                        onmousedown="audio_player.volumeDown(event, this);" id="no_play">
//                        <div class="audioTimesAP"><div class="audioTAP_strlka">100%</div></div>
//                        <div class="audioBGProgress"></div>
//                        <div class="audioPlayProgress" id="playerVolumeBar"><div class="audioSlider"></div></div>
//                        </div>
//                        </td>
//                        </tr>
//                        </tbody>
//                        </table>
//                        </div>
//                        </div>
//                        </div>
//                        HTML;
                    }

                }
                else {

                }
//                    msgbox('', $lang['search_none'], 'info_2');


                $params['search'] = $sql_;
                $params['navigation'] = Tools::navigation($gcount, $count['cnt'], '/search/?'.$query_string.'&page=');

//                $tpl = Tools::navigation($gcount, $count['cnt'], '/search/?'.$query_string.'&page=', $tpl);

//                $tpl->result['content'] .= '</div>
//
//<div class="col-2 d-none d-sm-none d-md-block  col-md-4 col-lg-2"></div>
//</div>';
            }

            return view('search.search', $params);
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }
}