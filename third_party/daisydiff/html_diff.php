<link rel="stylesheet" type="text/css" href="css/diff.css">

<?php

include 'src/HTMLDiff.php';

$html1 = file_get_contents("1.html");
$html2 = file_get_contents("2.html");

echo $html1;
echo $html2;

$diff = new HTMLDiffer();

echo $diff->htmlDiff($html1,$html2);

?>