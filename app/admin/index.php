<?php

require_once "init.php";

if (!SessionManagement::sessionExists("adminuserid")) {
    if ($_GET["url"] != "login/validate") {
        $_GET['url'] = "ajax/login";
    }
}
//print_r($_SESSION);exit;


$app = new App;
