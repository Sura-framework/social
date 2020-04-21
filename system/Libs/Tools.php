<?php

namespace System\Libs;

use System\Classes\Templates;

class Tools
{
	public static function set_cookie($name, $value, $expires) {
		if( $expires ) {
			$expires = time() + ($expires * 86400);
		} else {
			$expires = FALSE;
		}
		setcookie($name, $value, $expires, "/", DOMAIN, NULL, TRUE);
    }

	public static function NoAjaxQuery(){
		if(clean_url($_SERVER['HTTP_REFERER']) != clean_url($_SERVER['HTTP_HOST']) AND $_SERVER['REQUEST_METHOD'] != 'POST')
			header('Location: /index.php?go=none');
	}

    public static function GetVar($v){
        if(ini_get('magic_quotes_gpc'))
            return stripslashes($v) ;
        return $v;
    }

    public static function InstallationSelectedNew($id, $options){
        return str_replace('val="'.$id.'" class="', 'val="'.$id.'" class="active ', $options);
    }

    //deprecated
    public static function check_xss(){
        $url = html_entity_decode(urldecode($_SERVER['QUERY_STRING']));

        if($url){
            if((strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, '"' ) !== false) || (strpos( $url, './' ) !== false) || (strpos( $url, '../' ) !== false) || (strpos( $url, '\'' ) !== false) || (strpos( $url, '.php' ) !== false)){
                if($_GET['go'] != "search" AND $_GET['go'] != "messages")
                    die('Hacking attempt!');
            }
        }

        $url = html_entity_decode( urldecode( $_SERVER['REQUEST_URI'] ) );
        if($url){
            if((strpos($url, '<') !== false) || (strpos($url, '>') !== false) || (strpos($url, '"') !== false) || (strpos($url, '\'') !== false)){
                if($_GET['go'] != "search" AND $_GET['go'] != "messages")
                    die('Hacking attempt!');
            }
        }
    }

    public static function AjaxTpl($tpl){
//        global $tpl, $config;

//        $config = include __DIR__.'/../data/config.php';
        echo $tpl->result['info'].$tpl->result['content'];
    }

    public static function CheckBlackList($userId){
//        global $user_info;
        $user_info = Registry::get('user_info');


        $openMyList = Cache::mozg_cache("user_{$userId}/blacklist");

        if(stripos($openMyList, "|{$user_info['user_id']}|") !== false)
            return true;
        else
            return false;
    }
    public static function MyCheckBlackList($userId){
        $user_info = Registry::get('user_info');

        $openMyList = Cache::mozg_cache("user_{$user_info['user_id']}/blacklist");

        if(stripos($openMyList, "|{$userId}|") !== false)
            return true;
        else
            return false;
    }

    public static function CheckFriends($friendId){
        $user_info = Registry::get('user_info');

        $openMyList = Cache::mozg_cache("user_{$user_info['user_id']}/friends");

        if(stripos($openMyList, "u{$friendId}|") !== false)
            return true;
        else
            return false;
    }

    public static function navigation($gc, $num, $type, $tpl){
        if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//        $tpl = Registry::get('tpl');

        $gcount = $gc;
        $cnt = $num;
        $items_count = $cnt;
        $items_per_page = $gcount;
        $page_refers_per_page = 5;
        $pages = '';
        $pages_count = ( ( $items_count % $items_per_page != 0 ) ) ? floor( $items_count / $items_per_page ) + 1 : floor( $items_count / $items_per_page );
        $start_page = ( $page - $page_refers_per_page <= 0  ) ? 1 : $page - $page_refers_per_page + 1;
        $page_refers_per_page_count = ( ( $page - $page_refers_per_page < 0 ) ? $page : $page_refers_per_page ) + ( ( $page + $page_refers_per_page > $pages_count ) ? ( $pages_count - $page )  :  $page_refers_per_page - 1 );

        if($page > 1)
            $pages .= '<a href="'.$type.($page-1).'" onClick="Page.Go(this.href); return false">&laquo;</a>';
        else
            $pages .= '';

        if ( $start_page > 1 ) {
            $pages .= '<a href="'.$type.'1" onClick="Page.Go(this.href); return false">1</a>';
            $pages .= '<a href="'.$type.( $start_page - 1 ).'" onClick="Page.Go(this.href); return false">...</a>';

        }

        for ( $index = -1; ++$index <= $page_refers_per_page_count-1; ) {
            if ( $index + $start_page == $page )
                $pages .= '<span>' . ( $start_page + $index ) . '</span>';
            else
                $pages .= '<a href="'.$type.($start_page+$index).'" onClick="Page.Go(this.href); return false">'.($start_page+$index).'</a>';
        }

        if ( $page + $page_refers_per_page <= $pages_count ) {
            $pages .= '<a href="'.$type.( $start_page + $page_refers_per_page_count ).'" onClick="Page.Go(this.href); return false">...</a>';
            $pages .= '<a href="'.$type.$pages_count.'" onClick="Page.Go(this.href); return false">'.$pages_count.'</a>';
        }

        $resif = $cnt/$gcount;
        if(ceil($resif) == $page)
            $pages .= '';
        else
            $pages .= '<a href="'.$type.($page+1).'" onClick="Page.Go(this.href); return false">&raquo;</a>';

        if ( $pages_count <= 1 )
            $pages = '';

        $config = include __DIR__.'/../data/config.php';

        $tpl_2 = new Templates();
        $tpl_2->dir = __DIR__.'/../../templates/'.$config['temp'];;
        $tpl_2->load_template('nav.tpl');
        $tpl_2->set('{pages}', $pages);
        $tpl_2->compile('content');
        $tpl_2->clear();
        $tpl->result['content'] .= $tpl_2->result['content'];
        return $tpl;
    }

    public static function  box_navigation($gc, $num, $id, $function, $act, $tpl){
        global $page;


        $gcount = $gc;
        $cnt = $num;
        $items_count = $cnt;
        $items_per_page = $gcount;
        $page_refers_per_page = 5;
        $pages = '';
        $pages_count = ( ( $items_count % $items_per_page != 0 ) ) ? floor( $items_count / $items_per_page ) + 1 : floor( $items_count / $items_per_page );
        $start_page = ( $page - $page_refers_per_page <= 0  ) ? 1 : $page - $page_refers_per_page + 1;
        $page_refers_per_page_count = ( ( $page - $page_refers_per_page < 0 ) ? $page : $page_refers_per_page ) + ( ( $page + $page_refers_per_page > $pages_count ) ? ( $pages_count - $page )  :  $page_refers_per_page - 1 );

        if(!$act)
            $act = "''";
        else
            $act = "'{$act}'";

        if($page > 1)
            $pages .= '<a href="" onClick="'.$function.'('.$id.', '.($page-1).', '.$act.'); return false">&laquo;</a>';
        else
            $pages .= '';

        if ( $start_page > 1 ) {
            $pages .= '<a href="" onClick="'.$function.'('.$id.', 1, '.$act.'); return false">1</a>';
            $pages .= '<a href="" onClick="'.$function.'('.$id.', '.($start_page-1).', '.$act.'); return false">...</a>';

        }

        for ( $index = -1; ++$index <= $page_refers_per_page_count-1; ) {
            if ( $index + $start_page == $page )
                $pages .= '<span>' . ( $start_page + $index ) . '</span>';
            else
                $pages .= '<a href="" onClick="'.$function.'('.$id.', '.($start_page+$index).', '.$act.'); return false">'.($start_page+$index).'</a>';
        }

        if ( $page + $page_refers_per_page <= $pages_count ) {
            $pages .= '<a href="" onClick="'.$function.'('.$id.', '.($start_page + $page_refers_per_page_count).', '.$act.'); return false">...</a>';
            $pages .= '<a href="" onClick="'.$function.'('.$id.', '.$pages_count.', '.$act.'); return false">'.$pages_count.'</a>';
        }

        $resif = $cnt/$gcount;
        if(ceil($resif) == $page)
            $pages .= '';
        else
            $pages .= '<a href="/" onClick="'.$function.'('.$id.', '.($page+1).', '.$act.'); return false">&raquo;</a>';

        if ( $pages_count <= 1 )
            $pages = '';

        $config = include __DIR__.'/../data/config.php';

        $tpl_2 = new Templates();
        $tpl_2->dir = __DIR__.'/../../templates/'.$config['temp'];;
        $tpl_2->load_template('nav.tpl');
        $tpl_2->set('{pages}', $pages);
        $tpl_2->compile('content');
        $tpl_2->clear();
        $tpl->result['content'] .= $tpl_2->result['content'];
        return $tpl;
    }
}
