<?php

//http://culttt.com/2012/10/01/roll-your-own-pdo-php-class/
class home extends Controller {

    public function index($PAGE = "", $PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Welcome To Initedit Music";
        $result["description"] = "This is Home Desription";
        if (is_numeric($PAGE)) {
            $PAGE_NO = $PAGE;
        } else {
            if ($PAGE == "trending") {
                $this->trending($PAGE_NO);
                return;
            } else if ($PAGE == "surprise") {
                $this->surprise($PAGE_NO);
                return;
            } else if ($PAGE == "new") {
                $this->newpage($PAGE_NO);
                return;
            } else {
                $PAGE_NO = $PAGE;
            }
        }
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["data"] = $this->getView("common/error");
        } else {

            $query = "select count(*) from music where privacy=0";
            $this->database->query($query);

            $totalCount = $this->database->firstColumn();
            $pagingInfo = array();
            $pagingInfo["current"] = $PAGE_NO;
            $pagingInfo["musicPerPage"] = $this->musicPerPage;
            $pagingInfo["totalMusic"] = $totalCount;
            $pagingInfo["base"] = "/trending/";

            if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                $result["data"] = $this->getView("common/error");
            } else {

                $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);
                $result["data"] = $this->getView("home/home", ["paging" => $paging]);

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
                $result["musicinfo"][] = array("id" => "homeTrending", "music" => $musicInfos);
            }
        }
        echo json_encode($result);
    }

    private function trending($PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Trending";
        $result["description"] = "This is Hot Desription";
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["data"] = $this->getView("common/error");
        } else {

            $query = "select count(*) from music where privacy=0";
            $this->database->query($query);

            $totalCount = $this->database->firstColumn();
            $pagingInfo = array();
            $pagingInfo["current"] = $PAGE_NO;
            $pagingInfo["musicPerPage"] = $this->musicPerPage;
            $pagingInfo["totalMusic"] = $totalCount;
            $pagingInfo["base"] = "/trending/";

            if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                $result["data"] = $this->getView("common/error");
            } else {

                $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);
                $result["data"] = $this->getView("home/trending", ["paging" => $paging]);

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
                $result["musicinfo"][] = array("id" => "homeTrending", "music" => $musicInfos);
            }
        }
        echo json_encode($result);
    }

    private function surprise($PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Surprise";
        $result["description"] = "This is Hot Desription";
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["data"] = $this->getView("common/error");
        } else {

            $query = "select count(*) from music where privacy=0";
            $this->database->query($query);

            $totalCount = $this->database->firstColumn();
            $pagingInfo = array();
            $pagingInfo["current"] = $PAGE_NO;
            $pagingInfo["musicPerPage"] = $this->musicPerPage;
            $pagingInfo["totalMusic"] = $totalCount;
            $pagingInfo["base"] = "/";

            if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                $result["data"] = $this->getView("common/error");
            } else {

                $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);
                $result["data"] = $this->getView("home/surprise", ["paging" => $paging]);
                $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                $query = "select url from music where privacy=0 order by rand() limit $startFrom," . $this->musicPerPage;
                $this->database->query($query);
                $musicURLs = $this->database->resultSet();
                $musicInfos = [];
                $this->loadTools("MusicHelper");
                $m = new MusicHelper();
                foreach ($musicURLs as $musicURL) {
                    $url = $musicURL["url"];
                    $musicInfos[] = $m->getByURL($url);
                }
                $result["musicinfo"][] = array("id" => "homeTrending", "music" => $musicInfos);
            }
        }
        echo json_encode($result);
    }

    private function newpage($PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "New";
        $result["description"] = "This is New Desription";
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["data"] = $this->getView("common/error");
        } else {

            $query = "select count(*) from music where privacy=0";
            $this->database->query($query);

            $totalCount = $this->database->firstColumn();
            $pagingInfo = array();
            $pagingInfo["current"] = $PAGE_NO;
            $pagingInfo["musicPerPage"] = $this->musicPerPage;
            $pagingInfo["totalMusic"] = $totalCount;
            $pagingInfo["base"] = "/new/";

            if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                $result["data"] = $this->getView("common/error");
            } else {

                $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);
                $result["data"] = $this->getView("home/new", ["paging" => $paging]);

                $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                $query = "select url from music where privacy=0 order by musicid desc limit $startFrom," . $this->musicPerPage;
                $this->database->query($query);
                $musicURLs = $this->database->resultSet();
                $musicInfos = [];
                $this->loadTools("MusicHelper");
                $m = new MusicHelper();
                foreach ($musicURLs as $musicURL) {
                    $url = $musicURL["url"];
                    $musicInfos[] = $m->getByURL($url);
                }
                $result["musicinfo"][] = array("id" => "homeTrending", "music" => $musicInfos);
            }
        }
        echo json_encode($result);
    }

}
