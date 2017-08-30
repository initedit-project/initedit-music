<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of user
 *
 * @author home
 */
class user extends Controller {

    public function index($PAGE = "", $PAGE_NO = 1) {
        $result = array();
        $result["status"] = 404;
        $result["message"] = "Page Was not found.";
        $result["description"] = "Resource that you are trying to access is not available or you don't have persmission.";
        if (is_numeric($PAGE)) {
            
        } else {
            
        }
        echo json_encode($result);
    }

    public function overview($USER = "", $PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;


        $USER = strtolower($USER);
        if (!$this->userExists($USER)) {
            $result["status"] = 404;
            $result["title"] = "User Not Found.";
            $result["messsage"] = "User you are looking for does not exist.";
        } else {

            $result["title"] = "Overview";
            $result["messsage"] = "Music Overview By $USER On " . SITE_NAME;
            $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

            if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
                $result["messsage"] = "Bas Parameter.";
            } else {

                $userDetail = $this->getUserByName($USER);
                $result["user"] = $userDetail;

                $query = "select count(*) from music where privacy=0 and userid=:userid";
                $this->database->query($query);
                $this->database->bind("userid", $userDetail["userid"]);
                $totalCount = $this->database->firstColumn();
                $pagingInfo = array();
                $pagingInfo["current"] = $PAGE_NO;
                $pagingInfo["musicPerPage"] = $this->musicPerPage;
                $pagingInfo["totalMusic"] = $totalCount;
                $pagingInfo["base"] = "/api/" . App::$API_VERSION . "/user/overview/$USER/";

                if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                    $result["status"] = 422;
                    $result["title"] = "Error";
                    $result["messsage"] = "Bas Parameter.";
                } else {
                    $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                    $query = "select url from music where privacy=0 and userid=:userid order by musicid desc limit $startFrom," . $this->musicPerPage;
                    $this->database->query($query);
                    $this->database->bind("userid", $userDetail["userid"]);
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
        }
        echo json_encode($result);
    }

    public function likes($USER = "", $PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;


        $USER = strtolower($USER);
        if (!$this->userExists($USER)) {
            $result["status"] = 404;
            $result["title"] = "User Not Found.";
            $result["messsage"] = "User you are looking for does not exist.";
        } else {

            $result["title"] = "Likes";
            $result["messsage"] = "Music Likes By $USER On " . SITE_NAME;
            $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

            if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
                $result["messsage"] = "Bas Parameter.";
            } else {

                $userDetail = $this->getUserByName($USER);
                $result["user"] = $userDetail;

                $query = "select count(*) from music_like where musicid not  in (select musicid from music where privacy<>0) and userid=:userid";
                $this->database->query($query);
                $this->database->bind("userid", $userDetail["userid"]);
                $totalCount = $this->database->firstColumn();
                $pagingInfo = array();
                $pagingInfo["current"] = $PAGE_NO;
                $pagingInfo["musicPerPage"] = $this->musicPerPage;
                $pagingInfo["totalMusic"] = $totalCount;
                $pagingInfo["base"] = "/api/" . App::$API_VERSION . "/user/likes/$USER/";

                if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                    $result["status"] = 422;
                    $result["title"] = "Error";
                    $result["messsage"] = "Bas Parameter.";
                } else {
                    $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                    $query = "select url from music where musicid in (select musicid from music_like where userid=:userid) and privacy=0 order by musicid desc limit $startFrom," . $this->musicPerPage;
                    $this->database->query($query);
                    $this->database->bind("userid", $userDetail["userid"]);
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
        }
        echo json_encode($result);
    }

    public function music($USER = "", $PAGE_NO = 1) {
        $this->overview($USER, $PAGE_NO);
    }

    public function playlist($USER = "", $PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;


        $USER = strtolower($USER);
        if (!$this->userExists($USER)) {
            $result["status"] = 404;
            $result["title"] = "User Not Found.";
            $result["messsage"] = "User you are looking for does not exist.";
        } else {

            $result["title"] = "Playlist";
            $result["messsage"] = "Playlist By $USER On " . SITE_NAME;
            $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

            if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
                $result["messsage"] = "Bas Parameter.";
            } else {

                $userDetail = $this->getUserByName($USER);
                $result["user"] = $userDetail;

                $query = "select count(*) from playlistname where privacy=0 and userid=:userid";
                $this->database->query($query);
                $this->database->bind("userid", $userDetail["userid"]);
                $totalCount = $this->database->firstColumn();
                $pagingInfo = array();
                $pagingInfo["current"] = $PAGE_NO;
                $pagingInfo["musicPerPage"] = $this->musicPerPage;
                $pagingInfo["totalMusic"] = $totalCount;
                $pagingInfo["base"] = "/api/" . App::$API_VERSION . "/user/playlist/$USER/";

                if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                    $result["status"] = 422;
                    $result["title"] = "Error";
                    $result["messsage"] = "Bas Parameter.";
                } else {
                    
                    
                    
                    $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                    $query = "select playlistid,name,url from playlistname where privacy=0 and userid=:userid order by playlistid desc limit $startFrom," . $this->musicPerPage;
                    $this->database->query($query);
                    $this->database->bind("userid", $userDetail["userid"]);

                    $playlists = $this->database->resultSet();
                    $musicInfos = [];
                    foreach ($playlists as $playlist) {

                        $playlist["apiurl"] = SITE_URL . "/api/" . App::$API_VERSION . "/playlist/" . $playlist["url"];
                        $playlist["url"] = SITE_URL . "/playlist/" . $playlist["url"];

                        $musicInfos[] = $playlist;
                    }
                    $result["playlistinfo"][] = array("playlist" => $musicInfos);
                    $result["paging"] = $this->pagingInfo($pagingInfo);
                }
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

    private function userExists($username) {
        $this->database->query("select count(*) from usersignup where username=:username");
        $this->database->bind("username", $username);
        return ($this->database->firstColumn() == "0") ? false : true;
    }

    private function getUserByName($username) {
        $this->database->query("select userid,username from usersignup where username=:username");
        $this->database->bind("username", $username);
        return ($this->database->single());
    }

}
