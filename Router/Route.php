<?php
class Route {
    public $path;
    public $file;

    public function __construct($path, $file) {
        $this->path = $path;
        $this->file = $file;
    }
}
