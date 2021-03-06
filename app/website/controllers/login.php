<?php

class login extends Controller {

    public function index() {
        $result = array();
        $result["status"] = 200;
        if (!IS_LOGGED_IN) {

            $result["page"] = 3;
            $result["title"] = "Login";
            $result["description"] = "This is Login Desription";
            $result["data"] = $this->getView("login/login");
        } else {
            $result["page"] = 3;
            $result["title"] = "Opps! You are already logged in";
            $result["data"] = $this->getView("login/loggedin");
        }
        echo json_encode($result);
    }

    public function validate() {
        $result = ["code" => 100, "message" => "Username or password is wrong"];
        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";

        if (!empty($info)) {

            $info = (array) $info;
            $username = (isset($info['username'])) ? $info['username'] : "";
            $password = (isset($info['password'])) ? $info['password'] : "";
            $rememberMe = (isset($info['rememberme'])) ? $info['rememberme'] : false;

            if (!preg_match('/^[A-Za-z][A-Za-z0-9\._]{2,31}$/', $username)) {
                $result = ["code" => 101, "message" => "User name is not valid."];
            } else if (!preg_match('/^[A-Za-z][A-Za-z0-9\._@#]{2,31}$/', $password)) {
                $result = ["code" => 102, "message" => "Password is not valid.(Must be grater then 3 charecters)"];
            } else {

                if ($this->validateUser($username, $password)) {
                    $query = "select userid,username,img,logininfo from usersignup where username=:username";
                    $this->database->query($query);
                    $this->database->bind("username", $username);
                    $userDetailObject = $this->database->single();
                    SessionManagement::sessionStart();
                    SessionManagement::setSession("userid", $userDetailObject['userid']);
                    SessionManagement::setSession("img", $userDetailObject['img']);
                    SessionManagement::setSession("username", $userDetailObject['username']);
                    SessionManagement::setSession("logininfo", $userDetailObject['logininfo']);

                    if ($rememberMe == "true") {
                        CookieManagment::setCookie("loginInfo", $userDetailObject['logininfo']);
                        CookieManagment::setCookie("remember", "true");
                    } else {
                        CookieManagment::setCookie("remember", "false");
                    }

                    $result = ["code" => 1, "message" => "Successfully Logged In.", "nextPage" => "/"];
                    $result['profileurl'] = "/user/" . $userDetailObject['username'];
                } else {
                    $result = ["code" => 100, "message" => "Username or password is wrong."];
                }
                $result["remember"] = $rememberMe;
            }
        } else {
            $result = ["code" => 110, "message" => "Unknown Error."];
        }
        echo json_encode($result);
    }

    private function validateUser($username, $password) {
        $this->database->query("select count(*) from usersignup where username=:username and password=:password");
        $this->database->bind("username", $username);
        $this->database->bind("password", md5($password));
        return ($this->database->firstColumn() == 1) ? true : false;
    }

    private function logoutUser() {


        SessionManagement::sessionStart();
        SessionManagement::removeSession("userid");
        SessionManagement::removeSession("username");
        SessionManagement::removeSession("img");
        SessionManagement::removeSession("logininfo");

        if (CookieManagment::getCookie("remember") == "true") {
            CookieManagment::removeCookie("loginInfo");
            CookieManagment::setCookie("remember", "false");
        }
    }

    public function logout() {

        $result = ["code" => 1, "message" => "Looged out Suucessfuly."];
        if (isset($_POST["csrf"]) && SessionManagement::sessionExists("csrf")) {
            if (SessionManagement::getSession("csrf") == $_POST["csrf"]) {
                if (SessionManagement::sessionExists("userid")) {
                    $this->logoutUser();
                } else {
                    $result = ["code" => 101, "message" => "You are not logged in."];
                }
            }else{
                $result = ["code" => 102, "message" => "Unknown token."];
            }
        } else {
            $result = ["code" => 102, "message" => "Unknown Error.<br/>Try Refresh Page"];
        }

        echo json_encode($result);
    }

}
