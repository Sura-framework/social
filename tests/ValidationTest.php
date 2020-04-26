<?php

namespace System\Libs;

use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{

    public function testStrip_data()
    {
        $text = 'foo';

        $instance = Validation::strip_data($text);

        $this->assertEquals('foo', $instance);

        return $instance;
    }

    public function testReplace_rn()
    {
        $text = "foo\r";

        $instance = Validation::replace_rn($text);

        $this->assertEquals('foo', $instance);

        return $instance;
    }

    public function testMyBr()
    {
        $text = "foo\r";

        $instance = Validation::myBr($text);

        $this->assertEquals('foo<br />', $instance);

        return $instance;
    }

    public function testAjax_utf8()
    {
        $text = 'foo';

        $instance = Validation::ajax_utf8($text);

        $this->assertEquals('foo', $instance);

        return $instance;
    }

    public function testRn_replace()
    {
        $text = "foo\r";

        $instance = Validation::rn_replace($text);

        $this->assertEquals('foo', $instance);

        return $instance;
    }

    public function testMyBrRn()
    {
        $text = "foo<br />";

        $instance = Validation::myBrRn($text);

        $this->assertEquals('foo
', $instance);

        return $instance;
    }

    public function testTextFilter()
    {
        $text = 'foo';

        $instance = Validation::textFilter($text, 25000, false);

        $this->assertEquals('foo', $instance);

        return $instance;
    }

    public function test_strlen()
    {
        $text = 'foo';

        $instance = Validation::_strlen($text, $charset = "utf-8");

        $this->assertEquals('3', $instance);

        return $instance;
    }

    public function testCheck_ip()
    {
        $this->assertTrue(true);
    }

    public function testWord_filter()
    {
        $source = 'foo';

        $instance = Validation::word_filter($source, $encode = true);

        $this->assertEquals('foo', $instance);

        return $instance;
    }

    public function testcheck_name()
    {

        //Проверка имени
        $instance = Validation::check_name('Иван');

        $this->assertEquals('Иван', $instance);

        return $instance;

    }

    public function testcheck_email()
    {

        //Проверка имени
        $instance = Validation::check_email('example@example.com');

        $this->assertEquals('example@example.com', $instance);

        return $instance;

    }

    public function testcheck_password()
    {
        //Проверка имени
        //don`t use password qwerty10
        $instance = Validation::check_password('qwerty10', 'qwerty10');

        $this->assertEquals('qwerty10', $instance);

        return $instance;

    }

    public function testcheck_password2()
    {
        $password1 = Validation::check_password('rasmuslerdorf', 'rasmuslerdorf');
        $pass_hash1 = password_hash($password1, PASSWORD_DEFAULT);

        //Проверка имени
        //don`t use password qwerty10
        $password2 = Validation::check_password('rasmuslerdorf', 'rasmuslerdorf');
        $pass_hash2 = password_hash($password2, PASSWORD_DEFAULT);

        $instance = password_verify('rasmuslerdorf', $pass_hash2);
        $this->assertEquals(true, $instance);

        return $instance;

    }

    public function testcheck_password3()
    {
        // Смотрите пример использования password_hash(), для понимания откуда это взялось.
        $hash = '$2y$10$rkadTE2AWb2CwFK/0J9fbetb4IWTVdgTibsnM4UaFr0D5pl0za2ci';
        $instance = password_verify('Hec2GugBed', $hash);
        if ($instance) {
            echo 'Пароль правильный!';
        } else {
            echo 'Пароль неправильный.';
        }
        $this->assertEquals(true, $instance);
    }

    //old
    public function testcheck_password4()
    {

        function langdate($format, $stamp){
            $langdate = Langs::get_langdate();
            return strtr(date($format, $stamp), $langdate);
        }

        function megaDate($date, $func = false, $full = false){
            $server_time = intval($_SERVER['REQUEST_TIME']);

            if(date('Y-m-d', $date) == date('Y-m-d', $server_time))
                return $date = langdate('сегодня в H:i', $date);
            elseif(date('Y-m-d', $date) == date('Y-m-d', ($server_time-84600)))
                return $date = langdate('вчера в H:i', $date);
            else
                if($func == 'no_year')
                    return $date = langdate('j M в H:i', $date);
                else
                    if($full)
                        return $date = langdate('j F Y в H:i', $date);
                    else
                        return $date = langdate('j M Y в H:i', $date);
        }


        $str_date = time();

        $date = megaDate(strtotime($str_date));

        $this->assertEquals('', $date);


    }
}
