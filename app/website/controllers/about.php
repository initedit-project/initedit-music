<?php
class about extends Controller {

    public function index() {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 2;
        $result["title"] = "About";
        $result["description"] = "This is About Desription";
        $result["data"] = $this->getView("about/about");
        echo json_encode($result);
    }

}
