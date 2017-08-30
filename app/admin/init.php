<?php

$v = "../app/lib/tools/variables.php";
if (file_exists($v)) {
    require_once "../app/lib/tools/variables.php";
    require_once "../app/lib/tools/CookieManagment.php";
    require_once "../app/lib/tools/SessionManagement.php";
    require_once "../app/lib/tools/TimeHelper.php";
    require_once "../app/lib/tools/Database.php";
} else {
    require_once "../lib/tools/variables.php";
    require_once "../lib/tools/CookieManagment.php";
    require_once "../lib/tools/SessionManagement.php";
    require_once "../lib/tools/TimeHelper.php";
    require_once "../lib/tools/Database.php";
}


require_once "core/App.php";
require_once "core/Controller.php";

define("IS_LOGGED_IN", (SessionManagement::sessionExists("adminuserid") ? true : false), true);
