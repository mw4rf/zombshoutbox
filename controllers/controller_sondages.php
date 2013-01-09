<?php
session_start();
header( 'Content-Type: text/xml; charset=ISO-8859-1' );
include_once("../config.inc.php");
include_once("../fonctions.inc.php");

if(isset($_POST['action']) and $_POST['action'] == 'vote')
{
	$sondage_id = $_POST['sondage_id'];
	$option_id = $_POST['option_id'];
	$user = addslashes($_COOKIE['user']);
	
	$sql = "SELECT * FROM poll_votes WHERE user = '$user' AND sondage_id = '$sondage_id'";
	$count = mysql_num_rows(query($sql));
	
	if($count < 1)
	{
		$sql = "INSERT INTO poll_votes VALUES ('','$user','$sondage_id','$option_id')";
		query($sql);
	}
}
elseif(isset($_POST['action']) and $_POST['action'] == 'delvote')
{
	$sondage_id = $_POST['sondage_id'];
	$user = $_COOKIE['user'];
	
	$sql = "DELETE FROM poll_votes WHERE user='$user' AND sondage_id = '$sondage_id'";
	query($sql);
}

?>