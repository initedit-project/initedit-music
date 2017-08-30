<?php
$playlists = $data["playlists"];
/*
 * playlistid
 * name
 * privacy
 * url
 * username
 */
?>


<div class="pageContainer homeContainer">
    <div class="page home">

        <?php
        foreach ($playlists as $playlist) {
            extract($playlist);
//            print_r($playlist);
            ?>
            <div  class="userPlaylistItemContainer" id="userPlaylistItemContainer_<?php echo $playlistid; ?>">
                <div class="clearFix title">
                    <div><a href="/playlist/<?php echo $url;?>"><?php echo $name; ?></a></div>
                    <div class="userPlaylistMenuButtonContainer" >
                        <button class="menuButton" onclick="IniteditMusic.user.playlist.menu.showMenu('<?php echo $playlistid; ?>')" style="background-image: url(/public/images/setting-icon.svg);"></button>
                        <ul class="vl menuContent" id="userPlaylistMenu_<?php echo $playlistid; ?>">
                            <li onclick="IniteditMusic.user.playlist.menu.showPrivacyMenu('<?php echo $playlistid; ?>')">Privacy</li>
                            <li onclick="IniteditMusic.user.playlist.menu.showDeleteMenu('<?php echo $playlistid; ?>')">Delete</li>
                        </ul>
                        <ul class="vl menuContent deleteMenu" id="userPlaylistDeleteMenu_<?php echo $playlistid; ?>">
                            <li>
                                Are you sure?<br/>
                                <button onclick="IniteditMusic.user.playlist.menu.onClickDeletePlaylist('<?php echo $playlistid; ?>')">Yes</button>
                                <button onclick="IniteditMusic.user.playlist.menu.hideDeleteMenu('<?php echo $playlistid; ?>')">No</button>
                            </li>
                        </ul>
                        <ul class="vl menuContent privacyMenu" id="userPlaylistPrivacyMenu_<?php echo $playlistid; ?>">
                            <li>
                                Change Privacy<br/>
                                <select id="userPlaylistPrivacy_<?php echo $playlistid; ?>" onchange="IniteditMusic.user.playlist.menu.onClickChangePrivacyPlaylist('<?php echo $playlistid; ?>')">
                                    <option>Public</option>
                                    <option <?php echo ($privacy=="1")?"selected":"";?>>Private</option>
                                </select>
                            </li>
                        </ul>
                    </div>
                </div>
                <div id="userMenuContentPlaylist_<?php echo $playlistid; ?>" class="content">
                
                </div>
            </div>
        <?php } ?>

    </div>
    <?php echo $data["paging"]; ?>
</div>