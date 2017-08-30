<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of setting
 *
 * @author home
 */
class setting extends Controller {

    public function index() {
        $result = array();
        $result["status"] = 200;
        $result["page"] = 1;
        $result["title"] = "Admin Welcome To Initedit Music";
        $result["description"] = "This is Home Desription";


        $result["data"] = $this->getView("setting/general");
        echo json_encode($result);
    }

    public function updateSiteName() {
        $result = ["code" => 100, "message" => "Unknown Error."];
        if (SessionManagement::sessionExists("adminuserid")) {
            if (isset($_POST['extra'])) {
                $extra = json_decode($_POST['extra']);
                $extra = (array) $extra;
                if (isset($extra["name"]) &&
                        isset($extra["csrf"]) &&
                        SessionManagement::sessionExists("csrf")) {
                    if (SessionManagement::getSession("csrf") == $extra["csrf"]) {
                        $name = $extra["name"];
                        if (!empty($name)) {
                            $VAR_PATH = VAR_PATH;
                            $result = ["code" => 1, "message" => "Updated Site Name."];
                            if (file_exists($VAR_PATH)) {
                                $result["info"] = $this->updateVarSiteName($VAR_PATH, "SITE_NAME", $name);
                            } else {
                                $result["message"] = "File not found. ->" . VAR_PATH;
                            }
                            $result["refresh"] = true;
                        } else {
                            $result = ["code" => 108, "message" => "Site Name Must Not Be Empty."];
                        }
                    } else {
                        $result = ["code" => 107, "message" => "Permission Denied."];
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

    public function updateHelpEmail() {
        $result = ["code" => 100, "message" => "Unknown Error."];
        if (SessionManagement::sessionExists("adminuserid")) {
            if (isset($_POST['extra'])) {
                $extra = json_decode($_POST['extra']);
                $extra = (array) $extra;
                if (isset($extra["email"]) &&
                        isset($extra["csrf"]) &&
                        SessionManagement::sessionExists("csrf")) {
                    if (SessionManagement::getSession("csrf") == $extra["csrf"]) {
                        $email = $extra["email"];
                        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $VAR_PATH = VAR_PATH;
                            $result = ["code" => 1, "message" => "Updated Site Email."];
                            if (file_exists($VAR_PATH)) {
                                $result["info"] = $this->updateVarSiteName($VAR_PATH, "HELP_MAIL", $email);
                            } else {
                                $result["message"] = "File not found. ->" . VAR_PATH;
                            }
                            $result["refresh"] = true;
                        } else {
                            $result = ["code" => 108, "message" => "Email is not Valid."];
                        }
                    } else {
                        $result = ["code" => 107, "message" => "Permission Denied."];
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

    private function updateVarSiteName($f, $key, $val) {
        $result = "";

        $key_define = 'define("' . $key . '",';
        $file = fopen($f, "r+");

        while (!feof($file)) {
            $LINE = fgets($file);
            if ($this->startsWith($LINE, $key_define)) {
                $LINE = 'define("' . $key . '", "' . $val . '", true);' . "\n";
            }
            $result[] = $LINE;
        }
        fclose($file);
        $file = fopen($f, "w+");
        $res = implode($result, "");
        fwrite($file, $res);
        fclose($file);
        return $result;
    }

    private function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

}
