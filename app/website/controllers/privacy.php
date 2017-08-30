<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of privacy
 *
 * @author home
 */
class privacy extends Controller {

    public function index() {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 2;
        $result["title"] = "About";
        $result["description"] = "This is Privacy Desription";
        $result["data"] = $this->getView("privacy/privacy");
        echo json_encode($result);
    }

}
