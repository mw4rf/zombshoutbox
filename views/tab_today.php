<?php
session_start();
header( 'Content-Type: text/xml; charset=UTF-8' );
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
include_once("../classes/Rooms.php");

$rooms = new Rooms();
$now = date("H:i:s");

$rns = $rooms->getRoomsName(true,true);
$rnames = "<option value=\"0\">Public</option>";
foreach($rns as $r)
	if($rooms->hasUserBeenInRoom($r[0]))
	{
		$rnames .= "<option value=\"".$r[0]."\"";
		if(isset($_POST['room_id']) and $_POST['room_id'] == $r[0]) $rnames .= " selected";
		$rnames .= ">".$r[1]."</option>";
	}

echo "<div class=\"tabmaj\">
Derni&egrave;re mise &agrave; jour &agrave; $now
&nbsp;|&nbsp;<input id=\"tab_today_rb\" type=\"submit\" value=\"&#x238B; Actualiser\" onclick=\"ajaxrefresh('tab_today')\">
&nbsp;|&nbsp;Voir le salon <select id=\"today_room\">$rnames</select>
<input type=\"submit\" value=\"&#x2192;\" onclick=\"todayroom()\">
</div>";

if(isset($_SESSION['auth']) and $_SESSION['auth'] == 1 and isset($_COOKIE['key']))
{
	$tomorrow = mktime(0, 0, 0, date("m"), date("d")+1, date("Y"));
	$today  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	
	// ASC ou DESC ?
	if(!isset($_COOKIE['prefs_ajd']) or $_COOKIE['prefs_ajd'] == "desc")
		$ordre = "ORDER BY id DESC";
	else
		$ordre = "ORDER BY id ASC";
		
	// ROOM
	if(isset($_POST['room_id']) and is_numeric($_POST['room_id']))
	{
		$room_id = $_POST['room_id'];
		$room = "AND room_id='$room_id'";
		$rn = $rooms->getRoomName($room_id);
		echo "<div class=\"tabmaj\" style=\"margin-top:3px;\">Salon $rn</div>";
	}
	else
	{
		$room_id = 0;
		$room = "AND room_id='0'";
		echo "<div class=\"tabmaj\" style=\"margin-top:3px;\">Salon Public</div>";
	}
		
	
	// Requête
	$sql = "SELECT * FROM messages WHERE timestamp >= '$today' AND timestamp <= '$tomorrow' AND pm='0' $room $ordre";
	$req = query($sql);

	// Taille de la zone d'affichage
	if(isset($_COOKIE['prefs_height']) and is_numeric($_COOKIE['prefs_height']))
		echo "<div style=\"overflow:auto;height:".$_COOKIE['prefs_height']."px;\">";
	else	
		echo "<div style=\"overflow:auto;height:500px;\">";
	
	// Si salon privé = ne pas afficher
	$go = true;
	if($room_id != 0 and $rooms->isRoomPrivate($room_id))
		if(!$rooms->hasUserBeenInRoom($room_id))
		{
			echo "Vous n'avez pas la permission d'afficher les archives de ce salon";
			$go = false;
			break;
		}
	
	// Affichage des messages
	if($go) while($data = mysql_fetch_assoc($req))
	{	
		// Affichage
		echo "\n<div id=\"".$data['id']."\">";
		echo "<span class=\"msg_date\">[".date("d M - H:i:s",$data["timestamp"])."]</span>"
			."<span class=\"msg_user\">".$data["user"]."&nbsp;&gt;</span> "
			."<span class=\"msg_text\">".iconv("ISO-8859-1","UTF-8",stripslashes($data['message']))."</span>";
		echo "</div>";
	}

		echo "</div>";
}
else
{
	$output = "<div class=\"tabmaj\">Vous devez vous identifier avant d'avoir acc&egrave;s &agrave; cette page</div>";
	echo $output;
}
?>
