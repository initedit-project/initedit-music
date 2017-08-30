<?php
$user = $data["user"];
$menu = $data["menu"];
extract($user);
/*
 * userid,
 * username,
 * img,
 * logininfo,
 * cover
 */
?>

<div class="userHeader">
    <div style="background-image:url(/public/useruploads/user/cover/<?php echo $cover; ?>),linear-gradient(90deg,#18F3AD,#AAA) " data-img="/public/useruploads/user/cover/<?php echo $cover; ?>" id="userCoverImage" class="userCover">
        <div class="profileImageContainer">
            <img src="/public/useruploads/user/profile/<?php echo $img; ?>" data-img="/public/useruploads/user/profile/<?php echo $img; ?>" id="userProfileImage" class="profileImage" alt="<?php echo $username; ?>"/>
            <?php
            if (SessionManagement::sessionExists("username")) {
                if (SessionManagement::getSession("username") == $username) {
                    ?>
                    <div class="profileImageUpload">
                        <div class="text">Change Profile</div>
                        <input type="file" id="userProfileImageInput" class="imageFile" onchange="IniteditMusic.user.upload.onChangeProfile()"/>
                    </div>
                    <div class="profileImageActionContainer" id="profileImageActionContainer">
                        <button class="save" onclick="IniteditMusic.user.upload.onSaveProfile()">Save</button>
                        <button class="cancel" onclick="IniteditMusic.user.upload.onCancelProfile()">Cancel</button>
                    </div>
                    <?php
                }
            }
            ?>
        </div>

        <div class="profileName">
            <?php echo strtoupper($username); ?>
        </div>
        <?php
        if (SessionManagement::sessionExists("username")) {
            if (SessionManagement::getSession("username") == $username) {
                ?>
                <div class="coverChangeContainer">
                    <button class="coverChangeButton">
                        <div class="text">Change Cover Image</div>
                        <input type="file" id="userCoverImageInput"  class="imageFile" onchange="IniteditMusic.user.upload.onChangeCover()"/>
                    </button>
                    <div class="coverImageActionContainer" id="coverImageActionContainer">
                        <button class="save" onclick="IniteditMusic.user.upload.onSaveCover()">Save</button>
                        <button class="cancel" onclick="IniteditMusic.user.upload.onCancelCover()">Cancel</button>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <div class="userProfileMenuContainer">
        <ul class="hl userProfileMenu">
            <li><a href="/user/<?php echo $username ?>/overview" class="<?php echo ($menu == "overview") ? "highlight" : ""; ?>">Overview</a></li>
            <li><a href="/user/<?php echo $username ?>/music" class="<?php echo ($menu == "music") ? "highlight" : ""; ?>">Music</a></li>
            <li><a href="/user/<?php echo $username ?>/likes" class="<?php echo ($menu == "likes") ? "highlight" : ""; ?>">Likes</a></li>
            <li><a href="/user/<?php echo $username ?>/playlist" class="<?php echo ($menu == "playlist") ? "highlight" : ""; ?>">Playlist</a></li>
            <?php
            if (SessionManagement::sessionExists("userid")) {
                if (SessionManagement::getSession("userid") == $userid) {
                    ?>
                    <li><a href="/user/<?php echo $username ?>/privates" class="<?php echo ($menu == "privates") ? "highlight" : ""; ?>">Privates</a></li>
                    <li><a href="/user/<?php echo $username ?>/setting" class="<?php echo ($menu == "setting") ? "highlight" : ""; ?>">Setting</a></li>
                <?php
                }
            }
            ?>
        </ul>
    </div>
</div>
