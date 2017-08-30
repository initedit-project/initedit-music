<?php
$pagingInfo = $data["paging"];
extract($pagingInfo);
/*
 * current
 * musicPerPage
 * totalMusic
 * base
 */
?>
<div class="paggingContainer layout-content">
    <div class="paging">
        <?php
        if ($current == 1) {
            
        } else {
            ?>
        <a href="<?php echo $base . ($current - 1); ?>"><button class="button cta">Previous</button></a>
        <?php } ?>


        <?php if ($totalMusic > $current * $musicPerPage) { ?>
            <a href="<?php echo $base . ($current + 1); ?>">   
                <button class="button cta">Next</button>
            </a>
        <?php }
        ?>
    </div>
</div>