<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of playlist
 *
 * @author home
 */
/*
 * Varify Music Id in create method
 */


class playlist extends Controller {

    public function index($URL = "", $PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Playlist Page";
        $result["description"] = "This is Playlist Desription";
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;

        if (!is_numeric($PAGE_NO) || $PAGE_NO < 0 || empty($URL)) {
            $result["data"] = $this->getView("common/error");
        } else {

            $loggedinUserid = (SessionManagement::sessionExists("userid")) ? SessionManagement::getSession("userid") : -1;


            $query = "select count(*) from playlistname where url=:url and (privacy=0 or (privacy<>0 and userid=:loggedinuserid))";

            $this->database->query($query);
            $this->database->bind("url", $URL);
            $this->database->bind("loggedinuserid", $loggedinUserid);


            if ($this->database->firstColumn() == "1") {

                $query = "select playlistid,name,url,userid,time from playlistname where url=:url";
                $this->database->query($query);
                $this->database->bind("url", $URL);
                $playlistInfo = $this->database->single();


                $query = "select username,img,cover from usersignup where userid=:userid";
                $this->database->query($query);
                $this->database->bind("userid", $playlistInfo["userid"]);
                $userInfo = $this->database->single();





                $query = "select count(*) from playlist where playlistid=:playlistid";
                $this->database->query($query);
                $this->database->bind("playlistid", $playlistInfo["playlistid"]);
                $totalCount = $this->database->firstColumn();
                $pagingInfo = array();
                $pagingInfo["current"] = $PAGE_NO;
                $pagingInfo["musicPerPage"] = $this->musicPerPage;
                $pagingInfo["totalMusic"] = $totalCount;
                $pagingInfo["base"] = "/playlist/" . $URL . "/";

                if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                    $result["data"] = $this->getView("common/error");
                } else {


                    $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                    $query = "select url from music where privacy=0 and musicid IN (select musicid from playlist where playlistid=:playlistid) order by musicid desc limit $startFrom," . $this->musicPerPage;
                    $this->database->query($query);
                    $this->database->bind("playlistid", $playlistInfo["playlistid"]);
                    $musicURLs = $this->database->resultSet();
                    $musicInfos = [];
                    $this->loadTools("MusicHelper");
                    $m = new MusicHelper();

                    $images = [];
                    foreach ($musicURLs as $musicURL) {
                        $url = $musicURL["url"];
                        $musicObject = $m->getByURL($url);
                        $musicInfos[] = $musicObject;
                        $images[] = $musicObject["image"]["thumb"];
                    }
//                    $images = array_slice ($images,0,6);
                    $result["musicinfo"][] = array("id" => "homeTrending", "music" => $musicInfos,"display"=>"playlist");


                    $paging = $this->getView("/common/paging", ["paging" => $pagingInfo]);
                    $result["data"] = $this->getView("playlist/single", ["paging" => $paging, "playlist" => $playlistInfo, "user" => $userInfo, "images" => $images]);
                }
            } else {
                $result["data"] = $this->getView("common/error");
            }
        }
        echo json_encode($result);
    }

    public function add($d = 15) {
        $result = array();
        $result["status"] = 200;
        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";

        if (!empty($info)) {
            $info = (array) $info;
            $info = $info["music"];
            $info = (array) $info;
            if (isset($info["musicid"])) {
                if (!SessionManagement::sessionExists("userid")) {
                    $result["data"] = $this->getView("playlist/login");
                } else {
                    $musicid = $info["musicid"];


                    $query = "select count(*) from music where privacy=0 and musicid=:musicid";
                    $this->database->query($query);
                    $this->database->bind("musicid", $musicid);
                    if ($this->database->firstColumn() == "1") {
                        $query = "select name,playlistid,url from playlistname where userid=:userid";
                        $this->database->query($query);
                        $this->database->bind("userid", SessionManagement::getSession("userid"));
                        $playlists = $this->database->resultSet();
                        $playlistInfo = [];
                        foreach ($playlists as $playlist) {
                            $query = "select count(*) from playlist where playlistid=:playlistid and musicid=:musicid";
                            $this->database->query($query);
                            $this->database->bind("playlistid", $playlist["playlistid"]);
                            $this->database->bind("musicid", $musicid);
                            $playlistadded = $this->database->firstColumn() == "0" ? false : true;
                            $playlist["added"] = $playlistadded;
                            $playlist["musicid"] = $musicid;
                            $playlistInfo[] = $playlist;
                        }
                        $result["data"] = $this->getView("playlist/add", ["playlist" => $playlistInfo, "musicid" => $musicid]);
                    } else {
                        $result["data"] = "Unkwnon Error.";
                    }
                }
            } else {
                $result["data"] = "Unkwnon Error";
            }
            $result["music"] = $_POST['info'];
        } else {
            $result["data"] = "Unkwnon Error";
        }

        echo json_encode($result);
    }

    public function create() {
        $result = ["code" => 100, "message" => "Unknown Error"];
        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";

        if (!empty($info)) {
            $info = (array) $info;
            if (!empty($info["playlist"]) && isset($info["privacy"]) && isset($info["music"])) {
                $playlistPrivacy = ($info["privacy"] == "true") ? 0 : 1;
                $playlistName = $info["playlist"];
                $playlistName = trim($playlistName);
                $playlistName = preg_replace("/[^A-Za-z0-9 \-\_]/", '', $playlistName);
                $playlistName = strtolower($playlistName);
                $playlistName = ucwords($playlistName);
                $music = $info["music"];
                $music = (array) $music;
                $musicid = $music["musicid"];

                $query = "select count(*) from music where privacy=0 and musicid=:musicid";
                $this->database->query($query);
                $this->database->bind("musicid", $musicid);
                if ($this->database->firstColumn() == "1") {
                    $query = "select count(*) from playlistname where userid=:userid && name=:playlist";
                    $this->database->query($query);
                    $this->database->bind("userid", SessionManagement::getSession("userid"));
                    $this->database->bind("playlist", $playlistName);
                    $TIME = time();
                    $IS_ERROR = true;
                    if ($this->database->firstColumn() == "0") {
                        $url = strtolower($playlistName);
                        $url = preg_replace("/[^A-Za-z0-9 ]/", '', $url);
                        $url = preg_replace('!\s+!', '-', $url);
                        $url = $url . "-by-" . SessionManagement::getSession("username");
                        $query = "insert into playlistname(userid,name,privacy,time,url) values(:userid,:playlist,:privacy,:time,:url)";
                        $this->database->query($query);
                        $this->database->bind("userid", SessionManagement::getSession("userid"));
                        $this->database->bind("playlist", $playlistName);
                        $this->database->bind("privacy", $playlistPrivacy);
                        $this->database->bind("time", $TIME);
                        $this->database->bind("url", $url);
                        if ($this->database->execute()) {
                            $IS_ERROR = false;
                        } else {
                            $result = ["code" => 102, "message" => "Unknown Error"];
                        }
                    } else {
                        $IS_ERROR = false;
                        //playlist name alreday exixts
                        //Only add to playlist
                    }
                    if (!$IS_ERROR) {
                        $playlistid = 0;
                        $query = "select playlistid from playlistname where userid=:userid && name=:playlist";
                        $this->database->query($query);
                        $this->database->bind("userid", SessionManagement::getSession("userid"));
                        $this->database->bind("playlist", $playlistName);
                        $playlistid = $this->database->firstColumn();

                        $query = "select count(*) from playlist where userid=:userid and playlistid=:playlistid and musicid=:musicid";
                        $this->database->query($query);
                        $this->database->bind("userid", SessionManagement::getSession("userid"));
                        $this->database->bind("playlistid", $playlistid);
                        $this->database->bind("musicid", $musicid);

                        if ($this->database->firstColumn() == "0") {
                            $query = "insert into playlist(userid,playlistid,musicid,time) values(:userid,:playlistid,:musicid,:time)";
                            $this->database->query($query);
                            $this->database->bind("userid", SessionManagement::getSession("userid"));
                            $this->database->bind("playlistid", $playlistid);
                            $this->database->bind("musicid", $musicid);
                            $this->database->bind("time", $TIME);
                            if ($this->database->execute()) {
                                $result = ["code" => 1, "message" => "Added To playlist."];
                            } else {
                                $result = ["code" => 102, "message" => "Unknown Error"];
                            }
                        } else {
                            $result = ["code" => 103, "message" => "Already Added"];
                        }
                    }
                } else {
                    $result = ["code" => 104, "message" => "Unknown Error"];
                }
            } else {
                $result = ["code" => 101, "message" => "Unknown Error"];
            }
        }
        $result["info"] = $info;
        echo json_encode($result);
    }

    public function addtoplaylist() {
        $result = ["code" => 100, "message" => "Unknown Error"];
        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";

        if (!empty($info)) {
            $info = (array) $info;



            $music = $info["music"];
            $music = (array) $music;
            if (isset($music["musicid"]) && isset($info["playlist"])) {

                $musicid = $music["musicid"];
                $playlistName = $info["playlist"];
                $TIME = time();

                $query = "select count(*) from music where privacy=0 and musicid=:musicid";
                $this->database->query($query);
                $this->database->bind("musicid", $musicid);
                if ($this->database->firstColumn() == "1") {


                    $playlistid = 0;
                    $query = "select playlistid from playlistname where userid=:userid && name=:playlist";
                    $this->database->query($query);
                    $this->database->bind("userid", SessionManagement::getSession("userid"));
                    $this->database->bind("playlist", $playlistName);
                    $playlistid = $this->database->firstColumn();

                    $query = "select count(*) from playlist where userid=:userid and playlistid=:playlistid and musicid=:musicid";
                    $this->database->query($query);
                    $this->database->bind("userid", SessionManagement::getSession("userid"));
                    $this->database->bind("playlistid", $playlistid);
                    $this->database->bind("musicid", $musicid);

                    if ($this->database->firstColumn() == "0") {
                        $query = "insert into playlist(userid,playlistid,musicid,time) values(:userid,:playlistid,:musicid,:time)";
                        $this->database->query($query);
                        $this->database->bind("userid", SessionManagement::getSession("userid"));
                        $this->database->bind("playlistid", $playlistid);
                        $this->database->bind("musicid", $musicid);
                        $this->database->bind("time", $TIME);
                        if ($this->database->execute()) {
                            $result = ["code" => 1, "message" => "Added To playlist."];
                        } else {
                            $result = ["code" => 102, "message" => "Unknown Error"];
                        }
                    } else {
                        $result = ["code" => 103, "message" => "Already Added"];
                    }
                }else{
                    $result = ["code" => 104, "message" => "Unknown Error"];
                }
            } else {
                $result = ["code" => 101, "message" => "Unknown Error"];
            }
        }
        $result["info"] = $info;
        echo json_encode($result);
    }

    public function removefromplaylist() {
        $result = ["code" => 100, "message" => "Unknown Error"];
        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";

        if (!empty($info)) {
            $info = (array) $info;



            $music = $info["music"];
            $music = (array) $music;
            if (isset($music["musicid"]) && isset($info["playlist"])) {

                $musicid = $music["musicid"];
                $playlistName = $info["playlist"];



                $playlistid = 0;
                $query = "select playlistid from playlistname where userid=:userid and name=:playlist";
                $this->database->query($query);
                $this->database->bind("userid", SessionManagement::getSession("userid"));
                $this->database->bind("playlist", $playlistName);
                $playlistid = $this->database->firstColumn();

                $query = "select count(*) from playlist where userid=:userid and playlistid=:playlistid and musicid=:musicid";
                $this->database->query($query);
                $this->database->bind("userid", SessionManagement::getSession("userid"));
                $this->database->bind("playlistid", $playlistid);
                $this->database->bind("musicid", $musicid);

                if ($this->database->firstColumn() == "1") {
                    $query = "delete from playlist where userid=:userid and playlistid=:playlistid and musicid=:musicid";
                    $this->database->query($query);
                    $this->database->bind("userid", SessionManagement::getSession("userid"));
                    $this->database->bind("playlistid", $playlistid);
                    $this->database->bind("musicid", $musicid);
                    if ($this->database->execute()) {
                        $result = ["code" => 1, "message" => "Removed from playlist."];
                    } else {
                        $result = ["code" => 102, "message" => "Unknown Error"];
                    }
                } else {
                    $result = ["code" => 103, "message" => "Already Removed"];
                }
            } else {
                $result = ["code" => 101, "message" => "Unknown Error"];
            }
        }
        $result["info"] = $info;
        echo json_encode($result);
    }

    public function delete() {
        $result = ["code" => 100, "message" => "Unknown Error"];
        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";
        if (SessionManagement::sessionExists("userid")) {
            if (!empty($info)) {
                $info = (array) $info;
                if (isset($info["playlist"]) && !empty($info["playlist"])) {
                    $playlistId = $info["playlist"];

                    $query = "select count(*) from playlistname where userid=:userid and playlistid=:playlistid";
                    $this->database->query($query);
                    $this->database->bind("userid", SessionManagement::getSession("userid"));
                    $this->database->bind("playlistid", $playlistId);
                    $playlistcount = $this->database->firstColumn();
                    if ($playlistcount == "1") {
                        $query = "delete from playlist where playlistid=:playlistid";
                        $this->database->query($query);
                        $this->database->bind("playlistid", $playlistId);
                        if ($this->database->execute()) {
                            $query = "delete from playlistname where userid=:userid and playlistid=:playlistid";
                            $this->database->query($query);
                            $this->database->bind("userid", SessionManagement::getSession("userid"));
                            $this->database->bind("playlistid", $playlistId);
                            if ($this->database->execute()) {
                                $result = ["code" => 1, "message" => "Deleted."];
                                $result["playlist"] = $playlistId;
                            } else {
                                $result = ["code" => 103, "message" => "Unknown Error."];
                            }
                        } else {
                            $result = ["code" => 102, "message" => "Unknown Error."];
                        }
                    } else {
                        //Playlist doesn't exists
                        $result = ["code" => 1, "message" => "Deleted."];
                        $result["playlist"] = $playlistId;
                    }
                } else {
                    $result = ["code" => 101, "message" => "Unknown Error."];
                }
            }
        } else {
            $result = ["code" => 104, "message" => "Login first"];
        }
        $result["info"] = $info;
        echo json_encode($result);
    }

    public function changeprivacy() {
        $result = ["code" => 100, "message" => "Unknown Error"];
        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";
        if (SessionManagement::sessionExists("userid")) {
            if (!empty($info)) {
                $info = (array) $info;
                if (isset($info["playlist"], $info["privacy"]) && !empty($info["playlist"]) && !empty($info["privacy"])) {
                    $playlistId = $info["playlist"];
                    $privacy = $info["privacy"];
                    $privacy = strtolower($privacy);
                    $privacy = ($privacy == "public") ? 0 : 1;
                    $query = "select count(*) from playlistname where userid=:userid and playlistid=:playlistid";
                    $this->database->query($query);
                    $this->database->bind("userid", SessionManagement::getSession("userid"));
                    $this->database->bind("playlistid", $playlistId);
                    $playlistcount = $this->database->firstColumn();
                    if ($playlistcount == 1) {
                        $query = "update playlistname set privacy=:privacy and userid=:userid and playlistid=:playlistid";
                        $this->database->query($query);
                        $this->database->bind("userid", SessionManagement::getSession("userid"));
                        $this->database->bind("playlistid", $playlistId);
                        $this->database->bind("privacy", $privacy);
                        if ($this->database->execute()) {
                            $result = ["code" => 1, "message" => "Pivacy changed."];
                            $result["playlist"] = $playlistId;
                        } else {
                            $result = ["code" => 102, "message" => "Unknown Error."];
                        }
                    } else {
                        //Playlist doesn't exists
                        $result = ["code" => 105, "message" => "Unknown Error."];
                    }
                } else {
                    $result = ["code" => 101, "message" => "Unknown Error."];
                }
            }
        } else {
            $result = ["code" => 104, "message" => "Login first"];
        }
        $result["info"] = $info;
        echo json_encode($result);
    }

}
