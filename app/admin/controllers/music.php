<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of music
 *
 * @author home
 */
class music extends Controller {

    public function index($PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Admin Music " . SITE_NAME;
        $result["description"] = "";
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["data"] = $this->getView("common/error");
        } else {

            $query = "select count(*) from music";
            $this->database->query($query);

            $totalCount = $this->database->firstColumn();
            $pagingInfo = array();
            $pagingInfo["current"] = $PAGE_NO;
            $pagingInfo["musicPerPage"] = $this->musicPerPage;
            $pagingInfo["totalMusic"] = $totalCount;
            $pagingInfo["base"] = "/admin/music/all/";

            if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                $result["data"] = $this->getView("common/error");
            } else {
                $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                $query = "select url from music order by musicid desc limit $startFrom," . $this->musicPerPage;
                $this->database->query($query);
                $musicURLs = $this->database->resultSet();
                $musicInfos = $this->getView("/common/musicheader",["search" => ""]);

                $this->loadTools("MusicHelper");
                $m = new MusicHelper();
                foreach ($musicURLs as $musicURL) {
                    $url = $musicURL["url"];
                    $musicObject = $m->getByURL($url);
                    $musicInfos.= $this->getView("common/music", ["music" => $musicObject]);
                }
                $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);
                $result["data"] = $this->getView("music/list", ["paging" => $paging, "musics" => $musicInfos]);
            }
        }
        echo json_encode($result);
    }

    public function search($PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Search Admin Music ";
        $result["description"] = "";
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;
        $searchTerm = isset($_GET["s"]) ? $_GET["s"] : "";
        $originalSearch = $searchTerm;
        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["data"] = $this->getView("common/error");
        } else {

            $searchTerm = preg_replace("/[^A-Za-z0-9 _]/", '', $searchTerm);
            $searchTerm = preg_replace('!\s+!', ' ', $searchTerm);
            $searchTerm = (strlen($searchTerm) > 50) ? substr($searchTerm, 0, 50) : $searchTerm;
            $searchTerm = str_replace(" ", "%", $searchTerm);


            $query = "select count(*) from music where title like :searchTerm";
            $this->database->query($query);
            $this->database->bind("searchTerm", "%" . $searchTerm . "%");

            $totalCount = $this->database->firstColumn();
            $pagingInfo = array();
            $pagingInfo["current"] = $PAGE_NO;
            $pagingInfo["musicPerPage"] = $this->musicPerPage;
            $pagingInfo["totalMusic"] = $totalCount;
            $pagingInfo["base"] = "/admin/music/search/";

            if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                $result["data"] = $this->getView("common/error");
            } else {
                $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                $query = "select url from music where title like :searchTerm order by musicid desc limit $startFrom," . $this->musicPerPage;
                $this->database->query($query);
                $this->database->bind("searchTerm", "%" . $searchTerm . "%");
                $musicURLs = $this->database->resultSet();
                $musicInfos = $this->getView("/common/musicheader", ["search" => $originalSearch]);

                $this->loadTools("MusicHelper");
                $m = new MusicHelper();
                foreach ($musicURLs as $musicURL) {
                    $url = $musicURL["url"];
                    $musicObject = $m->getByURL($url);
                    $musicInfos.= $this->getView("common/music", ["music" => $musicObject]);
                }
                if (count($musicURLs) == 0) {
                    $result["data"] = $this->getView("music/noResultFound");
                }
                $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);
                $result["data"] = $this->getView("music/list", ["paging" => $paging, "musics" => $musicInfos]);
            }
        }
        echo json_encode($result);
    }

    public function all($PAGE_NO = 1) {
        $this->index($PAGE_NO);
    }

    public function edit($orginalurl = "") {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Edit Music";
        $result["description"] = "This is Edit Page Desription";

        $query = "select count(*) from music where url=:url";
        $this->database->query($query);

        $this->database->bind("url", $orginalurl);
        $count = $this->database->firstColumn();
        if ($count == "1") {
            $this->loadTools("MusicHelper");
            $m = new MusicHelper();
            $music = $m->getByURL($orginalurl);
            $query = "select tagname from tag where musicid=:musicid";
            $this->database->query($query);
            $this->database->bind("musicid", $music["musicid"]);
            $tagString = $this->database->firstColumn();

            $result["data"] = $this->getView("music/edit", ["music" => $music, "tag" => $tagString]);
        } else {
            $result["data"] = $this->getView("common/error");
            $result["title"] = "Sorry! Page Not Found";
        }
        echo json_encode($result);
    }

}
