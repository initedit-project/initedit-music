<?php
$playlistInfo = $data["playlist"];
/*
 * playlistid,
 * name,
 * url,
 * userid,
 * time
 */
$user = $data["user"];
/*
 * username,
 * img,
 * cover
 */
$images = $data["images"];
/*
 * Direct Value
 */
?>
<div class="pageContainer homeContainer">
    <div class="singlePlaylistHeader">
        <div id="sliderContainer" class="playlistBackgroundScrollContainer">
            <div class="playlistBackgroundScroll">
                <?php foreach ($images as $image) { ?>
                    <img src="<?php echo $image; ?>" height="200"/>
                <?php } ?> 
            </div>
        </div>
        <div class="gradient">
            
        </div>
        <div class="title">&ldquo; <?php echo ucwords($playlistInfo["name"]); ?> &rdquo; </div>
        <div class="userInfo">
            <img class="userImage" src="/public/useruploads/user/profile/<?php echo $user["img"] ?>" alt="<?php echo $user["username"] ?>"/>
            <br/>
            By <a href="/user/<?php echo $user["username"]; ?>"><?php echo $user["username"]; ?></a>
        </div>
    </div>
</div>
<div class="pageContainer homeContainer">
    <div class="page home">
        <div id="homeTrending" class="single-playlist"></div>
    </div>
    <?php echo $data["paging"]; ?>
</div>
<script>
    setTimeout(IniteditMusic.playlist.startAnimation, 2000);
</script>