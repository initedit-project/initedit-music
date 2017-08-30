<?php
$music = $data["music"];
$tag = $data["tag"];
?>

<div class="pageContainer layout-content">
    <div class="page">
        <div class="editMusic">
            <h2 class="title">Edit Music</h2>
            <div id="editError" class="error">
            </div>
            <div class="actionButtonDelete">
                <div class="confirmDeleteContainerBox">
                    <button class="button" onclick="IniteditMusic.upload.music.edit.onClickShowDeleteMenu()">Delete</button>
                    <div class="confirmDeleteContainer" id="confirmDeleteContainer">
                        <p class="title">Are You Sure?</p>
                        <div>
                            <button class="button cta" onclick="IniteditMusic.upload.music.edit.onClickDeleteMusic(<?php echo $music["musicid"]; ?>)">Yes</button>
                            <button class="button" onclick="IniteditMusic.upload.music.edit.onClickHideDeleteMenu()">No</button>
                        </div>
                    </div>
                </div>
                <a href="<?php echo $music["musicurl"]; ?>"><button class="button">View</button></a>
            </div>
            <div class="editMusicContent">
                <div class="leftContent">
                    <div class="image" id="editImageContainer" style="background-image: url(<?php echo $music["image"]["thumb"]; ?>);">
                        <div class="selectImageInputContainer">
                            <div>Change Image</div>
                            <input type="file" id="editImageInput" accept="image/*" onchange="IniteditMusic.upload.music.edit.onChangeImageFile()"/>
                        </div>
                    </div>
                </div>
                <div class="rightContent">
                    <div><input type="text" class="input" id="editTitleInput" placeholder="<?php echo htmlspecialchars($music["title"]); ?>" value="<?php echo htmlspecialchars($music["title"]); ?>"/></div>
                    <div><input type="text" class="input" id="editTagInput" placeholder="<?php echo htmlspecialchars($tag); ?>" value="<?php echo htmlspecialchars($tag); ?>"/></div>
                    <div>
                        <p>Music Will Be</p>
                        <div>
                            <span>
                                <input type="radio" id="editPrivateMusicPrivacy" <?php echo $music["privacy"] == "1" ? "checked" : ""; ?>  name="privacyEdit" value="1">Private
                            </span>
                            <span>
                                <input type="radio" id="editMusicPrivacy" <?php echo $music["privacy"] == "0" ? "checked" : ""; ?>  name="privacyEdit" value="0">Public
                            </span>
                        </div>
                    </div>
                    <textarea id="editDescriptionInput" class="input" rows="4" placeholder="type music description"><?php echo htmlspecialchars($music["description"]); ?></textarea>
                    <div class="actionButtonContainer">
                        <button class="button cta" onclick="IniteditMusic.upload.music.edit.onClickSaveEdit(<?php echo $music["musicid"]; ?>)">Save Edit</button>
                        <a href="<?php echo $music["musicurl"]; ?>"><button class="button">Cancel</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
