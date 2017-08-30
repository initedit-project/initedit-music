<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test
 *
 * @author home
 */
class test extends Controller {

    public $baseUploadMusic = BASE_MUSIC_UPLOAD;
//    public $baseUserIpload = "/public/";
    public $tmpUpload = TMP_UPLOAD;
    public $musicPublicUpload = MUSIC_PUBLIC_UPLOAD;
    public $musicPrivateUpload = MUSIC_PRIVATE_UPLOAD;
    public $musicImageThumb = MUSIC_IMAGE_THUMB;
    public $musicImageOriginal = MUSIC_IMAGE_ORIGINAL;
    public $musicWaveform = MUSIC_WAVEFORM;
    public $userProfile = USER_PROFILE;
    public $userCover = USER_COVER;
    public $allowedMusicFile = array("mp3");
    public $allowedImageFile = array("jpg", "jpeg", "png", "JPEG", "JPG", "PNG");

    public function index() {

        $ffmpeg = trim(shell_exec('type -P ffmpeg'));
        if (empty($ffmpeg)) {
            die('ffmpeg not available');
        } else {
            echo "Available";
        }
    }

    public function getid() {
        $filename = "useruploads/music/public/c8f73aaef4945252bf81647177f0dffe.mp3";
        if (function_exists("id3_get_tag")) {
            $info = id3_get_tag("useruploads/music/public/c8f73aaef4945252bf81647177f0dffe.mp3");
        }else{
            $this->loadLib("getid3/getid3.php");
            $getid3 = new getID3();
            $ThisFileInfo = $getID3->analyze($filename);
            $info = $ThisFileInfo;
        }
        print_r($info);
    }

    public function dir() {

//        foreach (glob(BASE_MUSIC_UPLOAD . MUSIC_IMAGE_ORIGINAL . '*.*') as $file) {
//            $f = explode("/", $file);
//            $f = array_pop($f);
//            echo $f . "<br/>";
//            $this->processMusicImageFile($f);
//        }
    }

    public function svg() {
        try {
            $query = "select music,waveimg from music where musicid>64 and musicid<73";
            $this->database->query($query);
            $musicURLs = $this->database->resultSet();
            $musicInfos = [];

            foreach ($musicURLs as $musicURL) {
                $music = $musicURL["music"];
                $waveimg = $musicURL["waveimg"];
                $path = $this->processMusicFile(0, $music);
                echo $this->generateMusicWaveform($path, $waveimg);
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }

    private function generateMusicWaveform($musicpath, $waveimg) {
        $waveimg = $this->baseUploadMusic . $this->musicWaveform . $waveimg;
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

                    $target_svg = $waveimg;

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
                    return $txt;
                }
            } catch (Exception $ex) {
                return "Exception";
            }
        } else {
            return "File Doesn't got created";
        }
        return "Completed";
    }

    private function processMusicFile($privacy, $file) {
        $moveto = "";
        if ($privacy == 0) {
            $moveto = $this->baseUploadMusic . $this->musicPublicUpload;
        } else {
            $moveto = $this->baseUploadMusic . $this->musicPrivateUpload;
        }

        return $moveto . $file;
    }

    private function processMusicImageFile($img) {

        $thumbpath = BASE_MUSIC_UPLOAD . MUSIC_IMAGE_THUMB . $img;
        $originalpath = BASE_MUSIC_UPLOAD . MUSIC_IMAGE_ORIGINAL . $img;
        unlink($thumbpath);

        copy($originalpath, $thumbpath);
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

        $imagick = new \Imagick($file);
        $imagick->scaleImage($newwidth, $newheight, true);
        $imagick->writeImage($file);
        /* $ext = explode(".", $file);
          $ext = array_pop($ext);
          $ext = strtolower($ext);

          if ($ext == "png") {
          $src = imagecreatefrompng($file);
          } else {
          $src = imagecreatefromjpeg($file);
          }
          $dst = imagecreatetruecolor($newwidth, $newheight);
          imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);


          if ($ext == "png") {

          imagepng($dst, $file);
          } else {
          imagejpeg($dst, $file);
          }
         * 
         */
    }

}
