<?php

namespace Joking\Route\Interfaces;


use Joking\Route\RouteEntity;

interface RouteMiddlewareInterface {

    public function handle(RouteEntity $entity);
}