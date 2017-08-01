<?php

require '../vendor/autoload.php';

//一定要全路径啊
$routeFile = '/route.php';

$router = new \Joking\Route\Router(\Joking\Route\RoutePredefine::class, ['routeFile' => $routeFile]);

//当找不到映射时抛出 RouteException
try {

    /**
     * @var \Joking\Route\RouteEntity $routeEntity
     */
    $routeEntity = $router->dispatch('GET', '/joking/router');

    //继续操作

} catch (\Joking\Route\RouteException $exception) {
    exit('NOT FOUND MAPPING');
}

