<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>SUPLA RGB Remote</title>
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .container a {
            cursor: pointer;
            display: block;
            float: left;
            width: 33.3vw;
            height: 33.3vh;
        }
    </style>
</head>
<body>
<?php
$colors = ['000000', 'ffffff', 'ffff00', 'ff0000', 'ff8800', 'ff00ff', '0000ff', '00ffff', '00ff00'];
?>
<div class="container">
    <?php foreach ($colors as $color): ?>
        <a style="background-color: #<?= $color ?>" onclick="changeColor()" color="<?= $color ?>"></a>
    <?php endforeach; ?>
</div>

<script>
    var anchors = document.getElementsByTagName('a');
    for (var z = 0; z < anchors.length; z++) {
        var elem = anchors[z];
        elem.onclick = changeColor;
    }

    function changeColor(event) {
        var color = event.target.getAttribute('color');
        var request = new XMLHttpRequest();
        request.open('GET', './rgb.php?channel=<?=$_GET['channel']?>&color=' + color, true);
        request.send();
    }
</script>
</body>
</html>
