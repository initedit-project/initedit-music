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

    public function index($USERNAME = "", $MENU = "overview", $PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "My Profile";
        $result["description"] = "This is Home Desription";
        if (empty($USERNAME) || !is_numeric($PAGE_NO) || $PAGE_NO < 0) {
            $result["data"] = $this->getView("common/error");
        } else {
            $query = "select userid,username,img,logininfo,cover from usersignup where username=:username";
            $this->database->query($query);
            $this->database->bind("username", $USERNAME);
            $userDetailObject = $this->database->single();
            if (isset($userDetailObject["userid"])) {
                $MENU = strtolower($MENU);
                $data = "";
                if ($MENU == "overview" || $MENU == "music") {
                    $r = $this->overview($userDetailObject, $PAGE_NO);
                    $data = $r["data"];
                    if (isset($r["musicinfo"])) {
                        $result["musicinfo"] = $r["musicinfo"];
                        $data = $this->getView("user/header", ["user" => $userDetailObject, "menu" => $MENU]) . $data;
                    } else {
                        
                    }
                } else if ($MENU == "likes") {
                    $r = $this->likes($userDetailObject, $PAGE_NO);
                    $data = $r["data"];
                    if (isset($r["musicinfo"])) {
                        $result["musicinfo"] = $r["musicinfo"];
                        $data = $this->getView("user/header", ["user" => $userDetailObject, "menu" => $MENU]) . $data;
                    } else {
                        
                    }
                } else if ($MENU == "playlist") {
                    $r = $this->playlist($userDetailObject, $PAGE_NO);
                    $data = $r["data"];
                    if (isset($r["musicinfo"])) {
                        $result["musicinfo"] = $r["musicinfo"];
                        $data = $this->getView("user/header", ["user" => $userDetailObject, "menu" => $MENU]) . $data;
                    } else {
                        $data = $this->getView("user/header", ["user" => $userDetailObject, "menu" => $MENU]) . $data;
                    }
                } else if ($MENU == "privates") {

                    if (SessionManagement::sessionExists("userid")) {
                        if (SessionManagement::getSession("userid") == $userDetailObject["userid"]) {
                            $r = $this->privates($userDetailObject, $PAGE_NO);
                            $data = $r["data"];
                            if (isset($r["musicinfo"])) {
                                $result["musicinfo"] = $r["musicinfo"];
                                $data = $this->getView("user/header", ["user" => $userDetailObject, "menu" => $MENU]) . $data;
                            } else {
                                
                            }
                        } else {
                            $data = $this->getView("common/error");
                        }
                    } else {
                        $data = $this->getView("common/error");
                    }
                } else if ($MENU == "setting") {
                    if (SessionManagement::sessionExists("userid")) {
                        if (SessionManagement::getSession("userid") == $userDetailObject["userid"]) {
                            $data = $this->getView("user/setting");
                            $data = $this->getView("user/header", ["user" => $userDetailObject, "menu" => $MENU]) . $data;
                        } else {
                            $data = $this->getView("common/error");
                        }
                    } else {
                        $data = $this->getView("common/error");
                    }
                } else {
                    $data = $this->getView("common/error");
                }
            } else {
                $data = $this->getView("common/error");
            }

            $result["data"] = $data;
        }

        echo json_encode($result);
    }

    private function overview($user, $pageno) {
        $userid = $user["userid"];
        $query = "select count(*) from music where privacy=0 and userid=:userid";
        $this->database->query($query);
        $this->database->bind("userid", $userid);
        $totalCount = $this->database->firstColumn();
        $pagingInfo = array();
        $pagingInfo["current"] = $pageno;
        $pagingInfo["musicPerPage"] = $this->musicPerPage;
        $pagingInfo["totalMusic"] = $totalCount;
        $pagingInfo["base"] = "/user/" . $user["username"] . "/overview/";

        if ($totalCount < ($pageno - 1) * $this->musicPerPage) {
            $result["data"] = $this->getView("common/error");
        } else {

            $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);


            $result["data"] = $this->getView("user/overview", ["paging" => $paging]);

            $startFrom = ($pageno - 1) * $this->musicPerPage;
            $query = "select url from music where privacy=0 and userid=:userid order by musicid desc limit $startFrom," . $this->musicPerPage;
            $this->database->query($query);
            $this->database->bind("userid", $userid);
            $musicURLs = $this->database->resultSet();
            $musicInfos = [];
            $this->loadTools("MusicHelper");
            $m = new MusicHelper();
            foreach ($musicURLs as $musicURL) {
                $url = $musicURL["url"];
                $musicInfos[] = $m->getByURL($url);
            }
            if (count($musicURLs) == 0) {
                $result["data"] = $this->getView("common/empty");
            }
            $result["musicinfo"][] = array("id" => "userMenuContent", "music" => $musicInfos);
        }
        return $result;
    }

    private function likes($user, $pageno) {
        $userid = $user["userid"];
        $query = "select count(*) from music_like where userid=:userid";
        $this->database->query($query);
        $this->database->bind("userid", $userid);
        $totalCount = $this->database->firstColumn();
        $pagingInfo = array();
        $pagingInfo["current"] = $pageno;
        $pagingInfo["musicPerPage"] = $this->musicPerPage;
        $pagingInfo["totalMusic"] = $totalCount;
        $pagingInfo["base"] = "/user/" . $user["username"] . "/likes/";

        if ($totalCount < ($pageno - 1) * $this->musicPerPage) {
            $result["data"] = $this->getView("common/error");
        } else {
            $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);


            $result["data"] = $this->getView("user/overview", ["paging" => $paging]);

            $startFrom = ($pageno - 1) * $this->musicPerPage;
            $query = "select url from music where privacy=0 and musicid in (select musicid from music_like where userid=:userid) order by musicid desc limit $startFrom," . $this->musicPerPage;
            $this->database->query($query);
            $this->database->bind("userid", $userid);
            $musicURLs = $this->database->resultSet();
            $musicInfos = [];
            $this->loadTools("MusicHelper");
            $m = new MusicHelper();
            foreach ($musicURLs as $musicURL) {
                $url = $musicURL["url"];
                $musicInfos[] = $m->getByURL($url);
            }
            if (count($musicURLs) == 0) {
                $result["data"] = $this->getView("common/empty");
            }


            $result["musicinfo"][] = array("id" => "userMenuContent", "music" => $musicInfos);
        }
        return $result;
    }

    private function privates($user, $pageno) {
        $userid = $user["userid"];
        $query = "select count(*) from music where privacy<>0 and userid=:userid";
        $this->database->query($query);
        $this->database->bind("userid", $userid);
        $totalCount = $this->database->firstColumn();
        $pagingInfo = array();
        $pagingInfo["current"] = $pageno;
        $pagingInfo["musicPerPage"] = $this->musicPerPage;
        $pagingInfo["totalMusic"] = $totalCount;
        $pagingInfo["base"] = "/user/" . $user["username"] . "/privates/";

        if ($totalCount < ($pageno - 1) * $this->musicPerPage) {
            $result["data"] = $this->getView("common/error");
        } else {

            $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);


            $result["data"] = $this->getView("user/overview", ["paging" => $paging]);

            $startFrom = ($pageno - 1) * $this->musicPerPage;
            $query = "select url from music where privacy<>0 and userid=:userid order by musicid desc limit $startFrom," . $this->musicPerPage;
            $this->database->query($query);
            $this->database->bind("userid", $userid);
            $musicURLs = $this->database->resultSet();
            $musicInfos = [];
            $this->loadTools("MusicHelper");
            $m = new MusicHelper();
            foreach ($musicURLs as $musicURL) {
                $url = $musicURL["url"];
                $musicInfos[] = $m->getByURL($url);
            }
            if (count($musicURLs) == 0) {
                $result["data"] = $this->getView("common/empty");
            }
            $result["musicinfo"][] = array("id" => "userMenuContent", "music" => $musicInfos);
        }
        return $result;
    }

    private function playlist($user, $pageno) {
        $userid = $user["userid"];
        $loggedinUserid = (SessionManagement::sessionExists("userid")) ? SessionManagement::getSession("userid") : -1;
        $query = "select count(*) from playlistname where  (privacy=0 or (privacy<>0 and userid=:loggedinuserid)) and userid=:userid";
        $this->database->query($query);
        $this->database->bind("userid", $userid);
        $this->database->bind("loggedinuserid", $loggedinUserid);

        $totalCount = $this->database->firstColumn();
        $pagingInfo = array();
        $pagingInfo["current"] = $pageno;
        $pagingInfo["musicPerPage"] = $this->musicPerPage;
        $pagingInfo["totalMusic"] = $totalCount;
        $pagingInfo["base"] = "/user/" . $user["username"] . "/playlist/";

        if ($totalCount < ($pageno - 1) * $this->musicPerPage) {
            $result["data"] = $this->getView("common/error");
        } else {

            $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);



            $startFrom = ($pageno - 1) * $this->musicPerPage;

            $query = "select playlistid,name,privacy,url,:username as username from playlistname where  (privacy=0 or (privacy<>0 and userid=:loggedinuserid)) and userid=:userid order by playlistid desc limit $startFrom," . $this->musicPerPage;
            $this->database->query($query);
            $this->database->bind("userid", $userid);
            $this->database->bind("loggedinuserid", $loggedinUserid);
            $this->database->bind("username", $user["username"]);
            $playlists = $this->database->resultSet();


            $this->loadTools("MusicHelper");
            $m = new MusicHelper();
            $maxLimit = ($this->musicPerRow);
//            $maxLimit = 6;
            foreach ($playlists as $playlist) {
                extract($playlist);

                $query = "select url from music where privacy=0 and musicid in (select musicid from playlist where playlistid=:playlistid order by id) order by musicid desc limit $maxLimit";
                $this->database->query($query);
                $this->database->bind("playlistid", $playlistid);
                $musicURLs = $this->database->resultSet();
                $musicInfos = [];
                foreach ($musicURLs as $musicURL) {
                    $url = $musicURL["url"];
                    $musicInfos[] = $m->getByURL($url);
                }
                $display = "box";
                if(count($musicInfos)>3){
//                    $display="playlist";
                }
                $result["musicinfo"][] = array("id" => "userMenuContentPlaylist_" . $playlistid, "music" => $musicInfos,"display"=>$display);
            }
            $result["data"] = $this->getView("user/playlist", ["playlists" => $playlists, "paging" => $paging]);



            if (count($playlists) == 0) {
                $result["data"] = $this->getView("common/empty");
            }
            
        }
        return $result;
    }

}
