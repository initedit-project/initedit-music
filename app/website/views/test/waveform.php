<div style="width: 1200px;height: 100px;">
    <canvas id="withimage"></canvas>
    <canvas id="withimagedisplayed"></canvas>
    <canvas id="withimagedisplayedwithwave"></canvas>
</div>
<script>
    var normalCanvas = $("#withimage").get(0);
    var normalCanvas_1 = $("#withimagedisplayed").get(0);
    var normalCanvas_2 = $("#withimagedisplayedwithwave").get(0);

    var ctx = normalCanvas.getContext("2d");
    var ctx_1 = normalCanvas_1.getContext("2d");
    var ctx_2 = normalCanvas_2.getContext("2d");

    var width = 1200;
    var height = 100;
    $("#withimage").prop("width", width);
    $("#withimage").prop("height", height);
    $("#withimagedisplayed").prop("width", width);
    $("#withimagedisplayed").prop("height", height);
    $("#withimagedisplayedwithwave").prop("width", width);
    $("#withimagedisplayedwithwave").prop("height", height);

    var img = new Image();
    img.onload = function ()
    {
        ctx.drawImage(img, 0, 0, img.naturalWidth, img.naturalHeight, 0, 0, width, height);//Image
        ctx_2.drawImage(img_1, 0, 0, img_1.naturalWidth, img_1.naturalHeight, 0, 0, width, height);//Wave Image


        var imageData = ctx.getImageData(0, 0, width, height);
        var imageData_1 = ctx_2.getImageData(0, 0, width, height);


        for (var i = 0; i < imageData_1.data.length; i += 4)
        {

            if (imageData_1.data[i] != 0) {
//                alert(imageData_1[i] + " <-> " + imageData.data[i]);return;
                imageData_1.data[i] = imageData.data[i]
            }
            if (imageData_1.data[i + 1] != 0) {
                
                imageData_1.data[i + 1] = imageData.data[i + 1]
            }
            if (imageData_1.data[i + 2] != 0) {
                
                imageData_1.data[i + 2] = imageData.data[i + 2]
            }
        }

        ctx_1.putImageData(imageData_1, 0, 0);

    }

    var img_1 = new Image();
    img_1.onload = function () {
        img.src = "/public/useruploads/image/thumb/721d2d3a4f666bce697d39437f3b5666.jpg";
    }
    img_1.src = "/public/useruploads/waveform/c8f73aaef4945252bf81647177f0dffe.svg";



</script>
