<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MusicHelper
 *
 * @author home
 */
class MusicHelper extends Controller {

    public $musicPublicUpload = "/public/useruploads/music/public/";
    public $musicPrivateUpload = "/public/useruploads/music/private/";
    public $musicImageThumb = "/public/useruploads/image/thumb/";
    public $musicImageOriginal = "/public/useruploads/image/original/";
    public $musicWaveform = "/public/useruploads/waveform/";
    public $userProfile = "/public/useruploads/user/profile/";
    public $userCover = "/public/useruploads/user/cover/";
    public $shortQuery = "SELECT m.musicid, m.userid, m.music, m.img, m.waveimg, m.title, m.time, m.descrption, m.url, m.view, m.privacy,u.username,u.img as userimg, u.cover as usercover   FROM music m,usersignup u  where m.url=:url and m.userid=u.userid";

    public function getByURL($url) {
        $this->database->query($this->shortQuery);
        $this->database->bind("url", $url);

        $musicDetailObject = $this->database->single();

        if ($musicDetailObject["privacy"] == 0) {
            $musicDetailObject["track"]["original"] = $this->musicPublicUpload . $musicDetailObject["music"];
        } else {
            $musicDetailObject["track"]["original"] = $this->musicPrivateUpload . $musicDetailObject["music"];
        }
        $musicDetailObject["image"]["thumb"] = $this->musicImageThumb . $musicDetailObject["img"];
        $musicDetailObject["image"]["original"] = $this->musicImageOriginal . $musicDetailObject["img"];
        $musicDetailObject["image"]["waveform"] = $this->musicWaveform . $musicDetailObject["waveimg"];
        $musicDetailObject["image"]["waveform"] = "/public/useruploads/waveform/c8f73aaef4945252bf81647177f0dffe.svg";
        $musicDetailObject["user"]["name"] = $musicDetailObject["username"];
        $musicDetailObject["user"]["profileurl"] = "/user/" . $musicDetailObject["username"];
        $musicDetailObject["user"]["image"] = $this->userProfile . $musicDetailObject["userimg"];
        $musicDetailObject["user"]["cover"] = $this->userCover . $musicDetailObject["usercover"];

        $musicDetailObject["musicurl"] = "/music/" . $musicDetailObject["url"];
        $musicDetailObject["description"] = $musicDetailObject["descrption"];
        
        unset($musicDetailObject["descrption"]);
        unset($musicDetailObject["img"]);
        unset($musicDetailObject["waveimg"]);
        unset($musicDetailObject["music"]);
        unset($musicDetailObject["url"]);

        return $musicDetailObject;
    }

}
