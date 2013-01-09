<?php
header ("Content-type: image/png");

include_once('../config.inc.php');
include_once('../classes/HordesXML.php');
include_once('../classes/Map.php');

// Création de l'image
imagepng($image);
?>