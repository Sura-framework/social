<?php
# language: ru

namespace System\Libs;

use InvalidArgumentException;
use System\Libs\Registry;


class Router
{
    private static $routes = [];

    private static $requestUri;

    private static $requestMethod;

    private static $requestHandler;

    private static $params = [];

    private static $placeholders = [
        ':seg' => '([^\/]+)',
        ':num'  => '([0-9]+)',
        ':any'  => '(.+)'
    ];

    private static  $controllerName;
    private static  $actionName;

    public function __construct($uri, $method = 'GET')
    {
        self::$requestUri = $uri;
        self::$requestMethod = $method;
    }

    /**
     * Factory method construct Router from global vars.
     * @return Router
     */
    public static function fromGlobals()
    {
        $config = include __DIR__.'/../data/config.php';
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        }elseif(!empty($config['home_url'])){
            $uri = $config['home_url'];
        }else{
            $uri = '';
            echo 'error: non url';
        }
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        return new static($uri, $_SERVER['REQUEST_METHOD']);
    }
    /**
     * Current .
     * @return array
     */
    public function getRoutes()
    {
        return self::$routes;
    }

    /**
     * Current processed URI.
     * @return string
     */
    public function getRequestUri()
    {
        return self::$requestUri; // ?: '/';
    }

  /**
   * Текущий обработанный URL
   */
//   public static function getCurrentUrl2() {
//     return (self::$requestedUrl?:'/');
//   }

    /**
     * Request method.
     * @return string
     */
    public function getRequestMethod()
    {
        return self::$requestMethod;
    }
    
    /**
     * Get Request handler.
     * @return string|callable
     */
    public function getRequestHandler()
    {
        return self::$requestHandler;
    }

    /**
     * Set Request handler.
     * @param $handler string|callable
     */
    public function setRequestHandler($handler)
    {
        self::$requestHandler = $handler;
    }

    /**
     * Request wildcard params.
     * @return array
     */
    public function getParams()
    {
        return self::$params; //old
    }

    /**
     * Request params.
     * @return array
     */
    public function getControllerName()
    {
        return self::$controllerName;
    }

    /**
     * Request params.
     * @return array
     */
    public  function getActionName()
    {
        return self::$actionName;
    }

    /**
     * Add route rule.
     *
     * Добавить маршрут
     *
     * @param string|array $route A URI route string or array
     * @param mixed $handler Any callable or string with controller classname and action method like "ControllerClass@actionMethod"
     * @return Router
     */
    public function add($route, $handler = null)
    {
        if ($handler !== null && !is_array($route)) {
            $route = array($route => $handler);
        }
        self::$routes = array_merge(self::$routes, $route);
        return $this;
    }

    /**
     * Process requested URI.
     * @return bool
     */
    public function isFound()
    {
        $uri = $this->getRequestUri();

        // if URI equals to route
        if (isset(self::$routes[$uri])) {
            self::$requestHandler = self::$routes[$uri];
            return true;
        }

        $find    = array_keys(self::$placeholders);
        $replace = array_values(self::$placeholders);
        foreach (self::$routes as $route => $handler) {
            // Replace wildcards by regex
            if (strpos($route, ':') !== false) {
                $route = str_replace($find, $replace, $route);
            }
            // Route rule matched
            if (preg_match('#^' . $route . '$#', $uri, $matches)) {
                self::$requestHandler = $handler;
                self::$params = array_slice($matches, 1);
                return true;
            }
        }

        return false;
    }
    /**
     * Execute Request Handler.
     * Запуск соответствующего действия/экшена/метода контроллера
     *
     * @param string|callable $handler
     * @param array $params
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function executeHandler($handler = null, $params = null)
    {
        if ($handler === null) {
             throw new InvalidArgumentException(
                 'Request handler not setted out. Please check '.__CLASS__.'::isFound() first'
             );
        }

        // execute action in callable
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        // execute action in controllers
        if (strpos($handler, '@')) {
            $ca = explode('@', $handler);
            self::$controllerName = $ca[0];
            $ModName = strtolower( str_replace('Controller', '', $ca[0]) );

//            Registry::set('ModName', $ModName); //main||manager||...

            $controllername = self::getController();
            $action = $ca[1];
            // self::$actionName = $ca[1];//delete
            $dir_name = ucfirst($ModName);

                if (class_exists('\\System\\Modules\\'.$controllername)) {
                    if (!method_exists('\\System\\Modules\\'.$controllername, $action)) {
                        // throw new RuntimeException("Method '{$controllerName}::{$action}' not found");
                        echo "Method \\System\\Modules\\{$controllername}::{$action} not found";
                    }else{
                        $class = 'System\Modules\\'.$controllername;
                        $foo = new $class();
                        return call_user_func_array(array($foo, $action), $params);
                        //return call_user_func_array(array('\\System\\Modules\\'.$controllername, $action), $params);
                    }
                }else  {
                        echo 'Method \\System\\Modules\\'.$controllername.'::'.$action.' not found';

                }
        }
    }

    public function getController()
    {
        $ctrlName = self::$controllerName;

        $mod = ucfirst(strtolower(str_replace('Controller', '', $ctrlName)));
        $mod=ucfirst($mod);

        return $mod.'Controller';
    }

     public function getAction($action)
     {
       # code...
     }

}
