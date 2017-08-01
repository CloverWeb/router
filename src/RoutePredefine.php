<?php

namespace Joking\Route;


use function FastRoute\cachedDispatcher;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Joking\Annotation\Annotation;
use Joking\Route\AnnotationTag\GroupTag;
use Joking\Route\AnnotationTag\RouteTag;
use Joking\Route\Interfaces\Routing;

/**
 * Class PredefineRoute
 * @package Joking\Route
 */
class RoutePredefine implements Routing {

    protected $routeFile;

    //一定要全路径啊
    protected $cacheFile = '';

    /**
     * @var bool 是否开启缓存,依赖于 上面的 $cacheFile
     */
    protected $openCache = false;

    public function __construct(array $parameters) {
        foreach ($parameters as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }

        //注册需要使用的两个标签
        Annotation::register('Route', RouteTag::class);
        Annotation::register('Group', GroupTag::class);
    }

    /**
     * 调度，通过 fastRoute
     * @param $method
     * @param $url
     * @return RouteEntity
     * @throws RouteException
     */
    public function dispatch($method, $url): RouteEntity {
        $dispatcher = cachedDispatcher(function (RouteCollector $routeCollector) {
            if (isset($this->routeFile) && is_file($this->routeFile)) {
                $route = new RouteCollection();
                require_once $this->routeFile;
            }

            foreach (RouteCollection::getCollection() as $entity) {
                $routeCollector->addRoute($entity->method, $entity->url, $entity);
            }
        }, [
            'cacheFile' => $this->cacheFile,
            'cacheDisabled' => !$this->openCache,
        ]);

        $routeResult = $dispatcher->dispatch($method, $url);
        $status = array_shift($routeResult);
        if ($status == Dispatcher::FOUND) {
            $routeResult[0]->params = $routeResult[1];
            return $routeResult[0];
        }

        throw new RouteException('', $status == Dispatcher::NOT_FOUND ? 404 : 405);
    }

    /**
     * 路由发生异常时（找不到映射，参数不对等）获取异常处理的方法
     * @return mixed
     */
    public function getErrorHandle() {
        return RouteCollection::getErrorHandle();
    }
}