<?php
include("config.inc.php");
include("fonctions.inc.php");

if(isset($_COOKIE['user']) and isset($_POST['focus']))
{
	if($_POST['focus'])
	{
		$sql = "UPDATE users SET focus='1', online='1' WHERE user='".$_COOKIE['user']."'";
		query($sql);
	}
	else
	{
		$sql = "UPDATE users SET focus='0', online='1' WHERE user='".$_COOKIE['user']."'";
		query($sql);
	}
}

?>
