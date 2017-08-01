<?php

namespace Joking\Route;


use Joking\Route\Interfaces\Routing;

/**
 * 默认的url请求
 * url格式： /class/method/parameter1/value1/parameter2/value2.......
 * Class DefaultRoute
 * @package Joking\Route
 */
class RouteDefault implements Routing {

    //handle class的空间命名
    protected $namespace;

    //当url为：/ 的时候执行这里的配置
    protected $defaultHandle = 'main::welcome';

    protected $errorHandle;

    /**
     * class名称需要增加的后缀
     * 从url上截取的class为name ，那么类名就是 name . $classExtension
     * @var string $classExtension
     */
    protected $classExtension = '';

    /**
     * 同上，只不过这个是方法而已
     * @var string $methodExtension
     */
    protected $methodExtension = '';

    /**
     * 路由支持的请求方式
     * @var array
     */
    protected $httpMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

    public function __construct($parameters = []) {
        foreach ($parameters as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }

        if (empty($this->namespace)) {
            throw new \RuntimeException('namespace 不能为空');
        }
    }

    /**
     * @param $method
     * @param $url
     * @return RouteEntity
     */
    public function dispatch($method, $url): RouteEntity {
        list($className, $methodName, $params) = $this->resolver($url);
        $handle = $this->formatHandle([$className, $methodName]);

        $routeEntity = new RouteEntity($url, $method, $handle);

        $routeEntity->params = $params;
        $routeEntity->middleware($className);
        return $routeEntity;
    }

    /**
     * 解析url，从url中获得class，method，params
     * @param $url
     * @return array
     * @throws RouteException
     */
    public function resolver($url) {
        $array = explode('/', trim($url, '/'));
        $className = array_shift($array);
        if (empty($className)) {
            $result = explode('::', $this->defaultHandle);
            $result[] = [];
            return $result;
        }

        //处理method
        $methodName = array_shift($array);
        if (empty($methodName)) {
            throw new RouteException('无效的路由！', 404);
        }

        $num = true;   //用于区分 true：key，false：value
        $keys = [];
        $values = [];
        foreach ($array as $value) {
            $num === true ? $keys[] = $value : $values[] = $value;
            $num = !$num;
        }

        if (count($keys) !== count($values)) {
            throw new RouteException('', 402);
        }

        $params = [];
        for ($i = 0; $i < count($keys); $i++) {
            $params[$keys[$i]] = $values[$i];
        }

        return [$className, $methodName, $params];
    }

    /**
     * 格式化handle
     * @param array|string $handle 只能传入['class','method']或者 class::method 这两种形式的参数
     * @return string
     */
    protected function formatHandle($handle) {
        if (is_string($handle)) {
            $handle = explode('::', $handle);
        }

        list($className, $methodName) = $handle;
        $className = trim($this->namespace) . '\\' . ucfirst($className) . $this->classExtension;
        $methodName = $methodName . $this->methodExtension;
        return $className . '::' . $methodName;
    }

    /**
     * 路由发生异常时（找不到映射，参数不对等）获取异常处理的方法
     * 路由最后的防线
     * @return mixed
     */
    public function getErrorHandle() {
        return $this->errorHandle;
    }
}