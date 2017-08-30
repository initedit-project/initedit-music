<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of help
 *
 * @author home
 */
class help extends Controller {

    public function index() {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 2;
        $result["title"] = "Help";
        $result["description"] = "This is Help Desription";
        $result["data"] = $this->getView("help/help");
        echo json_encode($result);
    }

}
