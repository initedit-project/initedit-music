<?php

class App {

    protected $controller = "home";
    protected $method = "index";
    protected $params = [];
 
    public function __construct() {
       
        if (!SessionManagement::sessionExists("userid") && CookieManagment::getCookie("remember") == "true") {
            $loginInfo = CookieManagment::getCookie("loginInfo");
            $controller = new Controller();
            $userObject = $controller->loadController("user");
            $userDetailObject = $userObject->getUserByLoginInfo($loginInfo);
            SessionManagement::sessionStart();
            SessionManagement::setSession("userid", $userDetailObject->getUserid());
            SessionManagement::setSession("img", $userDetailObject->getUserImage());
            SessionManagement::setSession("username", $userDetailObject->getUsername());
            define("IS_LOGGED_IN", true);
        }
        
        
        $url = $this->parseUrl();
        if (file_exists("controllers/" . $url[0] . ".php")) {
            $this->controller = $url[0];
            unset($url[0]);
        }
        require_once "controllers/" . $this->controller . ".php";

        $this->controller = new $this->controller;

        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }
        $this->params = $url ? array_values($url) : [];
        
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl() {
        if (isset($_GET['url'])) {
            $url = explode("/", filter_var(rtrim($_GET['url'], "/"), FILTER_SANITIZE_URL));
            if (isset($url[0])) {
                if ($url[0] == "ajax" || $url[0]=="") {
                    array_shift($url);
                }
            }
            if (count($url) == 0) {
                $url[0] = "";
            }
            return $url;
        }
    }

}
