<?php
session_start();
header( 'Content-Type: text/xml; charset=UTF-8' );
include_once("../config.inc.php");
include_once("../fonctions.inc.php");

if(isset($_SESSION['auth']) and $_SESSION['auth'] == 1)
{
	
	if(isset($_COOKIE['prefs_height']) and is_numeric($_COOKIE['prefs_height']))
		echo "<div id=\"innermplist\" style=\"overflow:auto;height:".$_COOKIE['prefs_height']."px;\">";
	else	
		echo "<div id=\"innermplist\" style=\"overflow:auto;height:500px;\">";
	
	include("view_mp.php");
	echo "</div>";
}
else
{
	$output = "<div class=\"tabmaj\">Vous devez vous identifier avant d'avoir acc&egrave;s &agrave; la messagerie priv&eacute;e.</div>";
	echo $output;
}
?>