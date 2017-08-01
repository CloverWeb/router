<?php

namespace Joking\Route\AnnotationTag;


class RouteTag {

    public $url;
    public $name;
    public $method;
    public $middleware;

    public function __construct($url, $method = null, $name = null, $middleware = []) {
        $this->url = $url;
        $this->name = $name;
        $this->method = $method;
        $this->middleware = $middleware;
    }
}