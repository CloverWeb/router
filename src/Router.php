<?php

namespace Joking\Route;


use Joking\Route\Interfaces\RouteMiddlewareInterface;
use Joking\Route\Interfaces\Routing;

class Router {

    public function __construct($routeClass = '', $params = []) {
        empty($routeClass) || $this->routeClass = $routeClass;
        $this->routeClassParams = $params;
    }

    /**
     * @var Routing
     */
    protected $router;

    //一定要真实路径
    protected $cachePath;

    //需要传入routeClass的参数
    protected $routeClassParams = [];

    /**
     * 保存注册的中间件
     * @var array
     */
    protected static $middleware = [];

    /**
     * @var string
     */
    protected $routeClass = RouteDefault::class;

    public function dispatch($method, $url) {
        $router = $this->getRouteClass();

        try {
            $routeResult = $router->dispatch($method, $url);

            /**
             * 执行路由中间件
             * @var RouteMiddlewareInterface $middleware
             */
            foreach ($routeResult->middleware as $middlewareName) {
                if (is_string($middlewareName) && class_exists($middlewareName)) {
                    $middleware = new $middlewareName();
                } else {
                    $className = $this->getByName($middlewareName);
                    if ($className == null) {                   //如果没有找到已经注册的中间件，那么直接跳过
                        continue;
                    }
                    $middleware = new $className();
                }

                $middleware->handle($routeResult);
            }

            return $routeResult;
        } catch (RouteException $exception) {
            if ($errorHandle = $router->getErrorHandle()) {
                return new RouteEntity($url, $method, $errorHandle);
            }

            throw new RouteException($exception->getMessage(), $exception->getCode());
        }
    }

    public static function registerMiddleware($name, $middleware) {
        static::$middleware[$name] = $middleware;
    }

    /**
     * 查找已经注册的中间件
     * @param string $name 中间件的名称
     * @return string
     * @throws \Exception
     */
    protected function getByName($name) {
        if (static::$middleware[$name]) {
            return static::$middleware[$name];
        }
        return null;
    }

    /**
     * @return Routing
     * @throws \Exception
     */
    public function getRouteClass() {
        if (isset($this->router)) {
            return $this->router;
        }

        if (class_exists($this->routeClass)) {
            return $this->router = new $this->routeClass($this->routeClassParams);
        }

        throw new \Exception('路由实现对象不合法！！！');
    }
}