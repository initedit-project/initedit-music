<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cookie
 *
 * @author home
 */
class cookie extends Controller {

    public function index() {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 2;
        $result["title"] = "Cookie";
        $result["description"] = "This is Cookie Desription";
        $result["data"] = $this->getView("cookie/cookie");
        echo json_encode($result);
    }

}
