<?php

declare(strict_types=1);

namespace App\Modules;

use Sura\Libs\Request;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;

class Fast_searchController extends Module{
	
	/**
	 * Быстрый поиск
	 *
	 * @return int
	 * @throws \JsonException
	 */
    public function index(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
//        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
//            $user_id = $user_info['user_id'];

            $limit_sql = 7;

            $query = $db->safesql(Validation::ajax_utf8(Validation::strip_data($request['query'])));
            $query = strtr($query, array(' ' => '%')); //Замеянем пробелы на проценты чтоб тоиск был точнее
            $type = (int)$request['se_type'];

            if(isset($query) AND !empty($query)){

                //Если критерий поиск "по людям"
                if($type == 1)
                    $sql_query = "SELECT user_id, user_search_pref, user_photo, user_birthday, user_country_city_name FROM `users` WHERE user_search_pref LIKE '%".$query."%' AND user_delet = '0' AND user_ban = '0' ORDER by `user_photo` DESC, `user_country_city_name` DESC LIMIT 0, ".$limit_sql;

                //Если критерий поиск "по видеозаписям"
                else if($type == 2)
                    $sql_query = "SELECT id, photo, title, add_date, owner_user_id FROM `videos` WHERE title LIKE '%".$query."%' AND privacy = 1 ORDER by `views` DESC LIMIT 0, ".$limit_sql;

                //Если критерий поиск "по сообществам"
                else if($type == 4)
                    $sql_query = "SELECT id, title, photo, traf, adres FROM `communities` WHERE title LIKE '%".$query."%' AND del = '0' AND ban = '0' ORDER by `traf` DESC, `photo` DESC LIMIT 0, ".$limit_sql;
                else
                    $sql_query = false;

                if($sql_query){
                    $sql_ = $db->super_query($sql_query, 1);
                    if($sql_){
                        foreach($sql_ as $key => $row){
                            $sql_[$key]['id'] = $key + 1;
                            //Если критерий поиск "по видеозаписям"
                            if($type == 2){
                                $ava = $row['photo'];
                                $img_width = 100;
                                $row['user_search_pref'] = $row['title'];
                                $country = 'Добавлено '.\Sura\Time\Date::megaDate(strtotime($row['add_date']), 1, 1);
                                $row['user_id'] = 'video'.$row['owner_user_id'].'_'.$row['id'].'" onClick="videos.show('.$row['id'].', this.href, location.href); return false';
                                $city = '';
                                //Если критерий поиск "по сообществам"
                            } else if($type == 4){
                                if($row['photo'])
                                    $ava = '/uploads/groups/'.$row['id'].'/50_'.$row['photo'];
                                else
                                    $ava = '/images/no_ava_50.png';

                                $img_width = 50;
                                $row['user_search_pref'] = $row['title'];
                                $titles = array('участник', 'участника', 'участников');//groups_users
                                $country = $row['traf'].' '.Gramatic::declOfNum($row['traf'], $titles);

                                if($row['adres'])
                                    $sql_[$key]['user_id'] = $row['adres'];
                                else
                                    $sql_[$key]['user_id'] = 'public'.$row['id'];

                                $city = '';
                                //Если критерий поиск "по людям"
                            } else {
                                //АВА
                                if($row['user_photo'])
                                    $ava = '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo'];
                                else
                                    $ava = '/images/no_ava_50.png';

                                $img_width = 50;

                                //Страна город
                                $expCountry = explode('|', $row['user_country_city_name']);
                                if($expCountry[0])
                                    $country = $expCountry[0];
                                else
                                    $country = '';
                                if($expCountry[1])
                                    $city = ', '.$expCountry[1];
                                else
                                    $city = '';

                                //Возраст юзера
                                $user_birthday = explode('-', $row['user_birthday']);
                                $sql_[$key]['age'] = \App\Libs\Profile::user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]);

//                                $img_width = '';

                                $sql_[$key]['user_id'] = 'u'.$row['user_id'];
                            }

                            $sql_[$key]['ava'] = $ava;
                            $sql_[$key]['country'] = $country;
                            $sql_[$key]['city'] = $city;
                            $sql_[$key]['img_width'] = $img_width;

//                            echo <<<HTML
//                            <a href="/{$row['user_id']}" onClick="Page.Go(this.href); return false;" onMouseOver="FSE.ClrHovered(this.id)" id="all_fast_res_clr{$sql_[$key]['id']}">
//                            <img src="{$ava}" width="{$img_width}" id="fast_img"  alt=""/>
//                            <div id="fast_name">{$row['user_search_pref']}</div>
//                            <div><span>{$countr}{$city}</span></div><span>{$age}</span><div class="clear"></div></a>
//                            HTML;
                        }

                        $params['search'] = $sql_;
                        return view('search.fast', $params);
                    }else{
	                    $status = Status::NOT_FOUND;
                    }
                }else{
	                $status = Status::NOT_FOUND;
                }
            }else{
	            $status = Status::NOT_DATA;
            }
        } else
        {
	        $status = Status::BAD_LOGGED;
        }
        //FIXME response
	    return _e_json(array(
		    'status' => $status,
	    ) );
    }
}