<?php

namespace Joking\Route\AnnotationTag;


class GroupTag {

    public $prefix;
    public $suffix;
    public $middleware;

    public function __construct($prefix = '', $suffix = '', $middleware = []) {
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->middleware = $middleware;
    }

    public function getOptions() {
        $options = [];
        if (!empty($this->prefix)) {
            $options['prefix'] = $this->prefix;
        }
        if (!empty($this->suffix)) {
            $options['suffix'] = $this->suffix;
        }
        if (!empty($this->middleware)) {
            $options['middleware'] = $this->middleware;
        }
        return $options;
    }
}