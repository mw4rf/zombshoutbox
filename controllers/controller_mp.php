<?php
session_start();
include("../config.inc.php");
include("../fonctions.inc.php");

$user = $_COOKIE['user'];

// Marquer comme lu
if(isset($_GET['mark']) and $_GET['mark'] == "read")
{
	$sql = "UPDATE messages SET pm_unread = 'no' WHERE pm = '$user'";
	query($sql);
}
// Supprimer
elseif( isset($_GET['mark']) and $_GET['mark'] == "delete" and isset($_GET['mpid']) and is_numeric($_GET['mpid']) )
{
	$mpid = $_GET['mpid'];
	$sql = "DELETE FROM messages WHERE id = '$mpid'";
	query($sql);
}
else
{
// MP système ?
if(isset($_COOKIE['prefs_mpsystem']))
{
	if($_COOKIE['prefs_mpsystem'] == "Non")
		$mpsystem = "AND user != 'SYSTEM'";
	else
		$mpsystem = "";
}
else
	$mpsystem = "";	

// Afficher le compteur
$sql = "SELECT * FROM messages WHERE pm = '$user' AND pm_unread = 'yes' $mpsystem";
$count = num_rows($sql);

if($count > 0)
	if($count == 1)
		echo "1 nouveau message";
	else
		echo "$count nouveaux messages";
}
?>