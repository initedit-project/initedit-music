<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of hash
 *
 * @author home
 */
class hash extends Controller {

    public function index($HASH = "") {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Welcome To Hash Tags";
        $result["description"] = "This is Hash Desription";
        $PAGE_NO = empty($PAGE_NO) ? 1 : $PAGE_NO;
        if (!empty($HASH)) {
            if (!is_numeric($PAGE_NO) || $PAGE_NO < 0) {
                $result["data"] = $this->getView("common/error");
            } else {

                $query = "select count(*) from tag where tagname like :tagname or tagname like :starttag or tagname like :endtag or tagname=:tagnamedirect";
                $this->database->query($query);
                $this->database->bind("tagname", "%_" . $HASH . "_%");
                $this->database->bind("starttag", $HASH . " %");
                $this->database->bind("endtag", "% " . $HASH);
                $this->database->bind("tagnamedirect", $HASH);
                $hashCount = $this->database->firstColumn();
                if ($hashCount != "0") {

                    $query = "select count(*) from tag where tagname like :tagname or tagname like :starttag or tagname like :endtag or tagname=:tagnamedirect";
                    $this->database->query($query);
                    $this->database->bind("tagname", "%_" . $HASH . "_%");
                    $this->database->bind("starttag", $HASH . " %");
                    $this->database->bind("endtag", "% " . $HASH);
                    $this->database->bind("tagnamedirect", $HASH);
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
                        $result["data"] = $this->getView("hash/single", ["paging" => $paging, "hash" => $HASH]);
                        $startFrom = ($PAGE_NO - 1) * $this->musicPerPage;
                        $query = "select url from music where privacy=0 and musicid in (select musicid from tag where tagname like :tagname or tagname like :starttag or tagname like :endtag or tagname=:tagnamedirect) order by musicid desc limit $startFrom," . $this->musicPerPage;
                        $this->database->query($query);
                        $this->database->bind("tagname", "%_" . $HASH . "_%");
                        $this->database->bind("starttag", $HASH . " %");
                        $this->database->bind("endtag", "% " . $HASH);
                        $this->database->bind("tagnamedirect", $HASH);
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
                } else {
                    $result["data"] = $this->getView("common/error");
                }
            }
        } else {
            $result["data"] = $this->getView("common/error");
        }
        echo json_encode($result);
    }

}
