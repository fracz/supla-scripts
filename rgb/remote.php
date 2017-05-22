<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SUPLA RGB Remote</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        #container {
            width: 100vw;
            height: 100vh;
        }

        #container a {
            cursor: pointer;
            display: block;
            float: left;
            width: 33.33%;
            height: 33.33%;
        }
    </style>
</head>
<body>
<?php
$colors = ['000000', 'ffffff', 'ffff00', 'ff0000', 'ff8800', 'ff00ff', '0000ff', '00ffff', '00ff00'];
?>
<div id="container">
    <?php foreach ($colors as $color): ?>
        <a style="background-color: #<?= $color ?>" onclick="changeColor()" color="<?= $color ?>"></a>
    <?php endforeach; ?>
</div>

<script>
    function calculateContainerDimensions() {
        // https://gist.github.com/joshcarr/2f861bd37c3d0df40b30
        var w = window,
            d = document,
            e = d.documentElement,
            g = d.getElementsByTagName('body')[0],
            x = w.innerWidth || e.clientWidth || g.clientWidth,
            y = w.innerHeight || e.clientHeight || g.clientHeight;
        var container = document.getElementById('container');
        container.style.width = x + 'px';
        container.style.height = y + 'px';
    }

    calculateContainerDimensions();

    window.addEventListener('resize', calculateContainerDimensions, false);

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
