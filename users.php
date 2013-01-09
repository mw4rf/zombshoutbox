<?php
error_reporting(0);
session_start();
include_once("config.inc.php");
include_once("fonctions.inc.php");
include_once('classes/Rooms.php');

echo "<script>loadSweetTitles();</script>";

// Fonction d'affichage de la liste des utilisateurs
function userslist($req,$active_room)
{
	while( $data = mysql_fetch_assoc($req) )
	{	
		$status = "";
	
		// Auth ? // Admin ?
		$sql2 = "SELECT * FROM users_registered WHERE user='".$data['user']."'";
		$req2 = query($sql2);
		$count = num_rows($sql2);
		if($count > 0 and isset($_SESSION['auth']) and $_SESSION['auth'] == 1) $status .= "+i"; else $status .= "-i";
	
		// Voiced ?
		if($data['voice']) $status .= "+v"; else $status .= "-v";
	
		// API key ?
		if(isset($data['apikey']) and !empty($data['apikey'])) $status .= "+k"; else $status .= "-k";
	
		// Admin ?
		if($count > 0)
		{
			$data2 = mysql_fetch_assoc($req2);
			if($data2['admin']) $status = "@";
		}
		
		// User
		$user = $data['user'];
		
		if($data['afk'])
		{
			$user = "<span class=\"user-afk\">$user</span>";
		}
		else
		{
			// Focus
			if($data['focus'])
				$user = "<span class=\"user-online\">$user</span>";
			else
				$user = "<span class=\"user-background\">$user</span>";
		}
		
		// Dans la même salle que moi ?
		if($active_room == 0)
			$room = "&bull;";
		else
		{
			$rooms = new Rooms();
			$sameroom = $rooms->isUserInRoom($active_room,$data['user']);
			if($sameroom)
				$room = "<span style=\"font-size:1em;\">&bull;</span>";
			else
			{
				$room = "";
				$user = "<s>$user</s>";
			}
		}
				
		// Afficher
		echo "<tr>";
		echo "<td>$room</td>";
		echo "<td style=\"font-size:0.8em;font-style:normal;font-weight:normal;text-align:right;\">";
		echo "$status";
		echo "</td>";
		echo "<td style=\"text-align:left;\">";
		echo "\n<span class=\"user\" name=\"normal\" id=\"user_".$data['id']."\" onclick=\"mp('".$data['user']."')\" onmouseover=\"enlight('".$data['user']."',true)\" onmouseout=\"enlight('".$data['user']."',false)\">";
		echo "&nbsp;".$user;
		echo "</span>";
		echo "</td></tr>";
	}
}

function butin()
{
	include_once('objets.php');
	// Objets trouvés
	$sql = "SELECT * FROM butin_found ORDER BY nombre DESC";
	$count = num_rows($sql);
	$cols = 3; // nombre de colonnes
	$col = 1;
	$passage = 0;
	$output = "";
	$retdisp = false;
	if($count > 0)
	{
		$retdisp = true;
		$req = query($sql);
		$output .= "<span class=\"tnamealt\">Butin d'exp&eacute;dition</span><br />";
		$output .= "<table width=\"100%\">";
		while($data = mysql_fetch_assoc($req))
		{
			$nombre = $data['nombre'];
			$objet = stripslashes($data['objet']);
			$userslist = $data['users'];
			
			// Utilisateurs
			$usersall = explode("," , $userslist);
			$users = array();
										
			foreach($usersall as $k=>$u)
				if(empty($users[$u]))
					$users[$u] = 1;
				else
					$users[$u] = $users[$u] + 1;
									
			$ud = "";
			foreach($users as $u=>$n)
				$ud .= "$u : $n<br />";
			$ud = trim($ud,"<br />");
			
			// Générer l'affichage
			if(!empty($objets[$objet])) 
				$objet = "<abbr title=\"<b><i>$objet</i></b><br />$ud\"><img src=\"http://data.hordes.fr/gfx/icons/item_".$objets[$objet].".gif\" title=\"\" /></abbr>";
			
			$out = "<td>$nombre&nbsp;$objet</td>";
			//$out = "<td>$col</td>"; // debug
			
			// Générer la case du tableau
			if($col == 1)
				{ $output .= "<tr>"; $output .= $out; $col++; }
			elseif($col == $cols)
				{ $output .= $out; $output .= "</tr>"; $col = 1; }
			else
				{ $output .= $out; $col++; }
						
			// Terminer de compléter le tableau
			if($passage == $count - 1)
			{
				for($i = $col - 1 ; $i < $cols ; $i++)
					$output .= "<td>&nbsp;</td>";
				$output .= "</tr>";
			}
			$passage++;
						
		}
		$output .= "</table>";
	}
	// Objets à ramener
	$sql = "SELECT * FROM butin_wanted ORDER BY nombre DESC";
	$count = num_rows($sql);
	$cols = 3; // nombre de colonnes
	$col = 1;
	$passage = 0;
	if($count > 0)
	{
		$retdisp = true;
		$req = query($sql);
		$output .= "<span class=\"tnamealt\">Butin &agrave; ramener</span><br />";
		$output .= "<table width=\"100%\">";
		while($data = mysql_fetch_assoc($req))
		{
			$nombre = $data['nombre'];
			$objet = stripslashes($data['objet']);
			
			if(!empty($objets[$objet])) 
				$objet = "<abbr title=\"Objet : <i>$objet</i>\"><img src=\"http://data.hordes.fr/gfx/icons/item_".$objets[$objet].".gif\" title=\"\" /></abbr>";
			
			$out = "<td>$nombre&nbsp;$objet</td>";
			//$out = "<td>$col</td>"; // debug
			
			// Générer la case du tableau
			if($col == 1)
				{ $output .= "<tr>"; $output .= $out; $col++; }
			elseif($col == $cols)
				{ $output .= $out; $output .= "</tr>"; $col = 1; }
			else
				{ $output .= $out; $col++; }
						
			// Terminer de compléter le tableau
			if($passage == $count - 1)
			{
				for($i = $col - 1 ; $i < $cols ; $i++)
					$output .= "<td>&nbsp;</td>";
				$output .= "</tr>";
			}
			$passage++;
		}
		$output .= "</table>";
	}
	if($retdisp)
		echo $output = "<div id=\"container_today\">$output</div>";
}

//-------------------------------------------------------------------------------------------------------------------------

// Affichage de la date et de l'heure actuelles
echo "<div id=\"container_today\">";
echo "\n<span class=\"tlabel\">".date("d M Y")."</span><br />"
	  ."<span class=\"tname\">".date("H\hi")."</span>";
// Affichage des MP
echo "<div id=\"new_mp\"></div>";
echo "</div>";

echo "<div id=\"container_users\">";
echo "<table id=\"table_users\" width=\"100%\">";

// Affichage des utilisateurs
$rooms = new Rooms();
$active_room = $rooms->getActiveRoom();

$sql = "SELECT * FROM users WHERE online='1' AND kicked !='1'";
$count = num_rows($sql);
if($count > 0) 
{
	echo "<tr><td colspan=\"3\" class=\"tnamealt\"><u>Utilisateurs connect&eacute;s</u> ($count)</td></tr>";
	$req = query($sql);
	userslist($req,$active_room);
}
// end table
echo "</table></div>";

// Affichage des salles
if(isset($_SESSION['auth']) and $_SESSION['auth'] == 1)
{
	$rooms = new Rooms();
	$rs = $rooms->getRoomsName();
	echo "<div class=\"timer\">";
	echo "<table width=\"100%\">";
	echo "<tr><td colspan=\"4\" class=\"tnamealt\"><u>Salons de discussion</u></td></tr>";
	// Salle publique active ou non ?
	if($rooms->getActiveRoom() == 0)
		echo "<tr class=\"d1\"><td id=\"room_0\">&#x2192;</td><td>Public</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
	else
		echo "<tr style=\"cursor:pointer;\" onMouseOver=\"onlight()\" onMouseOut=\"offlight()\" onClick=\"joinRoom('0')\"><td id=\"room_0\">&#x2318;</td><td>Public</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
	// Parcourir les salles
	foreach($rs as $r)
	{
		$rid = $rooms->getRoomId($r);
		$ro = iconv("ISO-8859-1", "UTF-8", $r);
		
		// Salon privé ?
		$priv = $rooms->isRoomPrivate($rid);
		if($priv)
			$priv = "#";
		else
			$priv = "";
		
		// L'utilisateur est dans cette salle
		if($rooms->isUserInRoom($rid))
		{
			if($rooms->getActiveRoom() == $rid)
				echo "<tr class=\"user-online\"><td id=\"room_$rid\">&#x2192;</td><td>$ro</td><td>(".$rooms->countRoomMembers($rid).")</td><td>$priv</td></tr>";
			else
				echo "<tr class=\"user-background\" style=\"cursor:pointer;\" onMouseOver=\"onlight()\" onMouseOut=\"offlight()\" onClick=\"joinRoom('$rid')\"><td id=\"room_$rid\">&bull;</td><td>$ro</td><td>(".$rooms->countRoomMembers($rid).")</td><td>$priv</td></tr>";
		}
		// L'utilisateur n'est pas dans cette salle
		else
			echo "<tr class=\"user-afk\"><td>&nbsp;</td><td>$ro</td><td>&nbsp;</td><td>$priv</td></tr>";
	}
	echo "</table></div>";
}

// Affichage du timer
$sql = "SELECT * FROM timer WHERE actif='1'";
$req = query($sql);

echo "<div id=\"container_timers\">";
while($data = mysql_fetch_assoc($req))
{
	$temps = $time = $data['timer'];
	$tname = stripslashes($data['tname']);
	if(!empty($data['label'])) $label = "<span class=\"tlabel\">".stripslashes($data['label'])."</span><br />"; else $label = "";

	// Calcul du temps qu'il reste
	$temps = $temps - time();
	$temps = date("H:i",$temps);
	$time = date("H\hi",$time+3600);

	// Protection contre les images
	if(isset($_COOKIE['prefs_images']) and $_COOKIE['prefs_images'] == "Non")
		$label = strip_selected_tags($label, array("img"));

	echo "\n<div class=\"timer\">"
		."<span class=\"tname\">$tname</span><br />"
		.$label
		."<span class=\"ttime\">$temps</span><br />"
		."<span class=\"tlabel\">Fin &agrave; $time</span>"
		."</div>";
}
echo "</div>";

// Affichage du butin
butin();

// Tweeter
//Problème avec cette fonction : l'API twitter ne peut être utilisée que 100 fois par heure par le même client.
//include_once('twitter.php');
//twitter_read();
?>