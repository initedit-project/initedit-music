<?php
$music = $data["music"];
extract($music);
/*
 * musicid
 * title
 * description
 * view
 * image ->(thumb,original,waveform)
 * user ->(name,profileurl,image,cover),
 * musicurl
 * track ->(original)
 * privacy
 */
?>
<div class="musicItem">

    <div>
        <ul class="hl">
            <li class="image">
                <div style="background-image: url(<?php echo $image["thumb"]; ?>);"></div>
            </li>
            <li class="title"><?php echo $title; ?></li>
            <li class="description"><?php echo $description; ?></li>
            <li class=""><?php echo $user["name"]; ?></li>
            <li class="">
                <?php echo ($privacy == 0) ? "Public" : "Private"; ?>
            </li>
            <li class="view"><?php echo $view; ?></li>
            <li class="actionButtonContainer">
                <a href="/admin/music/edit/<?php
                $urlArray = explode("/", $musicurl);
                echo array_pop($urlArray);
                ?>">
                    <button>EDIT</button>
                </a>
                <a href="<?php echo $musicurl; ?>" class="linkDefault" target="_blank">
                    <button>VIEW</button>
                </a>
            </li>

        </ul>
    </div>
</div>
