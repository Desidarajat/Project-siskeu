<?php

require_once 'models/Log.php';

class LogController {

    public function index() {
        $model = new Log();
        $data = $model->getAll();
        require 'views/admin/Log.php';
    }
}