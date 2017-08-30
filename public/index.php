<?php
include '../app/website/init.php';
if (!SessionManagement::sessionExists("csrf")) {
    $csrf = base64_encode(openssl_random_pseudo_bytes(16));
    SessionManagement::setSession("csrf", $csrf);
} else {
    $csrf = SessionManagement::getSession("csrf");
}

/*
 * for generating meta tags for sharing
 * 
 * 
 * 
 */
$URL = $_SERVER['REQUEST_URI'];
$URL = trim($URL);
$URL_ARRAY = explode("/", $URL);
$URL_MUSIC = array_pop($URL_ARRAY);
$URL_MUSIC = ($URL_MUSIC == "") ? array_pop($URL_ARRAY) : $URL_MUSIC;


$query = "select img,descrption,title from music where url=:url";
$db = new Database();
$db->query($query);
$db->bind("url", $URL_MUSIC);
$musicinfo = $db->single();

$IMG = SITE_URL . MUSIC_IMAGE_ORIGINAL . $musicinfo["img"];
$DESCRIPTION = $musicinfo["descrption"];
$TITLE = $musicinfo["title"];
$URL = SITE_URL . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="/public/css/home.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css"/>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo SITE_NAME; ?></title>
        <meta name="keywords" content="<?php echo SITE_NAME; ?>,music,upload">
        <meta name="description" content="Share your music ">
        <meta name="language" content="english">
        <script src="/public/js/jquery-2.2.0.min.js" type="text/javascript"></script>
        <script src="/public/js/app.js?v=<?php echo time(); ?>" type="text/javascript"></script>
        <script src="/public/js/analytics.google.com.js" type="text/javascript"></script>

        <link rel="icon" href="/favicon.ico">
        <!-- Chrome, Firefox OS, Opera and Vivaldi -->
        <meta name="theme-color" content="#18F3AD">
        <link rel="manifest" href="/manifest.json">
        <!-- Windows Phone -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="msapplication-navbutton-color" content="#18F3AD">
        <!-- iOS Safari -->
        <meta name="apple-mobile-web-app-status-bar-style" content="#18F3AD">

        <!--Social Info-->
        <link rel="author" href="https://plus.google.com/107451468871433725729" />
        <link rel="canonicalURL" href="<?php echo $_SERVER['REQUEST_URI']; ?>" />
        <link rel="canonical" class="metaURLCanonical" href="<?php echo $URL; ?>" />
        <meta property="og:url" class="metaURL" content="<?php echo $URL; ?>">
        <meta property="og:image" class="metaImage" content="<?php echo $IMG; ?>">
        <meta property="og:description" class="metaDescription" content="<?php echo $DESCRIPTION; ?>">
        <meta property="og:title" class="metaTitle" content="<?php echo $TITLE; ?>">
        <meta property="og:site_name" content="<?php echo SITE_URL; ?>">
        <meta property="og:see_also" content="<?php echo SITE_URL; ?>">
        <meta itemprop="name" class="metaTitle" content="<?php echo $TITLE; ?>">
        <meta itemprop="description" class="metaDescription" content="<?php echo $DESCRIPTION; ?>">
        <meta itemprop="image" class="metaImage" content="<?php echo $IMG; ?>">
        <meta name="twitter:card" content="summary_large_image" /> 
        <meta name="twitter:url"  class="metaURL" content="<?php echo $URL; ?>">
        <meta name="twitter:title" class="metaTitle" content="<?php echo $TITLE; ?>">
        <meta name="twitter:description" class="metaDescription" content="<?php echo $DESCRIPTION; ?>">
        <meta name="twitter:image" class="metaImage" content="<?php echo $IMG; ?>">

        <link href="https://fonts.googleapis.com/css?family=Roboto|Roboto+Condensed" rel="stylesheet"> 


    </head>
    <body class="" data-csrf="<?php echo $csrf; ?>" data-site="<?php echo SITE_NAME; ?>">
        <div class="headerContainer">
            <div class="header layout-content">
                <ul class="hl">
                    <li> 
                        <a href="/">
                            <button class="siteButton" style="background-image: url(/public/images/website-icon.svg)"></button>
                        </a>
                    </li>
                    <li class="right">
                        <button class="topMenuButton" onclick="IniteditMusic.mobile.menu.onClickShowTopMenu()" style="background-image: url(/public/images/menu-icon.svg)"></button>

                    </li>
                </ul>
                <ul class="hl">
                    <li class="">
                        <button onclick="IniteditMusic.search.onClickShowSearchDialog()" class="searchButton" style="background-image: url(/public/images/search-icon.svg)"></button>
                    </li>
                </ul>
                <ul class="hl right">
                    <li><a href="/upload" class="uploadTitle">upload</a></li>
                    <li class="settingMenuContainer">
                        <i class="settingMenuThreeDot"></i>
                        <ul class="vl settingMenu">
                            <li><a href="/">Home</a></li>
                            <li class="settingMenuLogIn" style="<?php echo (IS_LOGGED_IN) ? "display: none;" : ""; ?>"><a href="/login" >Log in</a></li>
                            <li class="settingMenuLogIn" style="<?php echo (IS_LOGGED_IN) ? "display: none;" : ""; ?>"><a href="/signup" >Sign up</a></li>
                            <li class="settingMenuLoggedIn" style="<?php echo (IS_LOGGED_IN) ? "" : "display: none;"; ?>">
                                <a 
                                    href="/user/<?php echo SessionManagement::sessionExists("username") ? SessionManagement::getSession("username") : ""; ?>" 
                                    id="settingMenuUserName" >
                                    Profile
                                </a>
                            </li>
                            <li><a href="/about">About us</a></li>
                            <li><a href="/help">Help</a></li>
                            <li><a href="/privacy">Privacy</a></li>
                            <li><a href="/cookie">Cookie</a></li>
                            <li class="settingMenuLoggedIn" style="<?php echo (IS_LOGGED_IN) ? "" : "display: none;"; ?>"><span onclick="IniteditMusic.account.logout(true)" >Logout</span></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div>
            <div id="main" class="main"></div>
        </div>
        <div class="footer" id="footer">
            <div class="footerContent layout-content">
                <ul class="hl">
                    <li><a href="/about" class="inlink">About us</a></li>
                    <li><a href="/help" class="inlink">Help</a></li>
                    <li><a href="/privacy" class="inlink">Privacy</a></li>
                    <li><a href="/cookie" class="inlink">Cookie</a></li>

                </ul>
                <ul class="hl right">
                    <li class="normalLine" >
                        <ul class="hl social">
                            <li><a href="https://plus.google.com/107451468871433725729/about"  target="_blank" class="icons googleplus linkDefault"></a></li>
                            <li><a href="https://www.facebook.com/InitEdit" target="_blank" class="icons facebook linkDefault"></a></li>
                            <li><a href="https://twitter.com/initedit" target="_blank" class="icons twitter linkDefault"></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="mainProgressBar" id="mainProgressBar"></div>
        <div id="topMenuContent" class="topMenuContent">
            <div class="closeButtonContainer">
                <button class="close" onclick="IniteditMusic.mobile.menu.onClickHideTopMenu()">
                    <img src="/public/images/close-icon.svg" alt="close"/> 
                    <!--Close-->
                </button>
            </div>
            <div id="topMenuItems" class="topMenuItems">

            </div>
        </div>
        <div class="addToPlaylistContainer" id="addToPlaylistContainer">
            <button class="close" onclick="IniteditMusic.playlist.hideAddToPlaylistFullScreen()">
                <img src="/public/images/close-icon.svg" alt="close"/> 
                <!--Close / ESC-->
            </button>
            <div id="addToPlaylist">

            </div>
        </div>
        <div class="shareDialogBoxContainer" id="shareDialogBoxContainer" >
            <button class="close" onclick="IniteditMusic.music.share.hideShareDialog()">
                <img src="/public/images/close-icon.svg" alt="close"/> 
                <!--Close / ESC-->
            </button>
            <div id="shareDialogBox" class="shareDialogBox" >
                <h2>Share Music</h2>
                <div><a herf="" class="twitterShare linkDefault" target="_blank" id="twitterShare">Twitter</a></div>
                <div><a herf="" class="googleShare linkDefault" target="_blank" id="googleShare">Google Plus</a></div>
            </div>
        </div>
        <div class="searchDialogBoxContainer" id="searchDialogBoxContainer">
            <button class="close" onclick="IniteditMusic.search.onClickHideSearchDialog()">
                <img src="/public/images/close-icon.svg" alt="close"/> 
                <!--Close / ESC-->
            </button>
            <div class="searchInputContainer">
                <span class="leftLine"></span>
                <input type="search" placeholder="Search" onkeyup="IniteditMusic.search.onChangeSearchDialogInput()" class="searchInput" id="searchDialogInput"/>
                <span class="rightLine"></span>
            </div>
            <div class="searchDialogMainContent">
                <div id="searchDialogMainContent">

                </div>
                <div class="searchDialogMainContentLoading" id="searchDialogMainContentLoading"></div>
            </div>
        </div>
        <div class="playlist-empty-template none">

            <p class='bottomEmptyPlaylist'>Playlist is empty.</p>
        </div>
        <div class="bottomPlayerContainer" id="bottomPlayerContainer">
            <div class="bottomPlayer layout-content">
                <ul class="hl">
                    <li>
                        <button class="button bg-35" onclick="IniteditMusic.player.previous()" id="bottomPreviousButton" style="background-image: url(/public/images/previous-icon.svg)"></button>
                    </li>

                    <li>
                        <button onclick="IniteditMusic.player.toggle()" id="bottomPlayPauseButton" class="button bg-35" style="background-image: url(/public/images/play-icon.svg)"></button>
                    </li>
                    <li>
                        <button onclick="IniteditMusic.player.next()" id="bottomNextButton" class="button bg-35" style="background-image: url(/public/images/next-icon.svg)"></button>
                    </li>
                </ul>
                <ul class="hl">
                    <li>
                        <button onclick="IniteditMusic.player.onClickRepeatToggle()" id="bottomRepeatButton" class="button bottomRepeatButton  bg-25" style="background-image: url(/public/images/repeat-icon.svg);"></button>
                    </li>
                </ul>
                <ul class="hl bottomMusicTimeContainer">
                    <li>
                        <span class="playedTime" id="bottomPlayedTime">00:00</span>
                    </li>
                    <li>
                        <div class="bottomMusicProgressContainer">
                            <div class="progressBackground"></div>
                            <div class="progressStreamed"></div>
                            <div class="progressPlayed" id="bottomMusicTrackProgress"></div>
                            <span class="circle" id="bottomMusicTrackCircle"></span>
                            <input class="input" id="bottomMusicTrackInput" onchange="IniteditMusic.player.sliders.trackDone()" oninput="IniteditMusic.player.sliders.track()" type="range" min="0" max="100" />
                        </div>
                    </li>
                    <li>
                        <span class="totalTime" id="bottomTotalTime">03:15</span>
                    </li>
                </ul>
                <ul class="hl">
                    <li>
                        <button class="button  bg-25" 
                                ondragover="IniteditMusic.player.playlist.bottom.allowDropMusicItem(event)"
                                ondrop="IniteditMusic.player.playlist.bottom.onMenuButtonDropMusicItem(event)"
                                onclick="IniteditMusic.player.showPlaylistOnClick()"
                                style="background-image: url(/public/images/menu-icon.svg);">

                        </button>
                        <div class="bottomPlaylistContainer" id="bottomPlatlistContainer">
                            <div class="bottom-playlist-header pv-10">
                                Your playlist

                                <button class="close-btn right bg-25 button" onclick="IniteditMusic.player.hidePlaylistOnClick()">

                                </button>
                                <button class="right bg-25 button clear" onclick="IniteditMusic.player.clearPlaylistOnClick()">
                                    clear
                                </button>
                            </div>
                            <div class="bottomPlatlistItemContainer scrollBar light" id="bottomPlatlistItemContainer">

                            </div>
                        </div>
                    </li>
                    <li onmouseenter="IniteditMusic.player.volume.bottomShow()"
                        onmouseleave="IniteditMusic.player.volume.bottomHide()">
                        <button class="button  bg-25" id="bottomVolumeButton" onclick="IniteditMusic.player.volume.toggleMute()" style="background-image: url(/public/images/volume-icon.svg);">

                        </button>
                        <div class="volumeChangeContainer" id="bottomVolumeChangeContainer">
                            <!--Order Matters Here-->
                            <div class="volumeContainer"></div>
                            <div class="volumeContainer background" id="bottomMusicVolumeProgress"></div>
                            <span class="circle" id="bottomMusicVolumeCircle"></span>
                            <input class="input" id="bottomMusicVolumeInput" oninput="IniteditMusic.player.sliders.volume()" onchange="IniteditMusic.player.sliders.volumeDone()" type="range" min="0" max="100"/>
                        </div>
                    </li>
                </ul>
                <ul class="hl">
                    <li>
                        <a href="" id="bottomPlayingMusicLink">
                            <div class="bottomPlayingMusic">
                                <div class="image" id="bottomMusicImage" style="background-image: url(/public/images/defaultMusicIcon.jpg),linear-gradient(135deg,#18F3AD,#AAA);;" ></div>
                                <span class="title" id="bottomMusicTitle">Black Beard</span>
                                <div class="username" id="bottomMusicUserName">Alan Turning</div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="none" id="waveformContainerTemplate">
            <div class="waveContainer"  
                 onclick="IniteditMusic.player.waveform.seekOnClickPlay(this, event)" 
                 onmouseout="IniteditMusic.player.waveform.hideSeekBar(this, event)" 
                 onmousemove="IniteditMusic.player.waveform.showSeekBar(this, event)">
                <canvas class="normalWaveform"></canvas>
                <div class="updateWaveformPlayedCanvas">
                    <canvas class="playedWaveform"></canvas>
                </div>
                <div class="seekWaveformContainer">
                    <canvas class="seekWaveform"></canvas>
                </div>
                <canvas class="seekWaveform thumbWaveform none"></canvas>
                <img  src="/public/useruploads/waveform/c8f73aaef4945252bf81647177f0dffe.svg"/>
            </div>
        </div>
        <div class="none" id="musicListItemContainer">
            <div class="musicListItem musicListItem_{{musicid}}">
                <div class="backgroundImage left"  >
                    <div class=" backgroundBackgroundImage_{{musicid}}">
                        <div class="img-placeholder">
                        </div>

                        <div class="backgroundBackgroundImage ">
                        </div>

                    </div>
                    <div class="playPauseButton linkDefault stopPropagation musicPlayPauseButtonItem_{{musicid}}" 
                         draggable="true"
                         ondrag="IniteditMusic.player.playlist.bottom.onDragStartedMusicItem(event,{{musicid}})" 
                         ondragstart="IniteditMusic.player.playlist.bottom.onDragStartedMusicItem(event,{{musicid}})"
                         onclick="IniteditMusic.player.toggleMusicOnMouseClick({{musicid}})" 
                         id="musicPlayPauseButtonItem_{{musicid}}"  
                         style="background-image: url(/public/images/play-icon.svg)">

                    </div>
                </div>
                <div class="left content">
                    <div class="title"><a href="{{musicurl}}">{{title}}</a></div>
                    <div class="username pb-10">
                        By <a href="/user/{{username}}" title="{{username}}">{{username}}</a>
                    </div>
                    <div class="waveform-container waveform">

                    </div>
                    <div class="action">
                        <button class="button"
                                onclick="IniteditMusic.playlist.onClickItemShowAddToPlatlist({{musicid}})"
                                >
                            Add To Playlist
                        </button>
                        <button class="button"
                                onclick="IniteditMusic.player.onClickAddToCurrentPlaylist({{musicid}})"
                                >
                            Add To Current Playlist
                        </button>
                        <button class="button"
                                onclick="IniteditMusic.music.share.showShareDialog({{musicid}})"
                                >
                            Share
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="none" id="musicPlaylistItemListTemplate">
            <div class="playlistItemListContainer playlistItemListContainer_{{musicid}}" 
                 onclick="IniteditMusic.player.onClickPlayFromPlaylistItem(this,{{musicid}})">
                <div class="title">
                    <span class="number">{{number}}</span>- &nbsp; {{title}} by <span>{{username}}</span>
                </div>
            </div>
        </div>

        <div class="none" id="musicPlaylistItemContainer">
            <div class="musicPlaylistItem musicListItem playlist_{{playlistid}} musicPlaylistItem_{{musicid}}">
                <div class="backgroundImage left"  >
                    <a href="{{musicurl}}" class="thumburl">
                        <div class="none-backgroundBackgroundImage_{{musicid}}">
                            <div class="img-placeholder">
                            </div>

                            <div class="backgroundBackgroundImage ">
                            </div>

                        </div>
                    </a>
                </div>
                <div class="left content">
                    <div class="title"><a href="{{musicurl}}">{{title}}</a></div>
                    <div class="username pb-10">
                        By <a href="/user/{{username}}" title="{{username}}">{{username}}</a>
                    </div>
                    <div class="waveform-container waveform">

                    </div>
                    <div class="action none">
                        <button class="button"
                                onclick="IniteditMusic.playlist.onClickItemShowAddToPlatlist({{musicid}})"
                                >
                            Add To Playlist
                        </button>
                        <button class="button"
                                onclick="IniteditMusic.player.onClickAddToCurrentPlaylist({{musicid}})"
                                >
                            Add To Current Playlist
                        </button>
                        <button class="button"
                                onclick="IniteditMusic.music.share.showShareDialog({{musicid}})"
                                >
                            Share
                        </button>
                    </div>
                    <div class="playlistItemListContainer">

                    </div>
                </div>
            </div>
        </div>

        <div class="none" id="musicItemContainer">

            <div class="musicItemCard" onmouseenter="IniteditMusic.player.showOnMouseEnter({{musicid}})" onmouseleave="IniteditMusic.player.hideOnMouseLeave({{musicid}})">
                <a href="{{musicurl}}">


                    <div class="backgroundImage "  >
                        <div class=" backgroundBackgroundImage_{{musicid}}">
                            <div class="img-placeholder">
                            </div>

                            <div class="backgroundBackgroundImage ">
                            </div>
                        </div>
                        <div class="playPauseButton linkDefault stopPropagation musicPlayPauseButtonItem_{{musicid}}" 
                             draggable="true"
                             ondrag="IniteditMusic.player.playlist.bottom.onDragStartedMusicItem(event,{{musicid}})" 
                             ondragstart="IniteditMusic.player.playlist.bottom.onDragStartedMusicItem(event,{{musicid}})"
                             onclick="IniteditMusic.player.toggleMusicOnMouseClick({{musicid}})" 
                             id="musicPlayPauseButtonItem_{{musicid}}"  
                             style="background-image: url(/public/images/play-icon.svg)">

                        </div>

                        <ul class="vl musicSettingItems stopPropagation musicSettingItem_{{musicid}}" id="musicSettingItem_{{musicid}}">
                            <li onclick="IniteditMusic.playlist.onClickItemShowAddToPlatlist({{musicid}})">Add To Play list</li>
                            <li onclick="IniteditMusic.player.onClickAddToCurrentPlaylist({{musicid}})">Add To Current Playlist</li>
                            <li onclick="IniteditMusic.music.share.showShareDialog({{musicid}})">Share</li>

                        </ul>


                    </div>
                </a>

                <div class="title" style="width:{{width}};">
                    <div class="titleText"><a href="{{musicurl}}">{{title}}</a></div>
                    <button class="musicSetting" onclick="IniteditMusic.player.toggleSetting({{musicid}})"  style="background-image: url(/public/images/setting-icon.svg);;"></button>
                </div>
                <div class="userInfo">
                    By <a href="/user/{{username}}" title="{{username}}">{{username}}</a>
                </div>
            </div>
        </div>
        <div class="none" id="bottomPlaylistItemTemplateContainer" onclick="">
            <div class="bottomPlaylistItem" 
                 draggable="true"  
                 ondragstart="IniteditMusic.player.playlist.bottom.onDragStartedMusicItem(event,{{musicid}})"
                 ondragover="IniteditMusic.player.playlist.bottom.allowDropMusicItem(event)"
                 ondrop="IniteditMusic.player.playlist.bottom.onDropMusicItem(event,{{musicid}})"
                 id="bottomPlaylistItem_{{musicid}}"
                 onclick="IniteditMusic.player.playFromPlaylistOnClick({{musicid}})">
                <div class="image"  style="background-image: url({{imagethumb}}),linear-gradient(135deg,#18F3AD,#AAA);;" ></div>
                <span class="title" >{{title}}</span>
                <div class="username" >{{username}}</div>
                <span class="close close-bg-icon" onclick="IniteditMusic.player.removeFromPlaylistOnClick(event,{{musicid}})">

                </span>
            </div>
        </div>

    </body>

</html>