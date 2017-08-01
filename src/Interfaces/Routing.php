<?php

namespace Joking\Route\Interfaces;


use Joking\Route\RouteEntity;

interface Routing {

    public function dispatch($method, $url): RouteEntity;

    /**
     * 路由发生异常时（找不到映射，参数不对等）获取异常处理的方法
     * 路由最后的防线
     * @return mixed
     */
    public function getErrorHandle();
}