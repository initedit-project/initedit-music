<?php 
$mainMusicId = $data["musicid"];
?>

<div class="playlistAddContainer">
    <div>
        <ul class="hl titleContainer">
            <li id="addToPlaylistAddContainerMenu" class="highlight" onclick="IniteditMusic.playlist.showAddToPlaylistAddContainer()">Add to playlist</li>
            <li id="addToPlaylistNewContainerMenu" onclick="IniteditMusic.playlist.showAddToPlaylistNewContainer()">Create New playlist</li>
        </ul>
    </div>
    <div class="createNewPlaylistContainer" id="addToPlaylistNewContainer">
        <div class="error" id="addNewplaylistError">

        </div>
        <div>
            <input id="newPlaylistName" class="newPlaylistName" type="text" placeholder="type playlist name"/>
        </div>
        <div class="margingSpace">Playlist Will be</div>

        <div  class="margingSpace">
            <span>
                <input type="radio" value="1" name="playlistPrivacy">Private
            </span>
            <span>
                <input type="radio" checked="checked" value="0" name="playlistPrivacy" id="playlistPrivacy">Public
            </span>
        </div>
        <div><button class="loginButton" onclick="IniteditMusic.playlist.onClickCreateNewPlaylistFullScreen(<?php echo $mainMusicId; ?>)">Create new Playlist</button></div>
    </div>
    <div class="playlistItemsContainer" id="addToPlaylistAddContainer">
        <div class="error" id="addToPlaylistError">

        </div>
        <?php
        foreach ($data["playlist"] as $playlist) {
            extract($playlist);
            ?>
            <div class="playlistItem">
                <div class="name"><?php echo $name; ?></div>
                <div class="action">
                    <button onclick="IniteditMusic.playlist.onClickAddToPlaylistFullScreen(<?php echo $musicid; ?>, '<?php echo $name; ?>', '<?php echo $url; ?>')" id="addButtonPlaylist_<?php echo $url; ?>_<?php echo $musicid; ?>" class="added <?php echo ($added) ? "none" : "" ?>">Add</button>
                    <button onclick="IniteditMusic.playlist.onClickRemoveFromPlaylistFullScreen(<?php echo $musicid; ?>, '<?php echo $name; ?>', '<?php echo $url; ?>')" id="removeButtonPlaylist_<?php echo $url; ?>_<?php echo $musicid; ?>" class="loginButton <?php echo ($added) ? "" : "none" ?>">Added</button>
                </div>
            </div>
        <?php } ?>
        <?php if (count($data["playlist"]) == 0) { ?>
            <p class="emptyPlaylistAdd">No Playlist Yet.</p>
        <?php } ?>

    </div>
</div>

