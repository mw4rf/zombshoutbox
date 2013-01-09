<?php
session_start();
include_once("config.inc.php");
include_once("fonctions.inc.php");
include_once('classes/Rooms.php');

if(!isset($_POST) or !isset($_SESSION['auth']) or $_SESSION['auth'] != 1) 
	die("Cette op&eacute;ration vous est interdite.");

$stype = $_POST['stype'];

// Order By
$tri = $_POST['tri'];
if($tri == 'ASC') 
{	$tri = "ORDER BY id ASC";	$msg_tri = "R&eacute;sultats tri&eacute;s du plus ancien au plus r&eacute;cent";}
else
{	$tri = "ORDER BY id DESC";	$msg_tri = "R&eacute;sultats tri&eacute;s du plus r&eacute;cent au plus ancien";}

// Commandes ?
$commandes = $_POST['commandes'];
if($commandes) 
{	$commandes = "";	$msg_commandes = "Commandes affich&eacute;es";	}
else 
{	$commandes = "AND command='0'";	$msg_commandes = "Commandes masqu&eacute;es";	}

// Limite
$dolimit = $_POST['dolimit'];
$limit = $_POST['limit'];
if($dolimit and is_numeric($limit)) 
{	$limit = "LIMIT 0,$limit";	$msg_limit = "Affichage limit&eacute; aux ".$_POST['limit']." premiers r&eacute;sultats";	} 
else 
{	$limit = "";	$msg_limit = "Tous les r&eacute;sultats affich&eacute;s";	}

// Salon
$room_id = $_POST['room_id'];
$rooms = new Rooms();
if($rooms->isRoomPrivate($room_id))
	if(!$rooms->hasUserBeenInRoom($room_id))
		die("Ce salon est priv&eacute; : ses archives ne sont accessibles qu'aux utilisateurs qui l'ont rejoint.");
$room_name = $rooms->getRoomName($room_id);
$room_query = "AND room_id='$room_id'";

// Recherche par DATES
if($stype == "dates" and isset($_POST['adebut']) and isset($_POST['afin']) and isset($_COOKIE['user']))
{
	$adebut = tmstp($_POST['adebut']." ".$_POST['tdebut']);
	$afin = tmstp($_POST['afin']." ".$_POST['tfin']);
		
	$sql = "SELECT * FROM messages WHERE timestamp >= '$adebut' AND timestamp <= '$afin' AND pm='0' $commandes $room_query $tri $limit";
	$req = query($sql);
	
	echo "<div class=\"prefs\" style=\"text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;\">
		<b>Messages &eacute;crits entre le ".$_POST['adebut']." &agrave; ".$_POST['tdebut']."
		et le ".$_POST['afin']." &agrave; ".$_POST['tfin']."</b>
		<br /><span style=\"font-size:0.8em\">$msg_tri | $msg_commandes | $msg_limit | Salon $room_name</span>
		</div>";
	
	while($data = mysql_fetch_assoc($req))
	{
		echo "\n<div id=\"".$data['id']."\">";
		echo "<span class=\"msg_date\">[".date("d M - H:i:s",$data["timestamp"])."]</span>"
			."<span class=\"msg_user\">".$data["user"]."&nbsp;&gt;</span> "
			."<span class=\"msg_text\">".iconv("ISO-8859-1","UTF-8",stripslashes($data['message']))."</span>";
		echo "</div>";
	}
}
// Recherche par UTILISATEUR
elseif($stype == "user" and isset($_POST['user']) and isset($_COOKIE['user']))
{
	$user = $_POST['user'];		
	$sql = "SELECT * FROM messages WHERE user='$user' AND pm='0' $commandes $room_query $tri $limit";
	$req = query($sql);
	
	echo "<div class=\"prefs\" style=\"text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;\">
		<b>Messages &eacute;crits par <u>".$_POST['user']."</u></b>
		<br /><span style=\"font-size:0.8em\">$msg_tri | $msg_commandes | $msg_limit | Salon $room_name</span>
		</div>";
	
	while($data = mysql_fetch_assoc($req))
	{
		echo "\n<div id=\"".$data['id']."\">";
		echo "<span class=\"msg_date\">[".date("d M - H:i:s",$data["timestamp"])."]</span>"
			."<span class=\"msg_user\">".$data["user"]."&nbsp;&gt;</span> "
			."<span class=\"msg_text\">".iconv("ISO-8859-1","UTF-8",stripslashes($data['message']))."</span>";
		echo "</div>";
	}
}
// Recherche FULLTEXT
elseif($stype == "fulltext" and isset($_POST['text']) and isset($_COOKIE['user']))
{
	$text = $_POST['text'];
	$text = iconv("UTF-8","ISO-8859-1",addslashes($text));
	$sql = "SELECT * FROM messages WHERE message LIKE '%$text%' AND pm='0' $commandes $room_query $tri $limit";
	$req = query($sql);
	
	echo "<div class=\"prefs\" style=\"text-align:center;font-size:1.1em;margin-top:1em;\">
		<b>Messages contenant <i>&laquo;&nbsp;".$_POST['text']."&nbsp;&raquo;</i></b>
		<br /><span style=\"font-size:0.8em\">$msg_tri | $msg_commandes | $msg_limit | Salon $room_name</span>
		</div>";
	
	while($data = mysql_fetch_assoc($req))
	{
		echo "\n<div id=\"".$data['id']."\">";
		echo "<span class=\"msg_date\">[".date("d M - H:i:s",$data["timestamp"])."]</span>"
			."<span class=\"msg_user\">".$data["user"]."&nbsp;&gt;</span> "
			."<span class=\"msg_text\">".iconv("ISO-8859-1","UTF-8",stripslashes($data['message']))."</span>";
		echo "</div>";
	}
}
// Affichage des graphiques
elseif($stype == "graphiques" and isset($_POST['ville']) and isset($_POST['graphique']) and isset($_COOKIE['user']))
{
	$graph = $_POST['graphique'];
	$ville = stripslashes($_POST['ville']);
	
	echo "<div class=\"command_tab\" style=\"text-align:center;\"><img src=\"graphiques/$graph.php?ville=$ville\" alt=\"Cr&eacute;ation du graphique...\"/></div>";
}
// Affichage des citoyens de la ville
elseif($stype == "citoyens" and isset($_POST['ville']) and isset($_COOKIE['user']))
{
	include("views/view_rencontres.php");
}
?>






















