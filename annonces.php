<?php
error_reporting(0);
include_once("config.inc.php");
include_once("fonctions.inc.php");

$sql = "SELECT annonce FROM annonces WHERE afficher = 'yes' ORDER BY id DESC LIMIT 0,1";
$count = num_rows($sql);

if($count > 0)
{
	$req = query($sql);
	$data = mysql_fetch_assoc($req);
	$annonce = entitiescharset(stripslashes($data['annonce']));

	echo "<u>Annonce</u>: ".iconv("UTF-8", "ISO-8859-1", $annonce);
}

?>