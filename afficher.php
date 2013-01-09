<?php
header( 'Content-Type: text/xml; charset=UTF-8' );
include_once("config.inc.php");
include_once("fonctions.inc.php");
include_once('classes/Rooms.php');

$user = $_COOKIE['user'];
$upuser = ucfirst($user);
if(isset($_COOKIE['lastaction'])) $lastaction = $_COOKIE['lastaction']; else $lastaction = 0;

// Récupérer le dernier message vu par l'utilisateur
$sql = "SELECT * FROM users where user='$user'";
$req = query($sql);
$data = mysql_fetch_assoc($req);
$msgid = $data['refresh'];
$kicked = $data['kicked'];

// Récupère les salles
$rooms = new Rooms();
$jrs = $rooms->getJoinedRooms();
if(!$jrs or !is_array($jrs))
	$joined_rooms = "AND room_id = '0'";
else
{
	$joined_rooms = "AND (room_id = '0' OR ";
	foreach($jrs as $jr)
		$joined_rooms .= "room_id = '$jr' OR ";
	$joined_rooms = trim($joined_rooms," OR ");
	$joined_rooms .= ")";
}

// Récupérer les derniers messages
$sql = "SELECT * FROM messages WHERE id > '$msgid' AND (pm='0' OR pm='$user' OR pm='$upuser' OR user='$user') $joined_rooms ORDER BY id";
//$up = num_rows($sql); $down = $up - $msg_limit;
//$sql = "SELECT * FROM messages WHERE timestamp > '$timestamp' ORDER BY id LIMIT $down,$up";
$req = query($sql);

$lastid = false;
$output = "";

while( $data = mysql_fetch_assoc($req) )
{		
	$auteur = $data['user'];
	$message = stripslashes(displayparsing(iconv("ISO-8859-1","UTF-8",$data['message'])));
	$ignore = false;
	if($data['command'] == '1') $iscommande = true; else $iscommande = false;
	if($data['pm']) $ismp = true; else $ismp = false;
	
	// Salle
	$room = $rooms->getRoomName($data['room_id']);
	if($data['room_id'] != 0)
		$room_name = " <span class=\"roomname\">#".htmlentities($room)."</span>";
	else
		$room_name = "";
	
	// Messages d'arrivée et de départ
	if( (!isset($_COOKIE['prefs_msgad']) or $_COOKIE['prefs_msgad'] == "Non" ) and $iscommande and $auteur == "SYSTEM")
	{
		if(substr($message,-15) == "vient d'arriver" or substr($message,-15) == "vient de partir")
			continue;
	}
	
	// Scripts ADMIN
	$sql_as = "SELECT * FROM scripts_admin";
	$count_as = num_rows($sql_as);
	if($count_as)
	{
		include_once("scripts.php");
		$req_as = query($sql_as);
		while($data_as = mysql_fetch_assoc($req_as))
		{
			$instruction = stripslashes($data_as['script']);
			eval(analyze($instruction));
		}
	}
	// end Scripts ADMIN
	
	// Scripts utilisateur
	if(isset($_COOKIE['prefs_activescripts']) AND $_COOKIE['prefs_activescripts'] == "Oui")
	{
		include_once("scripts.php");
		$sql_scripts = "SELECT * FROM scripts WHERE user = '$user'";
		$req_scripts = query($sql_scripts);
	
		while($data_scripts = mysql_fetch_assoc($req_scripts))
		{
			$instruction = stripslashes($data_scripts['script']);
			eval(analyze($instruction));		
		}
	}
	if($ignore) continue;
	// end Scripts
	
	// Afficher le message normal
	if(!$data['command'])
	{
		// Affiche le message public
		if(!$data['pm']) 
		{ 	$class = "msg_text"; $pm = ""; } 
		// Affiche le MP
		else 
		{ 
			$class = "pm_new";
			//blink
			if(isset($_COOKIE['prefs_mpblink']) and $_COOKIE['prefs_mpblink'] == "Oui")
				$class .= " blink";
			
			if($data['user'] == $user) // affichage chez l'expéditeur
			{
				$pm = "<i>(&agrave; ".$data['pm'].")</i>&nbsp;"; 
			}
			else // affichage chez le destintaire
			{
				$pm = "<i>(priv&eacute;)</i>&nbsp;";
				// Marquer le MP comme lu
				$sql2 = "UPDATE messages SET pm_unread='no' WHERE id='".$data['id']."'";
				query($sql2);
			}
		}
		
		$output .= "\n<div id=\"".$data['id']."\" class=\"msg\" style=\"display:none;cursor:pointer;\" onclick=\"addmenu(this)\"><div>";
		$output .= "<span class=\"msg_date\">[".date("d M - H:i:s",$data["timestamp"])."$room_name]</span>";
		$output	.="<span class=\"msg_user\" onmouseover=\"enlight('$auteur',true)\" onmouseout=\"enlight('$auteur',false)\">"
					."<span class=\"user_m\" onclick=\"mp('$auteur')\">$auteur</span>&nbsp;$pm&gt;</span> ";
		$output	.="<span class=\"$class\">".$message."</span></div>";
		$output .= "</div>";
	}
	// Affiche la commande
	else
	{
		$output .= "\n<div id=\"".$data['id']."\" class=\"command\" style=\"display:none;\"><div>"
			.$message."</div></div>";
	}
	
	$lastid = $data['id'];	
}

// Mise à jour de l'utilisateur
$sql = "UPDATE users SET refresh='$lastid', lastaction='".time()."', online='1' WHERE user='$user'";
if(is_numeric($lastid)) query($sql);

// Affichage
if($kicked)
	echo "<br /><b>Vous avez &eacute;t&eacute; eject&eacute;.</b> Veuillez vous reconnecter et faire plus attention &agrave; vos paroles.";
elseif(isset($_COOKIE['login']))
	echo $output;
else
	echo iconv("ISO-8859-1","UTF-8","<br />Vous avez été déconnecté après un temps d'inactivité. Veuillez vous reconnecter.");
?>