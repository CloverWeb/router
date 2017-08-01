<?php

namespace Joking\Route;


class RouteEntity {

    public function __construct($url, $method, $handle, $middleware = []) {
        $this->url = $url;
        $this->method = $method;
        $this->handle = $handle;
        $this->middleware = $middleware;
    }

    public $url;
    public $method = [];
    public $handle;
    public $params = [];
    public $name;
    public $middleware = [];

    public function middleware($middlewareNames) {
        is_string($middlewareNames) && $middlewareNames = [$middlewareNames];
        $this->middleware = array_merge($this->middleware, $middlewareNames);
        return $this;
    }

    public function name($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * 缓存需要
     * @param $properties
     * @return RouteEntity
     */
    public static function __set_state($properties) {
        $routeEntity = new RouteEntity($properties['url'], $properties['method'], $properties['handle']);
        foreach ($properties as $name => $value) {
            if (property_exists($routeEntity, $name)) {
                $routeEntity->$name = $value;
            }
        }

        return $routeEntity;
    }
}