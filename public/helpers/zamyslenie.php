<?php
$html = file_get_contents('https://www.zamyslenie.pl/aforyzm');
preg_match('#<blockquote.+?<h2.+?>(.+)</h2>#s', $html, $match);
echo $match[1];
