<?php
session_start();
header( 'Content-Type: text/xml; charset=ISO-8859-1' );
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
include_once("../classes/Rooms.php");

if(isset($_POST['action']) and $_POST['action'] == 'join' and isset($_POST['room_id']))
{
	$room_id = $_POST['room_id'];
	$user = $_COOKIE['user'];
	
	$rooms = new Rooms();
	
	$rooms->setActiveRoom($room_id,$user);
}

?>