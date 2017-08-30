<?php

/**
 * Created by PhpStorm.
 * User: home
 * Date: 2/5/2016
 * Time: 1:13 PM
 */
class Controller {

    public $database;
    public $musicPerPage = MUSIC_PER_PAGE;
    public $musicPerRow = 6;

    /**
     * Controller constructor.
     */
    public function __construct() {
        $this->database = new Database();
    }

    public function model($model) {
        require_once "models/" . $model . ".php";
        return new $model();
    }

    public function view($view, $data = []) {
        include "views/" . $view . ".php";
    }

    public function getView($view, $data = []) {
        ob_start();
        include "views/" . $view . ".php";
        return ob_get_clean();
    }

    public function loadController($controllerPath) {
        if (file_exists("controllers/" . $controllerPath . ".php")) {
            require_once "controllers/" . $controllerPath . ".php";
            return new $controllerPath;
        } else {
            return new Exception("Controller Class Not Found.");
        }
    }

    public function loadTools($controllerPath) {
        if (file_exists("../lib/tools/" . $controllerPath . ".php")) {
            require_once "../lib/tools/" . $controllerPath . ".php";
        } else {
            return new Exception("tools Class Not Found.");
        }
    }

    public function loadLib($libPath) {
        if (file_exists("../lib/" . $libPath)) {
            require_once "../lib/" . $libPath;
        } else {
            return new Exception("Library Class Not Found.");
        }
    }

    public function error($ERROR_NO = 404, $ERROR_REASON = "") {
        $this->view("common/error", ["error_no" => $ERROR_NO, "error_reason" => $ERROR_REASON]);
    }

    protected function pagingInfo($param) {
        extract($param);
        $base = SITE_URL . $base;
        $pagingInfo = [];
        if ($current == 1) {
            $pagingInfo[] = ["previous" => null];
        } else {
            $pagingInfo[] = ["previous" => $base . ($current - 1)];
        }

        if ($totalMusic > $current * $musicPerPage) {
            $pagingInfo[] = ["next" => $base . ($current + 1)];
        } else {
            $pagingInfo[] = ["next" => null];
        }
        return $pagingInfo;
    }

}
