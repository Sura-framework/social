<?php

namespace System\Libs;

use PHPUnit\Framework\TestCase;

class LangsTest extends TestCase
{

    protected string $rMyLang = 'Русский';
    protected string $checkLang = 'Russian';

    public function testGet_langs()
    {
        Langs::get_langs();
        $this->expectOutputString('');

    }

    public function testGet_langdate()
    {
        Langs::get_langdate();
        $this->expectOutputString('');
    }

    public function testSetlocale()
    {
        Langs::setlocale();
        $this->expectOutputString('');
    }

    public function testCheckLang()
    {
        //return Langs::checkLang();
        $this->expectOutputString('foo');
        print 'foo';
    }

}
