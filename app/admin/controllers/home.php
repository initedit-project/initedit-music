<?php

//http://culttt.com/2012/10/01/roll-your-own-pdo-php-class/
class home extends Controller {

    public function index($PAGE = "", $PAGE_NO = 1) {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Admin Welcome To Initedit Music";
        $result["description"] = "This is Home Desription";

        $homeInfo = array();

        $query = "select count(*) from music";
        $this->database->query($query);

        //$homeInfo["totalmusic"] = $this->database->firstColumn();
        
        $homeInfo[] = ["key"=>"Total Music","value"=>$this->database->firstColumn()];
        
        $query = "select count(*) from usersignup";
        $this->database->query($query);

       $homeInfo[] = ["key"=>"Total Users","value"=>$this->database->firstColumn()];

        $diskInfo = ($this->getDirectorySize("./../../public/useruploads"));
     
        $homeInfo[] = ["key"=>"User Uploaded Content","value"=>$this->getSymbolByQuantity($diskInfo["size"])];

        $homeInfo[] = ["key"=>"Total Available Size","value"=>$this->getSymbolByQuantity(disk_free_space("/"))];
        $homeInfo[] = ["key"=>"Total Size","value"=>$this->getSymbolByQuantity(disk_total_space("/"))];
        
        $result["data"] = $this->getView("home/home", ["home" => $homeInfo]);
        echo json_encode($result);
    }

    private function getSymbolByQuantity($bytes) {
        $symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $exp = floor(log($bytes) / log(1024));

        return sprintf('%.2f ' . $symbols[$exp], ($bytes / pow(1024, floor($exp))));
    }

    private function getDirectorySize($path) {
        $totalsize = 0;
        $totalcount = 0;
        $dircount = 0;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link($nextpath)) {
                    if (is_dir($nextpath)) {
                        $dircount++;
                        $result = $this->getDirectorySize($nextpath);
                        $totalsize += $result['size'];
                        $totalcount += $result['count'];
                        $dircount += $result['dircount'];
                    } elseif (is_file($nextpath)) {
                        $totalsize += filesize($nextpath);
                        $totalcount++;
                    }
                }
            }
        }
        closedir($handle);
        $total['size'] = $totalsize;
        $total['count'] = $totalcount;
        $total['dircount'] = $dircount;
        return $total;
    }

}
