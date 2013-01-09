<?php
include("config.inc.php");
include("fonctions.inc.php");

if(isset($_COOKIE['user']))
{
	$sql = "UPDATE users SET online='0' WHERE user='".$_COOKIE['user']."'";
	query($sql);

	
	$quit = "[".date("H:i")."] <b>".$_COOKIE['user']."</b> vient de partir";
	$tmp = time();
	$sql = "INSERT INTO messages VALUES('','$quit','$tmp','SYSTEM','1','0','no','0')";
	query($sql);
	
}
?>
