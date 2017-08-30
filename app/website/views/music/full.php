<?php
$music = $data["music"];
$liked = $data["liked"];
$timeElapsed = $data["time"];
$tag = $data["tag"];
?>

<div class="pageContainer fullMusicContainer" >
    <div class="page fullMusic" >
       
        <div class="fullMusicCover" style="background-image: url(<?php echo $music["image"]["original"]; ?>)"></div>
        <div class="fullMusicDetailContainer">
            <div class="imagethumb" style="background-image: url(<?php echo $music["image"]["thumb"]; ?>)">
                <div class="imagethumbplay" onclick="IniteditMusic.page.fullScreen.toggleMusic()" id="fullMusicPlayPauseButtonItem_<?php echo $music["musicid"]; ?>"></div>
            </div>
            <div class="leftInfo">
                <ul class="vl">
                    <li><?php echo $music["title"]; ?></li>
                    <li><a href="/user/<?php echo $music["user"]["name"]; ?>"><?php echo $music["user"]["name"]; ?></a></li>
                </ul>
            </div>
            <div class="imagethumbright" style="background-image: url(<?php echo $music["user"]["image"]; ?>)">

            </div>
            <div class="rightInfo">
                <ul class="vl">
                    <li><?php echo $tag; ?></li>
                    <li><?php echo $timeElapsed; ?></li>
                </ul>
            </div>
            
            <div class="waveformContainer" 
                 onclick="IniteditMusic.player.waveform.seekOnClick(this, event)" 
                 onmouseout="IniteditMusic.player.waveform.hideSeekBar(this, event)" 
                 onmousemove="IniteditMusic.player.waveform.showSeekBar(this, event)">
                <canvas class="normalWaveform"></canvas>
                <div class="updateWaveformPlayedCanvas_<?php echo $music["musicid"]; ?>">
                    <canvas class="playedWaveform"></canvas>
                </div>
                <div class="seekWaveformContainer_<?php echo $music["musicid"]; ?>"
                     >
                    <canvas class="seekWaveform"></canvas>
                </div>
                <canvas class="seekWaveform thumbWaveform none"></canvas>
                <img  src="/public/useruploads/waveform/c8f73aaef4945252bf81647177f0dffe.svg"/>
            </div>
        </div>
        <div class="fullScreenMenu">
            <ul class="hl">
                <li></li>
                <li onclick="IniteditMusic.music.like.onClickLikeMusic(<?php echo $music["musicid"]; ?>)" id="musiclike_<?php echo $music["musicid"]; ?>" class="<?php echo ($liked) ? "none" : ""; ?>">
                    <img src="/public/images/like-icon.svg" width="30" height="30" align="center"/>
                    Like</li>
                <li onclick="IniteditMusic.music.like.onClickUnlikeMusic(<?php echo $music["musicid"]; ?>)" id="musicliked_<?php echo $music["musicid"]; ?>" class="<?php echo ($liked) ? "" : "none"; ?>">
                    <img src="/public/images/like-icon-highlight.svg" width="30" height="30" align="center"/>
                    Liked</li>
                <li onclick="IniteditMusic.music.share.showShareDialog(<?php echo $music["musicid"]; ?>)"><img src="/public/images/share-icon.svg" width="30" height="30" align="center"/>
                    Share</li>
                <li onclick="IniteditMusic.playlist.showAddToPlaylistFullScreen(<?php echo $music["musicid"]; ?>)">
                    <img src="/public/images/add-to-playlist.svg" width="30" height="30" align="center"/>
                    Add to playlist</li>
                <li><img src="/public/images/view-count.svg" width="30" height="30" align="center"/>
                    <?php echo $music["view"]; ?>
                </li>
                <?php
                if (SessionManagement::sessionExists("username")) {
                    if (SessionManagement::getSession("username") == $music["user"]["name"]) {
                        ?>
                        <li><a href="/music/edit/<?php
                            $urlArray = (explode("/", $music["musicurl"]));
                            echo end($urlArray);
                            ?>"> Edit</a>
                        </li>
                        <?php
                    }
                }
                ?>
                <li class="waveformTypeContainerBox">
                    <div onclick="IniteditMusic.page.fullScreen.onClickShowWavformMenu()">Waveform</div>
                    <div class="waveformTypeContainer" id="waveformTypeContainer">
                        <div onclick="IniteditMusic.page.fullScreen.setWaveformType(1)">Normal</div>
                        <div onclick="IniteditMusic.page.fullScreen.setWaveformType(2)">Awesome</div>
                    </div>
                </li>
                <li></li>
            </ul>
        </div>
        <div class="pageContainer defaultPageContainer">
            <div id="homeTrending"></div>
             
        </div>
    </div>
</div>