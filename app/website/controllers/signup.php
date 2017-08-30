<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of singup
 *
 * @author home
 */
class signup extends Controller {

    public function index() {
        $result = array();
        $result["status"] = 200;
        if (!IS_LOGGED_IN) {
            $result["page"] = 4;
            $result["title"] = "Signup";
            $result["description"] = "This is Signup Desription";
            $result["data"] = $this->getView("signup/signup");
        }else{
            $result["page"] = 4;
            $result["title"] = "Opps! You are logged in.";
            $result["data"] = $this->getView("signup/loggedin");
        }
        echo json_encode($result);
    }

    public function create() {
        $result = ["code" => 100, "message" => "Username or password is wrong"];

        $info = (isset($_POST['info'])) ? json_decode($_POST['info']) : "";

        if (!empty($info)) {

            $info = (array) $info;
            $username = (isset($info['username'])) ? $info['username'] : "";
            $password = (isset($info['password'])) ? $info['password'] : "";
            $confirmPassword = (isset($info['confirmPassword'])) ? $info['confirmPassword'] : "";
            $email = (isset($info['email'])) ? $info['email'] : "";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result = ["code" => 105, "message" => "Invalid Email ID."];
            }else if (!preg_match('/^[A-Za-z][A-Za-z0-9\._]{2,31}$/', $username)) {
                $result = ["code" => 101, "message" => "User name is not valid."];
            }  else if (!preg_match('/^[A-Za-z][A-Za-z0-9\._@#]{2,31}$/', $password)) {
                $result = ["code" => 102, "message" => "Password is not valid.(Must be grater then 3 charecters)"];
            } else if (!preg_match('/^[A-Za-z][A-Za-z0-9\._@#]{2,31}$/', $confirmPassword)) {
                $result = ["code" => 103, "message" => "Confirm Password is not valid.(Must be grater then 3 charecters)"];
            } else if ($password !== $confirmPassword) {
                $result = ["code" => 104, "message" => "Password Didn't Match."];
            } else if ($this->userExists($username)) {
                $result = ["code" => 108, "message" => "Username is already taken."];
            }else if ($this->emailExists($email)) {
                $result = ["code" => 1011, "message" => "Email ID is already used."];
            } else {
                $password = md5($password);
                $i = md5(rand());
                $query = "insert into usersignup(username,password,logininfo,email) values(:username,:password,:logininfo,:email)";
                $this->database->query($query);
                $this->database->bind("username", $username);
                $this->database->bind("password", $password);
                $this->database->bind("logininfo", $i);
                $this->database->bind("email", $email);
                $status = $this->database->execute();
                if ($status) {
                    $result = ["code" => 1, "message" => "Signed Up Successfully.", "nextPage" => "/login"];
                } else {
                    $result = ["code" => 109, "message" => "Internal Error <br/> Try Again in some time."];
                }
            }
        } else {
            $result = ["code" => 110, "message" => "Unknown Error."];
        }

        echo json_encode($result);
    }

    private function userExists($username) {
        $this->database->query("select count(*) from usersignup where username=:username");
        $this->database->bind("username", $username);
        return ($this->database->firstColumn() == "0") ? false : true;
    }
    private function emailExists($email) {
        $this->database->query("select count(*) from usersignup where email=:email");
        $this->database->bind("email", $email);
        return ($this->database->firstColumn() == "0") ? false : true;
    }

}
