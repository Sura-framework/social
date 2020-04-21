<?php

namespace System\Libs;
//System\Libs\Validation::
use phpDocumentor\Reflection\Types\Integer;
use System\Classes\Db;

/**
 * Class Validation
 * @package System\Libs
 */
class Validation
{

    static $regx = array(
        //邮箱
        'email' => '/^[\w-\.]+@[\w-]+(\.(\w)+)*(\.(\w){2,4})$/',
        //手机号码
        'mobile' => '/^\d{11}$/',
        //固定电话带分机号
        'tel' => '/^((0\d{2,3})-)(\d{7,8})(-(\d{1,4}))?$/',
        //固定电话不带分机号
        'phone' => '/^\d{3}-?\d{8}|\d{4}-?\d{7}$/',
        //域名
        'domain' => '/@([0-9a-z-_]+.)+[0-9a-z-_]+$/i',
        //日期
        'date' => '/^[1-9][0-9][0-9][0-9]-[0-9]{1,2}-[0-9]{1,2}$/',
        //日期时间
        'datetime' => '/^[1-9][0-9][0-9][0-9]-[0-9]{1,2}-[0-9]{1,2} [0-9]{1,2}(:[0-9]{1,2}){1,2}$/',
        //时间
        'time' => '/^[0-9]{1,2}(:[0-9]{1,2}){1,2}$/',
        /*--------- 数字类型 --------------*/
        'int'=>'/^\d{1,11}$/', //十进制整数
        'hex'=>'/^0x[0-9a-f]+$/i', //16进制整数
        'bin'=>'/^[01]+$/', //二进制
        'oct' => '/^0[1-7]*[0-7]+$/', //8进制
        'float' => '/^\d+\.[0-9]+$/', //浮点型
        /*---------字符串类型 --------------*/
        //utf-8中文字符串
        'chinese' => '/^[\x{4e00}-\x{9fa5}]+$/u',
        /*---------常用类型 --------------*/
        'english' => '/^[a-z0-9_\.]+$/i', //英文
        'nickname' => '/^[\x{4e00}-\x{9fa5}a-z_\.]+$/ui', //昵称，可以带英文字符和数字
        'realname' => '/^[\x{4e00}-\x{9fa5}]+$/u', //真实姓名
        'password' => '/^[a-z0-9]{6,32}$/i', //密码
        'area' => '/^0\d{2,3}$/', //区号
        'version' => '/^\d+\.\d+\.\d+$/',       //版本号
        'url' => '((https?)://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)', //URL
    );

    /**
     * @param $regx
     * @param $input
     * @return bool|string
     */
    static function regx(string $regx, string $input)
    {
        $n = preg_match($regx, $input, $match);
        if ($n === 0)
        {
            return false;
        }
        else
        {
            return $match[0];
        }
    }

    /**
     * @param $ctype
     * @param $input
     * @return mixed
     */
    static function check($input, $ctype)
    {
        if (isset(self::$regx[$ctype]))
        {
            return self::regx(self::$regx[$ctype], $input);
        }
        else
        {
            return self::$ctype($input);
        }
    }

    /**
     * IPv4 or IPV6
     * @param string $ip
     * @return bool|string
     */
    static function check_ip(string $ip)
    {
        if (count ( explode ( ".", $ip ) ) == 4 OR filter_var( $ip , FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) OR strpos($ip, ":") !== false ){
            return $ip;
        }
        return false;
    }

    public static function check_name($user_name)
    {
        if (empty($user_name))
            return false;
        if (preg_match("/^[a-zA-Zа-яА-Я]+$/iu", $user_name) AND strlen($user_name) >= 2)
            return $user_name;
        else
            return false;

    }


    public static function check_password($password_first, $password_second)
    {
        if (empty($password_first) or empty($password_second))
            return false;
        if (strlen($password_first) >= 6 AND strlen($password_first) <= 72 AND $password_first == $password_second) {
            return $password_first;
        }else
            return false;
    }

    public static function check_email($email)
    {
        if (empty($email))
            return false;
        if(filter_var($email, FILTER_VALIDATE_EMAIL) )
        {
            if (empty( $email ) OR strlen( $email ) > 40 OR count(explode("@", $email)) !== 2){
                return false;
            }else
                return $email;
        }else
            return false;

    }

    /**
     * @param $text
     * @return string|string[]
     */
    public static function strip_data(string $text) :string
    {
		//$quotes = array ("\x27", "\x22", "\x60", "\t", "\n", "\r", "'", ",", "/", ";", ":", "@", "[", "]", "{", "}", "=", ")", "(", "*", "&", "^", "%", "$", "<", ">", "?", "!", '"' );
        $quotes = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " ", "&" );
		$goodquotes = array ("-", "+", "#" );
		$repquotes = array ("\-", "\+", "\#" );
		$text = stripslashes( $text );
		$text = trim( strip_tags( $text ) );
		$text = str_replace( $quotes, '', $text );
		$text = str_replace( $goodquotes, $repquotes, $text );
		return $text;
	}

    /**
     * @param $source
     * @return mixed
     */
    public static function ajax_utf8(string $source) :string
    {
		return $source;
	}


    /**
     * @param $source
     * @return string|string[]|null
     */
    public static function replace_rn(string $source) :string
    {
		
		$find[] = "'\r'";
		$replace[] = "";
		$find[] = "'\n'";
		$replace[] = "";
		
		$source = preg_replace($find, $replace, $source);
		
		return $source;
		
	}

    /**
     * @param $source
     * @return string|string[]|null
     */
    public static function myBr(string $source) :string
    {
		
		$find[] = "'\r'";
		$replace[] = "<br />";
		
		$find[] = "'\n'";
		$replace[] = "<br />";

		$source = preg_replace($find, $replace, $source);
		
		return $source;
		
	}

    /**
     * @param $source
     * @return string|string[]
     */
    public static function myBrRn(string $source) :string
    {

		$find[] = "<br />";
		$replace[] = "\r\n";
//		$find[] = "<br />";
//		$replace[] = "\n";
		
		$source = str_replace($find, $replace, $source);
		
		return $source;
	}

    /**
     * @param $source
     * @return string|string[]|null
     */
    public static function rn_replace(string $source) :string
    {
		
		$find[] = "'\r'";
		$replace[] = "";
		$find[] = "'\n'";
		$replace[] = "";
		
		$source = preg_replace($find, $replace, $source);
		
		return $source;
		
	}

    /**
     * @param string $source
     * @param int $substr_num
     * @param bool $strip_tags
     * @return string|string[]|null
     */
    public static function textFilter(string $source, int $substr_num = 25000, bool $strip_tags = false) :string
    {
		// global $db;
		//$db = Db::getDB();
//		 if(function_exists("get_magic_quotes_gpc") AND get_magic_quotes_gpc())
		 	$source = stripslashes($source);
		
		 $find = array('/data:/i', '/about:/i', '/vbscript:/i', '/onclick/i', '/onload/i', '/onunload/i', '/onabort/i', '/onerror/i', '/onblur/i', '/onchange/i', '/onfocus/i', '/onreset/i', '/onsubmit/i', '/ondblclick/i', '/onkeydown/i', '/onkeypress/i', '/onkeyup/i', '/onmousedown/i', '/onmouseup/i', '/onmouseover/i', '/onmouseout/i', '/onselect/i', '/javascript/i');
			
		 $replace = array("d&#097;ta:", "&#097;bout:", "vbscript<b></b>:", "&#111;nclick", "&#111;nload", "&#111;nunload", "&#111;nabort", "&#111;nerror", "&#111;nblur", "&#111;nchange", "&#111;nfocus", "&#111;nreset", "&#111;nsubmit", "&#111;ndblclick", "&#111;nkeydown", "&#111;nkeypress", "&#111;nkeyup", "&#111;nmousedown", "&#111;nmouseup", "&#111;nmouseover", "&#111;nmouseout", "&#111;nselect", "j&#097;vascript");

		 $source = preg_replace("#<iframe#i", "&lt;iframe", $source);
		 $source = preg_replace("#<script#i", "&lt;script", $source);
			
		 $source = self::myBr(htmlspecialchars(substr(trim($source), 0, $substr_num)));
		
		 $source = str_ireplace("{", "&#123;", $source);
		 $source = str_ireplace("`", "&#96;", $source);
		 $source = str_ireplace("{theme}", "&#123;theme}", $source);
		
		 $source = preg_replace($find, $replace, $source);
		
		 if($strip_tags)
		 	$source = strip_tags($source);

		return $source;
	}

    /**
     * @param $value
     * @param string $charset
     * @return false|int
     */
    public static function _strlen(string $value, string $charset = "utf-8" ) :int
    {

		if( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $value, $charset );
		} elseif( function_exists( 'iconv_strlen' ) ) {
			return iconv_strlen($value, $charset);
		}

		return strlen($value);
	}

    /**
     * @deprecated
     * @param $ips
     * @return bool|mixed
     */
    public static function check_ip_old(array $ips) :string
    {
		$_IP = $_SERVER['REMOTE_ADDR'];
		$blockip = '0.0.0.0';
        foreach($ips as $ip_line){
            $ip_arr = rtrim($ip_line['ip']);
            $ip_check_matches = 0;
            $db_ip_split = explode(".", $ip_arr);
            $this_ip_split = explode(".", $_IP);
            for($i_i = 0; $i_i < 4; $i_i ++){
                if($this_ip_split[$i_i] == $db_ip_split[$i_i] or $db_ip_split[$i_i] == '*'){
                    $ip_check_matches += 1;
                }
            }
            if($ip_check_matches == 4){
                $blockip = $ip_line['ip'];
                return $blockip;
            }
        }
		return $blockip;
	}

    /**
     * @param $source
     * @param bool $encode
     * @return string|string[]|null
     */
    public static function word_filter($source, $encode = true)  :string
    {
		$config = include __DIR__.'/../data/config.php';
			
		$safe_mode = false;
		
		if($encode){
				
			$all_words = file(__DIR__.'/../data/wordfilter.db.php');
			$find = array();
			$replace = array();
				
			if(!$all_words or !count($all_words)) return $source;
				
			foreach($all_words as $word_line){
				$word_arr = explode( "|", $word_line );
					
				if(function_exists("get_magic_quotes_gpc") AND get_magic_quotes_gpc()){
						
					$word_arr[1] = addslashes($word_arr[1]);
					
				}

				if($word_arr[4]){

					$register ="";

				} else $register ="i";

				if($config['charset'] == "utf-8") $register .= "u";

				$allow_find = true;

				if($word_arr[5] == 1 AND $safe_mode) $allow_find = false;
				if($word_arr[5] == 2 AND !$safe_mode) $allow_find = false;
					
				if($allow_find){

					if($word_arr[3]){
							
						$find_text = "#(^|\b|\s|\<br \/\>)".preg_quote($word_arr[1], "#")."(\b|\s|!|\?|\.|,|$)#".$register;
							
						if($word_arr[2] == "") $replace_text = "\\1";
						else $replace_text = "\\1<!--filter:".$word_arr[1]."-->".$word_arr[2]."<!--/filter-->\\2";
						
					} else {
							
						$find_text = "#(".preg_quote( $word_arr[1], "#" ).")#".$register;
							
						if($word_arr[2] == "") $replace_text = "";
						else $replace_text = $word_arr[2];
						
					}

					if($word_arr[6]){

						if(preg_match($find_text, $source)){

							return $source;

						}

					} else {

						$find[] = $find_text;
						$replace[] = $replace_text;
					}

				}

			}

			if(!count($find)) return $source;
				
			$source = preg_split('((>)|(<))', $source, - 1, PREG_SPLIT_DELIM_CAPTURE);
			$count = count($source);
				
			for($i = 0; $i < $count; $i ++){
				if($source[$i] == "<" or $source[$i] == "["){
					$i ++;
					continue;
				}
					
				if($source[$i] != "") $source[$i] = preg_replace($find, $replace, $source[$i]);
			}
				
			$source = join("", $source);
			
		} else {
				
			$source = preg_replace("#<!--filter:(.+?)-->(.+?)<!--/filter-->#", "\\1", $source);
			
		}
			
		return $source;
	}

    static function string($str)
    {
        return filter_var($str, FILTER_DEFAULT);
    }

    static function strlen_8bit($binary_string) {
        if (function_exists('mb_strlen')) {
            return mb_strlen($binary_string, '8bit');
        }
        return strlen($binary_string);
    }

    static function substr_8bit($binary_string, $start, $length) {
        if (function_exists('mb_substr')) {
            return mb_substr($binary_string, $start, $length, '8bit');
        }
        return substr($binary_string, $start, $length);
    }

}
