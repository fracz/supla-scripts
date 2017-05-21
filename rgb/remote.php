<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <!--		<meta charset="UTF-8" />-->
    <!--		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> -->
    <!--		<meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <title>Blueprint: Responsive Full Width Grid</title>
    <!--		<meta name="description" content="Blueprint: Responsive Full Width Grid Layout" />-->
    <!--		<meta name="keywords" content="100% grid, layout, columns, images, thumbnails, responsive, full width grid, image grid, css, jquery" />-->
    <!--		<meta name="author" content="Codrops" />-->
    <!--		<link rel="shortcut icon" href="../favicon.ico">-->
    <!--		<link rel="stylesheet" type="text/css" href="css/default.css" />-->
    <!--		<link rel="stylesheet" type="text/css" href="css/component.css" />-->
    <!--		<script src="js/modernizr.custom.js"></script>-->
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

    //    var request = new XMLHttpRequest();
    //    request.open('POST', '/my/url', true);
    //    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    //    request.send(data);
</script>
</body>
</html>
