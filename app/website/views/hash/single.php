<?php
$hash = $data["hash"];
?>
<div class="pageContainer homeContainer">
    <div class="page home">
        <div class="hashSingleHeader">
            <h2 class="title"><span class="hashSymbol">#</span><?php echo $hash;?></h2>
        </div>
        <div id="homeTrending"></div>
    </div>
    <?php echo $data["paging"]; ?>
</div>
