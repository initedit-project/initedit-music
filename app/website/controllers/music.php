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

    public function index($orginalurl = "") {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Welcome To Initedit Music";
        $result["description"] = "This is Home Desription";

        $query = "select count(*) from music where url=:url";
        $this->database->query($query);

        $this->database->bind("url", $orginalurl);
        if ($this->database->firstColumn() == "1") {

            $query = "select url from music where privacy=0 order by musicid desc limit 18";
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
            $music = $m->getByURL($orginalurl);

            $result["music"] = $music;
            $isLiked = false;
            if (SessionManagement::sessionExists("userid")) {
                $query = "select count(*) from music_like where userid=:userid and musicid=:musicid";
                $this->database->query($query);
                $this->database->bind("userid", SessionManagement::getSession("userid"));
                $this->database->bind("musicid", $music["musicid"]);
                if ($this->database->firstColumn() == "1") {
                    $isLiked = true;
                }
            }
            $query = "select tagname from tag where musicid=:musicid";
            $this->database->query($query);
            $this->database->bind("musicid", $music["musicid"]);
            $tagString = $this->database->firstColumn();
            $tag = $tagString;
            $tag = "#" . $tag;
            $tag = preg_replace("![^a-z0-9]+!i", " #", $tag);
            $tag = preg_replace('/(?<!\S)#([0-9a-zA-Z]+)/', '<a href="/hash/$1">#$1</a>', $tag);
            $timeAgo = TimeHelper::time_elapsed_string($music["time"]);
            $result["data"] = $this->getView("music/full", ["music" => $music, "liked" => $isLiked, "time" => $timeAgo, "tag" => $tag]);
            $result["isFullScreenMusic"] = true;
            $result["title"] = $music["title"];
        } else {
            $result["data"] = $this->getView("common/error");
        }
        echo json_encode($result);
    }

    public function edit($orginalurl = "") {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Edit Music";
        $result["description"] = "This is Edit Page Desription";

        $userid = SessionManagement::sessionExists("userid") ? SessionManagement::getSession("userid") : -1;
        $query = "select count(*) from music where url=:url and userid=:userid";
        $this->database->query($query);
        $this->database->bind("userid", $userid);
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
//            $result["title"] = $orginalurl;
        } else {
            $result["data"] = $this->getView("common/error");
            $result["title"] = "Sorry! Page Not Found";
        }
        echo json_encode($result);
    }

    public function like() {
        $result = ["code" => 100, "message" => "Unknown Error"];
        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";

        if (SessionManagement::sessionExists("userid")) {
            if (!empty($info)) {
                $info = (array) $info;
                $music = $info["music"];
                $music = (array) $music;
                if (isset($music["musicid"])) {
                    $musicid = $music["musicid"];

                    $query = "select count(*) from music_like where userid=:userid and musicid=:musicid";
                    $this->database->query($query);
                    $this->database->bind("userid", SessionManagement::getSession("userid"));

                    $this->database->bind("musicid", $musicid);
                    if ($this->database->firstColumn() == "0") {
                        $TIME = time();
                        $query = "insert into music_like(userid,musicid,time) values(:userid,:musicid,:time)";
                        $this->database->query($query);
                        $this->database->bind("userid", SessionManagement::getSession("userid"));

                        $this->database->bind("musicid", $musicid);
                        $this->database->bind("time", $TIME);
                        if ($this->database->execute()) {
                            $result = ["code" => 1, "message" => "Liked"];
                        } else {
                            $result = ["code" => 102, "message" => "Unknown Error."];
                        }
                    } else {
                        $result = ["code" => 1, "message" => "Liked"];
                    }
                } else {
                    $result = ["code" => 101, "message" => "Unknown Error"];
                }
            }
        } else {
            $result = ["code" => 103, "message" => "Login First"];
        }
        $result["info"] = $info;
        echo json_encode($result);
    }

    public function unlike() {
        $result = ["code" => 100, "message" => "Unknown Error"];
        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";
        if (SessionManagement::sessionExists("userid")) {
            if (!empty($info)) {
                $info = (array) $info;
                $music = $info["music"];
                $music = (array) $music;
                if (isset($music["musicid"])) {
                    $musicid = $music["musicid"];

                    $query = "select count(*) from music_like where userid=:userid && musicid=:musicid";
                    $this->database->query($query);
                    $this->database->bind("userid", SessionManagement::getSession("userid"));

                    $this->database->bind("musicid", $musicid);
                    if ($this->database->firstColumn() == "1") {

                        $query = "delete from music_like where userid=:userid and musicid=:musicid";
                        $this->database->query($query);
                        $this->database->bind("userid", SessionManagement::getSession("userid"));

                        $this->database->bind("musicid", $musicid);

                        if ($this->database->execute()) {
                            $result = ["code" => 1, "message" => "Unliked"];
                        } else {
                            $result = ["code" => 102, "message" => "Unknown Error."];
                        }
                    } else {
                        $result = ["code" => 1, "message" => "Unliked"];
                    }
                } else {
                    $result = ["code" => 101, "message" => "Unknown Error"];
                }
            }
        } else {
            $result = ["code" => 103, "message" => "Login First"];
        }
        $result["info"] = $info;
        echo json_encode($result);
    }

    public function viewCount() {
        $result = ["code" => 100, "message" => "Unknown Error"];
        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";

        if (!empty($info)) {
            $info = (array) $info;
            $music = $info["music"];
            $music = (array) $music;
            if (isset($music["musicid"])) {
                $musicid = $music["musicid"];

                $query = "update music set view = view + 1 where musicid=:musicid";
                $this->database->query($query);
                $this->database->bind("musicid", $musicid);
                if ($this->database->execute()) {
                    $result = ["code" => 1, "message" => "Synched"];
                } else {
                    $result = ["code" => 102, "message" => "Unknown Error."];
                }
            } else {
                $result = ["code" => 101, "message" => "Unknown Error"];
            }
        }
        $result["info"] = $info;
        echo json_encode($result);
    }
}
