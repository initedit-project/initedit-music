<?php
$home = $data["home"];
?>
<div class="pageContainer homeContainer">
    <div class="page home">
        <div id="adminRightContent">
            <ul class="vl homeInfo">
                <?php foreach ($home as $h) { ?>
                    <li class="homeInfoItem">
                        <ul class="hl">
                            <li><?php echo $h["key"];?></li>
                            <li><?php echo $h["value"];?></li>
                        </ul>
                    </li>
                <?php } ?>

            </ul>
        </div>
    </div>
</div>
<div id="svgCircleHDD">

</div>
<script>
    var circlesInfo = {
        id: "svgCircleHDD",
        width: 300,
        height: 300,
        segments: [
            {
                size: 20,
                title: "This is Total Amount"
            },
            {
                size: 20,
                title: "This is Total Amount"
            },
            {
                size: 20,
                title: "This is Total Amount"
            }
        ]
    }

</script>
