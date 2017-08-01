<?php

namespace Joking\Route;

use Throwable;

class RouteException extends \Exception {

    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        switch ($code) {
            case 404 :
                $message = '404 NOT FOUND';
                break;
            case 405 :
                $message = '405 METHOD NOT ALLOWED';
                break;
            case 402:
                $message = 'param format error';
                break;
            default:
                ;
        }

        parent::__construct($message, $code, $previous);
    }

}