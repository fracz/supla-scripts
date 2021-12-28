<?php
$html = file_get_contents('https://www.zamyslenie.pl/aforyzm');
preg_match('#class="quote-single.+?>(.+?)</p>#s', $html, $match);
echo $match[1];
