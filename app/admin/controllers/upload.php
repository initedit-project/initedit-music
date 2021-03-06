<?php

class upload extends Controller {

    public $baseUploadMusic = "../../public/";
    public $baseUserIpload = "/public/";
    public $tmpUpload = "tmp/";
    public $musicPublicUpload = "useruploads/music/public/";
    public $musicPrivateUpload = "useruploads/music/private/";
    public $musicImageThumb = "useruploads/image/thumb/";
    public $musicImageOriginal = "useruploads/image/original/";
    public $musicWaveform = "useruploads/waveform/";
    public $userProfile = "useruploads/user/profile/";
    public $userCover = "useruploads/user/cover/";
    public $allowedMusicFile = array("mp3");
    public $allowedImageFile = array("jpg", "jpeg", "png", "JPEG", "JPG", "PNG");

    private function createdir() {
        if (!file_exists($this->baseUploadMusic . $this->tmpUpload)) {
            mkdir($this->baseUploadMusic . $this->tmpUpload, 0744);
        }
        if (!file_exists($this->baseUploadMusic . $this->musicPublicUpload)) {
            mkdir($this->baseUploadMusic . $this->musicPublicUpload, 0744, true);
        }
        if (!file_exists($this->baseUploadMusic . $this->musicPrivateUpload)) {
            mkdir($this->baseUploadMusic . $this->musicPrivateUpload, 0744, true);
        }
        if (!file_exists($this->baseUploadMusic . $this->musicImageThumb)) {
            mkdir($this->baseUploadMusic . $this->musicImageThumb, 0744, true);
        }
        if (!file_exists($this->baseUploadMusic . $this->musicImageOriginal)) {
            mkdir($this->baseUploadMusic . $this->musicImageOriginal, 0744, true);
        }
        if (!file_exists($this->baseUploadMusic . $this->musicWaveform)) {
            mkdir($this->baseUploadMusic . $this->musicWaveform, 0744, true);
        }
        if (!file_exists($this->baseUploadMusic . $this->userProfile)) {
            mkdir($this->baseUploadMusic . $this->userProfile, 0744, true);
        }
        if (!file_exists($this->baseUploadMusic . $this->userProfile)) {
            mkdir($this->baseUploadMusic . $this->userProfile, 0744, true);
        }
    }

    public function userprofile() {
        $result = ["code" => 100, "message" => "Unknown Error."];
        $this->createdir();
        if (SessionManagement::sessionExists("userid")) {
            if (isset($_FILES['image'])) {
                $INSERT_FILE_NAME = $_FILES["image"]["name"];
                $explodes = explode(".", $_FILES["image"]["name"]);
                $exten = array_pop($explodes);
                if (!in_array($exten, $this->allowedImageFile)) {
                    $result = array("status" => 3, "message" => "Image File Format Is  Not Support");
                } else {
                    $extension = "." . $exten;
                    $random_number = md5(time() . "MUSIC");
                    $random_file_name = $random_number . $extension;
                    $target_file = $this->baseUploadMusic . $this->userProfile . $random_file_name;
                    while (file_exists($target_file)) {
                        $random_number = md5(time() . rand(0, 300) . "MUSIC");
                        $random_file_name = $random_number . $extension;
                        $target_file = $this->baseUploadMusic . $this->userProfile . $random_file_name;
                    }

                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $this->resize_image($target_file);
                        $query = "update usersignup set img=:img where userid=:userid";

                        $this->database->query($query);
                        $this->database->bind("userid", SessionManagement::getSession("userid"));

                        $this->database->bind("img", $random_file_name);

                        if ($this->database->execute()) {
                            $result = ["code" => 1, "message" => "Uploaded Successfully."];
                            $result["image"] = $target_file;
                        } else {
                            $result = ["code" => 106, "message" => "Unknwon Error."];
                        }
                    } else {
                        $result = ["code" => 104, "message" => "Unable to save file."];
                    }
                }
            } else {
                $result = ["code" => 101, "message" => "Unknown Error."];
            }
        } else {
            $result = ["code" => 105, "message" => "Login first."];
        }

        echo json_encode($result);
    }

    public function usercover() {
        $result = ["code" => 100, "message" => "Unknown Error."];
        $this->createdir();
        if (SessionManagement::sessionExists("userid")) {
            if (isset($_FILES['image'])) {
                $INSERT_FILE_NAME = $_FILES["image"]["name"];
                $explodes = explode(".", $_FILES["image"]["name"]);
                $exten = array_pop($explodes);
                if (!in_array($exten, $this->allowedImageFile)) {
                    $result = array("status" => 3, "message" => "Image File Format Is  Not Support");
                } else {
                    $extension = "." . $exten;
                    $random_number = md5(time() . "MUSIC");
                    $random_file_name = $random_number . $extension;
                    $target_file = $this->baseUploadMusic . $this->userCover . $random_file_name;
                    while (file_exists($target_file)) {
                        $random_number = md5(time() . rand(0, 300) . "MUSIC");
                        $random_file_name = $random_number . $extension;
                        $target_file = $this->baseUploadMusic . $this->userCover . $random_file_name;
                    }

                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
//                        $this->resize_image($target_file);
                        $query = "update usersignup set cover=:cover where userid=:userid";

                        $this->database->query($query);
                        $this->database->bind("userid", SessionManagement::getSession("userid"));

                        $this->database->bind("cover", $random_file_name);

                        if ($this->database->execute()) {
                            $result = ["code" => 1, "message" => "Uploaded Successfully."];
                            $result["image"] = $target_file;
                        } else {
                            $result = ["code" => 106, "message" => "Unknwon Error."];
                        }
                    } else {
                        $result = ["code" => 104, "message" => "Unable to save file."];
                    }
                }
            } else {
                $result = ["code" => 101, "message" => "Unknown Error."];
            }
        } else {
            $result = ["code" => 105, "message" => "Login first."];
        }

        echo json_encode($result);
    }

    public function editmusic() {
        $result = ["code" => 100, "message" => "Unknown Error."];
        $this->createdir();
        if (SessionManagement::sessionExists("adminuserid")) {
            if (isset($_POST['extra'])) {
                $extra = json_decode($_POST['extra']);
                $extra = (array) $extra;
                if (isset($extra["title"])) {
                    $imageSelected = $extra["imageSelected"];
                    $imageerror = true;
                    $random = md5(time() . "MUSIC");
                    $img = "";
                    if ($imageSelected && isset($_FILES["image"]["tmp_name"])) {
                        $explodes = explode(".", $_FILES["image"]["name"]);
                        $exten = array_pop($explodes);
                        if (!in_array($exten, $this->allowedImageFile)) {
                            $result = array("status" => 3, "message" => "Image File Format Is  Not Support");
                        } else {
                            $extension = "." . $exten;
                            $random_number = $random;
                            $random_file_name = $random_number . $extension;
                            $target_file = $this->baseUploadMusic . $this->tmpUpload . $random_file_name;
                            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                                $imageerror = false;
                                $img = $random_file_name;
                            } else {
                                $result = ["code" => 104, "message" => "Unable to save file."];
                            }
                        }
                    } else {
                        $imageerror = false;
                    }
                    if (!$imageerror) {
                        $title = $extra['title'];

                        $tag = $extra['tag'];
                        $description = $extra['description'];
                        $privacy = $extra['privacy'];
                        $musicid = $extra['musicid'];
                        $privacy = strtolower($privacy);
                        $privacy = ($privacy) ? 0 : 1;

                        $query = "select count(*) from music  where musicid=:musicid";

                        $this->database->query($query);

                        $this->database->bind("musicid", $musicid);
                        $count = $this->database->firstColumn();

                        if ($count == "1") {
                            $query = "update music set title=:title,descrption=:descrption,privacy=:privacy where musicid=:musicid";
                            $this->database->query($query);
                            $this->database->bind("title", $title);
                            $this->database->bind("descrption", $description);
                            $this->database->bind("musicid", $musicid);
                            $this->database->bind("privacy", $privacy);
                            if ($this->database->execute()) {
                                if ($imageSelected && !empty($img)) {

                                    $query = "select img from music  where musicid=:musicid";

                                    $this->database->query($query);
                                    $this->database->bind("musicid", $musicid);
                                    $musicImage = $this->database->firstColumn();

                                    $thumbpath = $this->baseUploadMusic . $this->musicImageThumb . $musicImage;
                                    $originalpath = $this->baseUploadMusic . $this->musicImageOriginal . $musicImage;
                                    if (file_exists($thumbpath)) {
                                        unlink($thumbpath);
                                    }
                                    if (file_exists($originalpath)) {
                                        unlink($originalpath);
                                    }

                                    $query = "update music set img=:img where musicid=:musicid";
                                    $this->database->query($query);

                                    $this->database->bind("musicid", $musicid);
                                    $this->database->bind("img", $img);
                                    $this->database->execute();
                                    $this->processMusicImageFile($img);
                                }

                                $query = "select music from music  where musicid=:musicid";

                                $this->database->query($query);
                                $this->database->bind("musicid", $musicid);
                                $musicURLName = $this->database->firstColumn();


                                $srcPath = $this->baseUploadMusic;
                                $dstPath = $this->baseUploadMusic;
                                if ($privacy == 0) {
                                    $srcPath .= $this->musicPrivateUpload;
                                    $dstPath .= $this->musicPublicUpload;
                                } else {
                                    $srcPath .= $this->musicPublicUpload;
                                    $dstPath .= $this->musicPrivateUpload;
                                }
                                $srcPath.=$musicURLName;
                                $dstPath.=$musicURLName;
                                if (file_exists($srcPath)) {
                                    rename($srcPath, $dstPath);
                                }

                                $query = "update tag set tagname=:tag where musicid=:musicid";
                                $this->database->query($query);
                                $this->database->bind("musicid", $musicid);
                                $this->database->bind("tag", $tag);
                                $this->database->execute();
                                $result = ["code" => 1, "message" => "Edited Successfully."];
                            } else {
                                $result = ["code" => 107, "message" => "Unknown Error."];
                            }
                        } else {
                            $result = ["code" => 108, "message" => "Permission Denied."];
                        }
                    }
                } else {
                    $result = ["code" => 102, "message" => "Unknown Error."];
                }
            } else {
                $result = ["code" => 101, "message" => "Unknown Error."];
            }
        } else {
            $result = ["code" => 106, "message" => "Login First."];
        }
        $result['extra'] = json_decode($_POST['extra']);

        echo json_encode($result);
    }

    public function deletemusic() {
        $result = ["code" => 100, "message" => "Unknown Error."];
        $this->createdir();
        if (SessionManagement::sessionExists("adminuserid")) {
            if (isset($_POST['extra'])) {
                $extra = json_decode($_POST['extra']);
                $extra = (array) $extra;
                if (isset($extra["musicid"])) {
                    if (true) {
                        $musicid = $extra['musicid'];
                        $query = "select count(*) from music  where musicid=:musicid";
                        $this->database->query($query);
                        $this->database->bind("musicid", $musicid);
                        $count = $this->database->firstColumn();
                        if ($count == "1") {

                            $query = "select music,privacy,img,waveimg from music  where musicid=:musicid";
                            $this->database->query($query);
                            $this->database->bind("musicid", $musicid);
                            $musicInfo = $this->database->single();

                            $query = "delete from music where musicid=:musicid";
                            $this->database->query($query);

                            $this->database->bind("musicid", $musicid);
                            if ($this->database->execute()) {
                                $privacy = $musicInfo["privacy"];
                                $musicURLName = $musicInfo["music"];
                                $img = $musicInfo["img"];
                                $waveimg = $musicInfo["waveimg"];

                                $srcPath = $this->baseUploadMusic;
                                $dstPath = $this->baseUploadMusic;
                                if ($privacy == 0) {
                                    $srcPath .= $this->musicPrivateUpload;
                                } else {
                                    $srcPath .= $this->musicPublicUpload;
                                }
                                $srcPath.=$musicURLName;
                                if (file_exists($srcPath)) {
                                    unlink($srcPath);
                                }
                                $imgThumb = $this->baseUploadMusic . $this->musicImageThumb . $img;
                                $imgOriginal = $this->baseUploadMusic . $this->musicImageOriginal . $img;
                                if (file_exists($imgThumb)) {
                                    unlink($imgThumb);
                                }
                                if (file_exists($imgOriginal)) {
                                    unlink($imgOriginal);
                                }
                                $imgWave = $this->baseUploadMusic . $this->musicWaveform . $waveimg;
                                if (file_exists($imgWave)) {
                                    unlink($imgWave);
                                }
                                $query = "delete from tag where musicid=:musicid";
                                $this->database->query($query);
                                $this->database->bind("musicid", $musicid);
                                $this->database->execute();

                                $query = "delete from music_like where musicid=:musicid";
                                $this->database->query($query);
                                $this->database->bind("musicid", $musicid);
                                $this->database->execute();

                                $query = "delete from playlist where musicid=:musicid";
                                $this->database->query($query);
                                $this->database->bind("musicid", $musicid);
                                $this->database->execute();

                                $nextPage = "/admin/music/all/";
                                $result = ["code" => 1, "message" => "Deleted Successfully.", "nextpage" => $nextPage];
                            } else {
                                $result = ["code" => 107, "message" => "Unknown Error."];
                            }
                        } else {
                            $result = ["code" => 108, "message" => "Permission Denied."];
                        }
                    }
                } else {
                    $result = ["code" => 102, "message" => "Unknown Error."];
                }
            } else {
                $result = ["code" => 101, "message" => "Unknown Error."];
            }
        } else {
            $result = ["code" => 106, "message" => "Login First."];
        }
        echo json_encode($result);
    }

    private function processMusicFile($privacy, $file) {
        $moveto = "";
        if ($privacy == 0) {
            $moveto = $this->baseUploadMusic . $this->musicPublicUpload;
        } else {
            $moveto = $this->baseUploadMusic . $this->musicPrivateUpload;
        }
        rename($this->baseUploadMusic . $this->tmpUpload . $file, $moveto . $file);
        return $moveto;
    }

    private function processMusicImageFile($img) {
        $imagepath = $this->baseUploadMusic . $this->tmpUpload . $img;
        $thumbpath = $this->baseUploadMusic . $this->musicImageThumb . $img;
        $originalpath = $this->baseUploadMusic . $this->musicImageOriginal . $img;
        copy($imagepath, $thumbpath);
        copy($imagepath, $originalpath);
        unlink($imagepath);
        $this->resize_image($thumbpath);
    }

    private function resize_image($file) {
        list($width, $height) = getimagesize($file);
        $w = 300;
        $h = 300;
        $r = $width / $height;

        if ($w / $h > $r) {
            $newwidth = $h * $r;
            $newheight = $h;
        } else {
            $newheight = $w / $r;
            $newwidth = $w;
        }

        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        imagejpeg($dst, $file);
    }

    private function generateMusicWaveform($musicpath, $waveimg) {
        $waveimg = $this->baseUploadMusic . $this->musicImageOriginal . $waveimg;
        $target_wave = $this->baseUploadMusic . $this->tmpUpload . "wave_" . time() . rand(0, 300) . ".wav";
        exec("ffmpeg -i " . $musicpath . " -acodec pcm_s16le $target_wave");
        if (file_exists($target_wave)) {
            try {
                $this->loadLib("waveform/classAudioFile.php");
                $AF = new AudioFile;
                $AF->loadFile($target_wave);
                if ($AF->wave_id == "RIFF") {
                    $AF->visual_width = 800;
                    $AF->visual_height = 100;
                    $WAVE_PATH = substr("$target_wave", 0, strlen("$target_wave") - 4) . ".png";
                    $AF->getVisualization($WAVE_PATH);

                    //Genrate Wave SVG file
                    $f = fopen($target_svg, "w");
                    $data = $AF->data;

                    $txt = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $AF->visual_width . '" height="' . $AF->visual_height . '">';
                    $txt .= "<style>";
                    $txt .= "line{stroke:#CCC;stroke-width:1.5}";
                    $txt .= "</style>";
                    $COUNT_POINT = count($data);
                    $MAX_NUMBER_OF_POINT = 180;
                    $SKIP_COUNT = floor($COUNT_POINT / $MAX_NUMBER_OF_POINT);

                    for ($i = 0; $i < $COUNT_POINT; $i++) {

                        if ($i % $SKIP_COUNT != ($SKIP_COUNT - 1)) {
                            continue;
                        }
                        $y1_4 = $AF->visual_height / 4;
                        $y1 = $AF->visual_height - $y1_4;
                        $y2 = $data[$i];
                        $y2_4 = ($AF->visual_height - $y2) / 4;
                        $y2 = $y2 - $y2_4;


                        $y1_4 = $y1 + 1;
                        $y2_4 +=$y1_4;

                        $y2 = number_format((float) $y2, 1, '.', '');
                        $y2_4 = number_format((float) $y2_4, 1, '.', '');
                        $y2 = round($y2);
                        $y2_4 = round($y2_4);
                        $txt .= '<line x1="' . $i . '" y1="' . $y2 . '" x2="' . $i . '" y2="' . $y2_4 . '"/>';
                    }
                    $txt .= '<line x1="' . 0 . '" y1="' . $y1_4 . '" x2="' . $AF->visual_width . '" y2="' . $y1_4 . '" style="stroke:#FFF;stroke-width:1;"/>';
                    $txt.="</svg>";
                    fwrite($f, $txt);
                    fclose($f);
                    unlink($target_wave);
                }
            } catch (Exception $ex) {
                
            }
        }
    }

}
