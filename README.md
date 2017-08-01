# router
一个简单的路由方式

>一个简单的使用方式
    
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
    
>路由文件的内容应该时这样的：

    /**
     * @var \Joking\Route\RouteCollection $route
     */
    
    $route->group(['namespace' => 'App/Handle'], function (\Joking\Route\RouteCollection $route) {
    
        $route->get('/joking/router', 'Handle::action');
    });