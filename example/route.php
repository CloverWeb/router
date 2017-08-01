<?php
/**
 * @var \Joking\Route\RouteCollection $route
 */

$route->group(['namespace' => 'App/Handle'], function (\Joking\Route\RouteCollection $route) {

    $route->get('/joking/router', 'Handle::action');
});