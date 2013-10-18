<?php
include '../../../wp-blog-header.php';
$twitter = new Twitter();
header('HTTP/1.1 200 OK');
echo $twitter->getData();
?>