<?php


namespace System\Libs;


class Request
{

    public $get = array();
    public $post = array();
    public $files = array();
    public $cookie = array();
    public $session = array();
    public $request;
    public $server = array();

    public $header = array();

    function setGlobal()
    {
        /**
         * 将HTTP头信息赋值给$_SERVER超全局变量
         */
        foreach ($this->header as $key => $value)
        {
            $_key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $this->server[$_key] = $value;
        }
        $_GET = $this->get;
        $_POST = $this->post;
        $_FILES = $this->files;
        $_COOKIE = $this->cookie;
        $_SERVER = $this->server;

        //$this->request = $_REQUEST = array_merge($this->get, $this->post, $this->cookie);
        $this->request = $_REQUEST = array_merge($this->get, $this->post, $this->cookie);
    }

    function getGlobal()
    {
        return $this->request;
    }

    /**
     * LAMP环境初始化
     */
    function initWithFastCGI()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookie = $_COOKIE;
        $this->server = $_SERVER;
        $this->request = $_REQUEST;
    }

    function unsetGlobal()
    {
        $_REQUEST = $_SESSION = $_COOKIE = $_FILES = $_POST = $_SERVER = $_GET = array();
    }

    function isWebSocket()
    {
        return isset($this->header['Upgrade']) && strtolower($this->header['Upgrade']) == 'websocket';
    }

    function getClientIP()
    {
        if (isset($this->server["HTTP_X_REAL_IP"]) and strcasecmp($this->server["HTTP_X_REAL_IP"], "unknown"))
        {
            return $this->server["HTTP_X_REAL_IP"];
        }
        if (isset($this->server["HTTP_CLIENT_IP"]) and strcasecmp($this->server["HTTP_CLIENT_IP"], "unknown"))
        {
            return $this->server["HTTP_CLIENT_IP"];
        }
        if (isset($this->server["HTTP_X_FORWARDED_FOR"]) and strcasecmp($this->server["HTTP_X_FORWARDED_FOR"], "unknown"))
        {
            return $this->server["HTTP_X_FORWARDED_FOR"];
        }
        if (isset($this->server["REMOTE_ADDR"]))
        {
            return $this->server["REMOTE_ADDR"];
        }
        return "";
    }






}