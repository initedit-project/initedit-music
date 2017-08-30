<?php
/*
 * General Setting CONSTANT
 */

define("SITE_NAME", "Initedit Music", true);
define("HELP_MAIL", "help@music.initedit.com1", true);
define("VAR_PATH", "../lib/tools/variables.php", true);
define("MUSIC_PER_PAGE", 18, true);

/*
 * Database CONSTANT
 */
define("DB_HOST", "localhost");
define("DB_USER", "initedit_music");
define("DB_PASS", "@Android1");
define("DB_NAME", "initedit_music");


/*
 * Upload CONSTANT
 */

define("MUSIC_PUBLIC_UPLOAD", "/public/useruploads/music/public/", true);
define("MUSIC_PRIVATE_UPLOAD", "/public/useruploads/music/private/", true);
define("MUSIC_IMAGE_THUMB", "/public/useruploads/image/thumb/", true);
define("MUSIC_IMAGE_ORIGINAL", "/public/useruploads/image/original/", true);
define("MUSIC_WAVEFORM", "/public/useruploads/waveform/", true);
define("USER_PROFILE", "/public/useruploads/user/profile/", true);
define("USER_COVER", "/public/useruploads/user/cover/", true);
define("TMP_UPLOAD", "/public/tmp/", true);
define("BASE_MUSIC_UPLOAD", "../..", true);



function siteURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol . $domainName;
}

define("SITE_URL", siteURL(), true);







