<?php
include '../app/admin/init.php';
if (!SessionManagement::sessionExists("csrf")) {
    $csrf = base64_encode(openssl_random_pseudo_bytes(16));
    SessionManagement::setSession("csrf", $csrf);
} else {
    $csrf = SessionManagement::getSession("csrf");
}
?>
<head>
    <link rel="stylesheet" href="/public/css/header.css?<?php echo time(); ?>" type="text/css"/>
    <link href="/public/css/home.css" rel="stylesheet" type="text/css"/>
    <link href="/public/css/adminhome.css" rel="stylesheet" type="text/css"/>
    <meta charset="UTF-8">
    <title><?php echo SITE_NAME; ?></title>
    <meta name="keywords" content="initedit,music">
    <meta name="description" content="Share your music ">
    <meta name="language" content="english">
    <script src="/public/js/jquery-2.2.0.min.js" type="text/javascript"></script>
    <script src="/public/js/appadmin.js" type="text/javascript"></script>
</head>
<body class="scrollBar" data-csrf="<?php echo $csrf; ?>" data-site="<?php echo SITE_NAME; ?>">
    <div class="headerContainer">
        <div class="header">
            <ul class="hl">
                <li> 
                    <a href="/admin/home">
                        <button class="siteButton" style="background-image: url(/public/images/website-icon.svg)"></button>

                    </a>
                </li>
                <li>Admin <?php echo SessionManagement::sessionExists("adminusername") ? " &rarr; " . ucfirst(SessionManagement::getSession("adminusername")) : ""; ?></li>
            </ul>
            <ul class="hl right">

                <li class="settingMenuContainer">
                    <i class="settingMenuThreeDot"></i>
                    <ul class="vl settingMenu">
                        <li><a href="/admin/">Home</a></li>
                        <li class="settingMenuLogIn" style="<?php echo (IS_LOGGED_IN) ? "display: none;" : ""; ?>"><a href="/login" >Log in</a></li>
                        <li class="settingMenuLoggedIn" style="<?php echo (IS_LOGGED_IN) ? "" : "display: none;"; ?>">
                            <a 
                                href="/user/<?php echo SessionManagement::sessionExists("adminusername") ? SessionManagement::getSession("adminusername") : ""; ?>" 
                                id="settingMenuUserName" >
                                Profile
                            </a>
                        </li>
                        <li class="settingMenuLoggedIn" style="<?php echo (IS_LOGGED_IN) ? "" : "display: none;"; ?>"><span onclick="IniteditMusicAdmin.account.logout(true)" >Logout</span></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div>
        <div class="admin">
            <div class="adminLeft">
                <ul class="vl menu">
                    <li><a href="/admin/home">Home</a></li>
                    <li>
                        <div><a href="/admin/music/all">Music</a></div>
                        <ul class="vl">
                            <li><a href="/admin/music/all">All</a></li>
                        </ul>
                    </li>
                    <li>
                        <div><a href="/admin/setting">Setting</a></div>
                        <ul class="vl">
                            <li><a href="/admin/setting/general">General</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div id="main" class="main adminRight"></div>
        </div>
    </div>
    <div class="mainProgressBar" id="mainProgressBar"></div>
</body>

