<?php

namespace Joking\Route;


use Joking\Annotation\ReflectionAnnotationClass;
use Joking\Route\Interfaces\RouteMethod;

class RouteCollection {

    public function __construct($temporaryOptions = []) {
        $this->temporaryOptions = $temporaryOptions;
    }

    /**
     * @var RouteEntity[]
     */
    protected static $collection = [];

    //路由异常时需要执行的映射
    protected static $errorHandle;

    public $temporaryOptions = [];


    /**
     * 路由分组
     * @param array $options 只有四个参数 ['namespace' , 'prefix' , 'suffix' , 'middleware']
     * @param \Closure $closure
     */
    public function group(array $options, \Closure $closure) {
        $nextOptions = $this->temporaryOptions;
        $nextOptions[] = $options;
        call_user_func($closure, new RouteCollection($nextOptions));
    }

    /**
     * 添加请求路由
     * @param $method
     * @param $url
     * @param $handle
     * @return RouteEntity
     */
    public function addRoute($method, $url, $handle) {

        is_string($method) && $method = [$method];

        list($namespace, $prefix, $suffix, $middleware) = $this->disposeOptions($this->temporaryOptions);

        if (is_string($handle) && strpos($handle, '::')) {
            $handle = $namespace . '\\' . trim($handle, '\\');
        }

        $url = $prefix . '/' . ltrim($url, '/') . $suffix;
        if (strpos($url, '/') !== 0) {
            $url = '/' . $url;
        }

        $routeEntity = new RouteEntity($url, $method, $handle, $middleware);
        array_push(static::$collection, $routeEntity);
        return $routeEntity;
    }

    public function get($url, $handle) {
        return $this->addRoute(RouteMethod::GET, $url, $handle);
    }

    public function post($url, $handle) {
        return $this->addRoute(RouteMethod::POST, $url, $handle);
    }

    public function delete($url, $handle) {
        return $this->addRoute(RouteMethod::DELETE, $url, $handle);
    }

    public function put($url, $handle) {
        return $this->addRoute(RouteMethod::PUT, $url, $handle);
    }

    public function patch($url, $handle) {
        return $this->addRoute(RouteMethod::PATCH, $url, $handle);
    }

    public function any($url, $handle) {
        $methods = [RouteMethod::GET, RouteMethod::POST, RouteMethod::DELETE, RouteMethod::PUT, RouteMethod::PATCH];
        return $this->addRoute($methods, $url, $handle);
    }

    /**
     * 设置异常处理
     * @param $handle
     */
    public function errorAction($handle) {
        static::$errorHandle = $handle;
    }

    /**
     * 直接丢一个class进来，然后class里面用注释的方式定义 请求映射
     * @param $className
     */
    public function annotation($className) {
        $reflectionAnnotationClass = new ReflectionAnnotationClass(new \ReflectionClass($className));
        $classRouteLabel = $reflectionAnnotationClass->getAnnotation('Group');
        $annotationMethods = $reflectionAnnotationClass->getMethods();
        $callback = function (RouteCollection $route) use ($annotationMethods, $className) {
            foreach ($annotationMethods as $method) {
                if ($methodLabel = $method->getAnnotation('Route')) {
                    $handle = $className . '::' . $method->getName();

                    $resultEntity = $methodLabel->method == null ? $route->any($methodLabel->url, $handle)
                        : $route->addRoute($methodLabel->method, $methodLabel->url, $handle);

                    $methodLabel->name == null || $resultEntity->name($methodLabel->name);               //设置name
                    foreach ($methodLabel->middleware as $middleware) {                 //设置middleware
                        $resultEntity->middleware($middleware);
                    }
                }
            }
        };

        $classRouteLabel == null ? $callback($this) : $this->group($classRouteLabel->getOptions(), $callback);
    }

    protected function disposeOptions($options) {
        $namespace = [];
        $prefix = '';
        $suffix = '';
        $middleware = [];

        foreach ($options as $option) {
            if (isset($option['namespace'])) {
                $namespace[] = trim($option['namespace'], '\\');
            }

            if (isset($option['prefix'])) {
                $prefix = empty($prefix) ? $option['prefix'] : $prefix . '/' . $option['prefix'];
            }

            if (isset($option['suffix'])) {
                $suffix = $suffix . $option['suffix'];
            }

            if (isset($option['middleware'])) {
                $middleware = array_merge(
                    $middleware, is_array($option['middleware']) ? $option['middleware'] : [$option['middleware']]
                );
            }
        }

        return [implode('\\', $namespace), trim($prefix, '/'), $suffix, $middleware];
    }

    public static function getCollection() {
        return static::$collection;
    }

    public static function getErrorHandle() {
        return static::$errorHandle;
    }
}