<?php

class home extends Controller {

    public function index($PAGE = "", $PAGE_NO = 1) {
        $result = array();
        $result["status"] = 404;
        $result["message"] = "Page Was not found.";
        $result["description"] = "Resource that you are trying to access is not available or you don't have persmission.";
        if (is_numeric($PAGE)) {
            
        } else {
            if($PAGE=="new"){
                $this->newpage($PAGE_NO);
                return;
            }
        }
        echo json_encode($result);
    }

    public function trending($PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;

        $result["title"] = "Trending";
        $result["messsage"] = "Trending Music On " . SITE_NAME;
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["messsage"] = "Bas Parameter.";
        } else {

            $query = "select count(*) from music where privacy=0";
            $this->database->query($query);

            $totalCount = $this->database->firstColumn();
            $pagingInfo = array();
            $pagingInfo["current"] = $PAGE_NO;
            $pagingInfo["musicPerPage"] = $this->musicPerPage;
            $pagingInfo["totalMusic"] = $totalCount;
            $pagingInfo["base"] = "/api/" . App::$API_VERSION . "/home/trending/";

            if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                $result["status"] = 422;
                $result["title"] = "Error";
                $result["messsage"] = "Bas Parameter.";
            } else {
                $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                $query = "select url from music where privacy=0 order by view desc limit $startFrom," . $this->musicPerPage;
                $this->database->query($query);
                $musicURLs = $this->database->resultSet();
                $musicInfos = [];
                $this->loadTools("MusicHelper");
                $m = new MusicHelper();
                foreach ($musicURLs as $musicURL) {
                    $url = $musicURL["url"];
                    $musicInfos[] = $m->getByURL($url);
                }
                $result["musicinfo"][] = array("music" => $musicInfos);
                $result["paging"] = $this->pagingInfo($pagingInfo);
            }
        }
        echo json_encode($result);
    }

    public function surprise($PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;

        $result["title"] = "Surprise";
        $result["messsage"] = "Surprise Music On " . SITE_NAME;
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["messsage"] = "Bas Parameter.";
        } else {

            $query = "select count(*) from music where privacy=0";
            $this->database->query($query);

            $totalCount = $this->database->firstColumn();
            $pagingInfo = array();
            $pagingInfo["current"] = 1;
            $pagingInfo["musicPerPage"] = $this->musicPerPage;
            $pagingInfo["totalMusic"] = 0;
            $pagingInfo["base"] = "/api/" . App::$API_VERSION . "/home/surprise/";

            if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                $result["status"] = 422;
                $result["title"] = "Error";
                $result["messsage"] = "Bas Parameter.";
            } else {
                $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                $query = "select url from music where privacy=0 order by rand() desc limit $startFrom," . $this->musicPerPage;
                $this->database->query($query);
                $musicURLs = $this->database->resultSet();
                $musicInfos = [];
                $this->loadTools("MusicHelper");
                $m = new MusicHelper();
                foreach ($musicURLs as $musicURL) {
                    $url = $musicURL["url"];
                    $musicInfos[] = $m->getByURL($url);
                }
                $result["musicinfo"][] = array("music" => $musicInfos);
                $result["paging"] = $this->pagingInfo($pagingInfo);
            }
        }
        echo json_encode($result);
    }
    
    private function newpage($PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;

        $result["title"] = "Newesr";
        $result["messsage"] = "New Music On " . SITE_NAME;
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["messsage"] = "Bas Parameter.";
        } else {

            $query = "select count(*) from music where privacy=0";
            $this->database->query($query);

            $totalCount = $this->database->firstColumn();
            $pagingInfo = array();
            $pagingInfo["current"] = $PAGE_NO;
            $pagingInfo["musicPerPage"] = $this->musicPerPage;
            $pagingInfo["totalMusic"] = $totalCount;
            $pagingInfo["base"] = "/api/" . App::$API_VERSION . "/home/trending/";

            if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                $result["status"] = 422;
                $result["title"] = "Error";
                $result["messsage"] = "Bas Parameter.";
            } else {
                $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                $query = "select url from music where privacy=0 order by view desc limit $startFrom," . $this->musicPerPage;
                $this->database->query($query);
                $musicURLs = $this->database->resultSet();
                $musicInfos = [];
                $this->loadTools("MusicHelper");
                $m = new MusicHelper();
                foreach ($musicURLs as $musicURL) {
                    $url = $musicURL["url"];
                    $musicInfos[] = $m->getByURL($url);
                }
                $result["musicinfo"][] = array("music" => $musicInfos);
                $result["paging"] = $this->pagingInfo($pagingInfo);
            }
        }
        echo json_encode($result);
    }
    
    
   

}
