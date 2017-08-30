<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of search
 *
 * @author home
 */
class search extends Controller {

    public function index() {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 2;
        $result["title"] = "Page not found";
        $result["description"] = "404: Page not found";
        $result["data"] = $this->getView("common/error");
        echo json_encode($result);
    }

    public function getSearchDialogResult($PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Search Music";
        $result["description"] = "This is Search Desription";
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["data"] = $this->getView("common/error");
        } else {
            if (isset($_POST["search"])) {
                $searchTerm = $_POST["search"];

                $searchTerm = preg_replace("/[^A-Za-z0-9 _]/", '', $searchTerm);
                $searchTerm = preg_replace('!\s+!', ' ', $searchTerm);
                $searchTerm = (strlen($searchTerm) > 50) ? substr($searchTerm, 0, 50) : $searchTerm;
                $searchTerm = str_replace(" ", "%", $searchTerm);


                $query = "select count(*) from music where privacy=0 and title like :searchTerm";
                $this->database->query($query);
                $this->database->bind("searchTerm", "%" . $searchTerm . "%");
                $totalCount = $this->database->firstColumn();
                $pagingInfo = array();
                $pagingInfo["current"] = $PAGE_NO;
                $pagingInfo["musicPerPage"] = $this->musicPerPage;
                $pagingInfo["totalMusic"] = $totalCount;
                $pagingInfo["base"] = "/search/";
                if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                    $result["data"] = $this->getView("common/error");
                } else {
                    //$paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);
                    $paging = "";
                    $result["data"] = $this->getView("search/searchResultDialog", ["paging" => $paging]);
                    $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                    $query = "select url from music where privacy=0 and title like :searchTerm order by musicid desc limit $startFrom," . $this->musicPerPage;
                    $this->database->query($query);
                    $this->database->bind("searchTerm", "%" . $searchTerm . "%");
                    $musicURLs = $this->database->resultSet();
                    $musicInfos = [];
                    $this->loadTools("MusicHelper");
                    $m = new MusicHelper();
                    foreach ($musicURLs as $musicURL) {
                        $url = $musicURL["url"];
                        $musicInfos[] = $m->getByURL($url);
                    }
                    $result["musicinfo"][] = array("id" => "searchDialogResult", "music" => $musicInfos);
                    if (count($musicURLs) == 0) {
                        $result["data"] = $this->getView("search/noResultFound");
                    }
                }
            } else {
                $result["data"] = $this->getView("common/error");
            }
        }
        echo json_encode($result);
    }

}
