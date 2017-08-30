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
class playlist extends Controller {

    public function index($PLAYLIST = "", $PAGE_NO = 1) {
        $result = array();
        $result["status"] = 404;
        $result["title"] = "Page Was not found.";
        $result["message"] = "Resource that you are trying to access is not available or you don't have persmission.";
        if (empty($PLAYLIST)) {
            
        } else {

            $result["status"] = 200;
            if ($this->playlistExists($PLAYLIST)) {
                if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
                    $result["messsage"] = "Bas Parameter.";
                } else {
                    
                    $playlistInfo = $this->getPlaylistByURL($PLAYLIST);

                    $result["playlist"] = $playlistInfo;

                    $query = "select count(*) from playlist where musicid not in (select musicid from music where privacy<>0) and playlistid=:playlistid";

                    $this->database->query($query);
                    $this->database->bind("playlistid", $playlistInfo["playlistid"]);
                    $totalCount = $this->database->firstColumn();
                    $pagingInfo = array();
                    $pagingInfo["current"] = $PAGE_NO;
                    $pagingInfo["musicPerPage"] = $this->musicPerPage;
                    $pagingInfo["totalMusic"] = $totalCount;
                    $pagingInfo["base"] = "/api/" . App::$API_VERSION . "/home/trending/";

                    if ($totalCount < ($PAGE_NO - 1) * $this->musicPerPage) {
                        $result["status"] = 422;
                        $result["title"] = "Error";
                        $result["message"] = "Bas Parameter.";
                    } else {
                        $result["title"] = "Playlist";
                        $result["message"] = "Playlist Music";
                        $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                        $query = "select url from music where privacy=0 and musicid in (select musicid from playlist where playlistid=:playlistid) order by view desc limit $startFrom," . $this->musicPerPage;
                        $this->database->query($query);
                        $this->database->bind("playlistid", $playlistInfo["playlistid"]);
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
            } else {
                $result["message"] = "Playlist Was not found.";
                $result["description"] = "Resource that you are trying to access is not available or you don't have persmission.";
            }
        }
        echo json_encode($result);
    }

    private function playlistExists($url) {
        $this->database->query("select count(*) from playlistname where url=:url and privacy=0");
        $this->database->bind("url", $url);
        return ($this->database->firstColumn() == "0") ? false : true;
    }

    private function getPlaylistByURL($url) {
        $this->database->query("select playlistid,name from playlistname where url=:url");
        $this->database->bind("url", $url);
        return ($this->database->single());
    }

}
