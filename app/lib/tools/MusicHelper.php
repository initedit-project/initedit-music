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

    public $musicPublicUpload = MUSIC_PUBLIC_UPLOAD;
    public $musicPrivateUpload = MUSIC_PRIVATE_UPLOAD;
    public $musicImageThumb = MUSIC_IMAGE_THUMB;
    public $musicImageOriginal =MUSIC_IMAGE_ORIGINAL ;
    public $musicWaveform = MUSIC_WAVEFORM;
    public $userProfile = USER_PROFILE;
    public $userCover = USER_COVER;
    public $shortQuery = "SELECT m.musicid, m.userid, m.music, m.img, m.waveimg, m.title, m.time, m.descrption, m.url, m.view, m.privacy,u.username,u.img as userimg, u.cover as usercover   FROM music m,usersignup u  where m.url=:url and m.userid=u.userid";

    public function getByURL($url) {
        $this->database->query($this->shortQuery);
        $this->database->bind("url", $url);

        $musicDetailObject = $this->database->single();

        if ($musicDetailObject["privacy"] == 0) {
            $musicDetailObject["track"]["original"] = SITE_URL . $this->musicPublicUpload . $musicDetailObject["music"];
        } else {
            $musicDetailObject["track"]["original"] = SITE_URL . $this->musicPrivateUpload . $musicDetailObject["music"];
        }
        $musicDetailObject["image"]["thumb"] = SITE_URL . $this->musicImageThumb . $musicDetailObject["img"];
        $musicDetailObject["image"]["original"] = SITE_URL . $this->musicImageOriginal . $musicDetailObject["img"];
        $musicDetailObject["image"]["waveform"] = SITE_URL . $this->musicWaveform . $musicDetailObject["waveimg"];
//        $musicDetailObject["image"]["waveform"] = SITE_URL . "/public/useruploads/waveform/c8f73aaef4945252bf81647177f0dffe.svg";
        $musicDetailObject["user"]["name"] = $musicDetailObject["username"];
        $musicDetailObject["user"]["profileurl"] = SITE_URL . "/user/" . $musicDetailObject["username"];
        $musicDetailObject["user"]["image"] = SITE_URL . $this->userProfile . $musicDetailObject["userimg"];
        $musicDetailObject["user"]["cover"] = SITE_URL . $this->userCover . $musicDetailObject["usercover"];

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
