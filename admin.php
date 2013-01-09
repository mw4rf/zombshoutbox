<?php
session_start();
include_once("config.inc.php");
include_once("fonctions.inc.php");

$cmd = explode(" ",$message);     
$pm = array();
$output = "";

$timestamp = time();

// Vérifier que l'utilisateur qui a envoyé la commande est administrateur
$sql = "SELECT * FROM users_registered WHERE user='$user'";
$req = query($sql);
$data = mysql_fetch_assoc($req);
$isadmin = $data['admin'];

// Vérifie que l'utilisateur est bien connecté
if($isadmin)
{
	if(isset($_SESSION['auth']) and $_SESSION['auth'] == 1)
		$isadmin = true;
	else
		$isadmin = false;
}


if(!$isadmin)
	$output = "Vous ne poss&eacute;dez pas les droits suffisants pour ex&eacute;cuter cette commande.";
else
// Traitement de la commande
switch($cmd[1])
{
	// Jeux
	case 'pf':
		$user = $_COOKIE['user'];
		$msg = "<b>$user</b> lance une pi&egrave;ce <img src=\"smilies/small_coin.gif\" /> qui retombe <i><b>sur la tranche !</b></i>";
		$output = "Omg LOL <b>$user</b>, you pwnd d4 w0rld !";
		$sqlx = "INSERT INTO messages VALUES ('','$msg','$timestamp','$user', '1', '0', 'no','0')";
		query($sqlx);
	break;
	
	case 'carte':
		$user = $_COOKIE['user'];
		$timestamp = time();
		$couleurs = array("Coeur", "Carreau", "Pique", "Tr&egrave;fle");
		$couleur = $couleurs[round(rand(0,3))];
		
		$msgs[0] = "<b>$user</b> tire <img src=\"smilies/item_cards.gif\" /> <i><b>l'As de $couleur</b></i>";
		$msgs[1] = "<b>$user</b> tire <img src=\"smilies/item_cards.gif\" /> <i><b>le Roi de $couleur</b></i>";
		$msgs[2] = "<b>$user</b> tire <img src=\"smilies/item_cards.gif\" /> <i><b>la Reine de $couleur</b></i>";
		$msgs[3] = "<b>$user</b> tire <img src=\"smilies/item_cards.gif\" /> <i><b>le Valet de $couleur</b></i>";
		$msgs[4] = "<b>$user</b> tire <img src=\"smilies/item_cards.gif\" /> <i><b>le 10 de $couleur</b></i>";

		foreach($msgs as $msg)
		{
			$msg = addslashes($msg);
			$sqlx = "INSERT INTO messages VALUES ('','$msg','$timestamp','$user', '1', '0', 'no','0')";
			query($sqlx);
		}
		
		$output = "OMFG LOL !! It's a <u><b><i>ROYAL FLUSH !!!</i></b></u> Dude you pwnd d4 w0rld !";
	break;
	
	// Kick
	case 'kick':
		if(!empty($cmd[2]))
		{
			$victim = $cmd[2];
			$sql = "UPDATE users SET kicked='1' WHERE user='$victim'";
			query($sql);
			$output = "Utilisateur <u>$victim</u> &eacute;ject&eacute;.";
		}
	break;
	
	// Ban
	case 'ban':
		if(!empty($cmd[2]) and !empty($cmd[3]))
		{
			// Récupération des données
			$victim = $cmd[2];
			$time = $cmd[3];
			$now = date("Y-m-d H:i:s");
			// Récupération de l'IP
			$sql = "SELECT * FROM users WHERE user='$victim'";
			$req = query($sql);
			$data = mysql_fetch_assoc($req);
			$ip = $data['ip'];
			// Admin qui a utilisé la commande
			$admin = $_COOKIE['user'];
			// Motif du bannissement
			$reason = " ";
			if(isset($cmd[4]))
			{
				for($i = 4; $i < count($cmd) ; $i++)
					$reason .= $cmd[$i]." ";
				$reason = trim($reason);
			}
			// Construction de la requête
			if($time == "forever")
			{
				$sql = "INSERT INTO banlist VALUES ('', '$victim', '$ip','$now', '$now', '1','$admin','$reason')";
				$out = "pour toujours.";
			}
			else
			{
				$time = eval("return ".$time.";");
				$to = date("Y-m-d H:i:s", mktime(date("H"),date("i")+$time,date("s"),date("m"), date("d"), date("Y")));
				$sql = "INSERT INTO banlist VALUES ('', '$victim', '$ip', '$now', '$to', '0','$admin','$reason')";
				$out = "jusqu'au ".fdatetime($to,"d M y, H\hi").".";
			}
			query($sql,"query($sql)");
			
			// kick après ban ^^
			// ne fonctionne pas : le ban empêche l'affichage dans tous les cas, l'utilisateur est bloqué sur la shout et rien ne s'affiche
			//$sql = "UPDATE users SET kicked='1' WHERE user='$victim'";
			//query($sql);
			// affichage public de la sanction :-)
			
			$output = "Utilisateur <u>$victim</u> ($ip) banni $out Raison : <i>$reason</i>";
			// Journalisation
			adminlog("Ban d'un utilisateur",$output);
		}
		else
		{
			$output = "<u>Syntaxe de la commande <b>ban</b></u> : /@ ban &lt;user&gt; &lt;time&gt; [raison]<br />L'argument <b>time</b> peut &ecirc;tre <u><i>forever</i></u> pour un bannissement permanent ou une dur&eacute;e exprim&eacute;e <i>en nombre de <u>minutes</u></i>. NB : vous pouvez exprimer le nombre de minutes par une op&eacute;ration math&eacute;matique ; p. ex. 60*2 pour 2 heures. L'argument <b>raison</b> est facultatif.";
		}
	break;
	
	// Unban
	case 'unban':
		if(!empty($cmd[2]))
		{
			$victim = $cmd[2];
			if(preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)/",$victim)) // adresse IP
			{
				$sql = "DELETE FROM banlist WHERE ip='$victim'";
				$out = "IP";
			}
			else // nom d'utilisateur
			{
				$sql = "DELETE FROM banlist WHERE user='$victim'";
				$out = "utilisateur";
			}
			query($sql);
			$output = "Bannissement lev&eacute; sur l'$out <u>$victim</u>";
			// Journalisation
			adminlog("Un-Ban d'un utilisateur",$output);
		}
		else
		{
			$output = "<u>Syntaxe de la commande <b>unban</b></u> : /@ unban [user|IP]<br />Cette commande permet d'enlever le bannissement qui frappe, au choix, un utilisateur ou une adresse IP.";
		}
	break;
	
	// affichage de la banlist
	case 'banlist':
		$sql = "SELECT * FROM banlist WHERE end > NOW() OR forever='1' ORDER BY id DESC";
		$count = num_rows($sql);
		$req = query($sql);
		$output = "<u><b>Liste des utilisateurs bannis</b></u> (total : $count)<br />";
		$output .= "<table width=\"100%\"><tr><td>Utilisateur</td><td>D&eacute;but</td><td>Fin</td><td>Admin</td><td>Raison</td></tr>";
		while($data = mysql_fetch_assoc($req))
		{
			$victim = $data['user'];
			$start = fdatetime($data['start'],"d/m/Y H\hi");
			if($data['forever']) $end = "Jamais"; else $end = fdatetime($data['end'],"d/m/Y H\hi");
			$raison = $data['reason'];
			$admin = $data['admin'];
			
			$output .= "<tr>"
					."<td>$victim</td>"
					."<td>$start</td>"
					."<td>$end</td>"
					."<td>$admin</td>"
					."<td>$raison</td>"
					."</tr>";
		}
		$output .= "</table>";
		// Journalisation
		adminlog("Affichage de la banlist");
	break;
	
	// Empêcher un utilisateur de parler ou couper le canal
	case '-v':
		// Faire taire un utilisateur
		if(!empty($cmd[2]))
		{
			$victim = $cmd[2];
			$sql = "UPDATE users SET voice='0' WHERE user='$victim'";
			query($sql);
			$output = "Utilisateur <u>$victim</u> unvoiced.";
			// Avertir l'utilisateur
			$sql = "INSERT INTO messages VALUES ('','Vous avez &eacute;t&eacute; priv&eacute; de parole sur ce canal par $user.','$timestamp','SYSTEM', '0', '$victim', '1','0')";
			query($sql);
			// Journalisation
			adminlog("Unvoice d'un utilisateur","Victime : $victim");
		}
		// Couper le canal
		else
		{
			$sql = "UPDATE users SET voice='0'";
			query($sql);
			$output = "Tous utilisateurs unvoiced.";
			// Avertir les utilisateur
			$req = query("SELECT * FROM users WHERE voice='0' AND online='1'");
			while($data = mysql_fetch_assoc($req))
			{
				$sql = "INSERT INTO messages VALUES ('','Vous avez &eacute;t&eacute; priv&eacute; de parole sur ce canal par $user.','$timestamp','SYSTEM', '0', '".$data['user']."', '1','0')";
				query($sql);
			}
			// Journalisation
			adminlog("Unvoice général");
		}
	break;
	
	// Rétablir la parole d'un utilisateur ou rétablir le canal
	case '+v':
		// Faire taire un utilisateur
		if(!empty($cmd[2]))
		{
			$victim = $cmd[2];
			$sql = "UPDATE users SET voice='1' WHERE user='$victim'";
			query($sql);
			$output = "Utilisateur <u>$victim</u> voiced.";
			// Avertir l'utilisateur
			$sql = "INSERT INTO messages VALUES ('','Votre droit de parole a &eacute;t&eacute; r&eacute;tabli sur ce canal par $user.','$timestamp','SYSTEM', '0', '$victim', '1','0')";
			query($sql);
			// Journalisation
			adminlog("Voice d'un utilisateur","Victime : $victim");
		}
		// Couper le canal
		else
		{
			$sql = "UPDATE users SET voice='1'";
			query($sql);
			$output = "Tous utilisateurs voiced.";
			// Avertir les utilisateur
			$req = query("SELECT * FROM users WHERE voice='1' AND online='1'");
			while($data = mysql_fetch_assoc($req))
			{
				$sql = "INSERT INTO messages VALUES ('','Votre droit de parole a &eacute;t&eacute; r&eacute;tabli sur ce canal par $user.','$timestamp','SYSTEM', '0', '".$data['user']."', '1','0')";
				query($sql);
			}
			// Journalisation
			adminlog("Voice général");
		}
	break;
	
	// Annonces
	case 'annonce':
		// Nouvelle annonce
		if(!empty($cmd[2]))
		{
			$annonce = "";
			for($i = 2; $i < count($cmd) ; $i++)
				$annonce .= $cmd[$i]." ";
			$annonce = addslashes(trim($annonce));
			$annonce_conv = $annonce;
			$annonce_conv = iconv("UTF-8", "ISO-8859-1", $annonce_conv);
			
			$sql = "INSERT INTO annonces VALUES ('','$annonce_conv','yes')";
			query($sql);
			
			$output = "Annonce mise en ligne : <i>&laquo; $annonce &raquo;</i>";
			// Journalisation
			adminlog("Ajout d'une annonce","$annonce");
		}
		// Ne plus afficher l'annonce
		else
		{
			$sql = "UPDATE annonces SET afficher = 'no'";
			query($sql);
			$output = "Annonce retir&eacute;e";
			// Journalisation
			adminlog("Retrait d'une annonce");
		}
	break;
	
	//MP Global
	case 'mp':
		if(!empty($cmd[2]))
		{
			$mp = "";
			for($i = 2; $i < count($cmd) ; $i++)
				$mp .= $cmd[$i]." ";
			$mp = addslashes(trim($mp));
			$mp = iconv("UTF-8", "ISO-8859-1", $mp);
			
			$sql = "SELECT * FROM users_registered";
			$req = query($sql);
			while($data = mysql_fetch_assoc($req))
			{
				$destinataire = $data['user'];
				$sql2 = "INSERT INTO messages VALUES ('','$mp','".time()."','SYSTEM','0','$destinataire','yes','0')";
				query($sql2);
			}
			// Journalisation
			adminlog("MP global","$mp");
		}
	break;
	
	// Evenement COA
	case 'event':
		if(empty($cmd[5]))
			$output = "Syntaxe de la commande : <span class=\"comm\">/event &lt;date&gt; &lt;heure de d&eacute;but&gt; &lt;heure de fin&gt; &lt;message&gt;</span><br /> La date peut &ecirc;tre &eacute;crite au format JJ/MM/AAAA ou en fran&ccedil;ais : &laquo; ajd &raquo; (pour aujourd'hui), &laquo; demain &raquo; ou &laquo; apd &raquo; (pour apr&egrave;s-demain)<br />Les heures de d&eacute;but et de fin doivent &ecirc;tre &eacute;crites au format HH:MM.";
		else
		{
			// Texte
			$texte = "";
			for($i = 5 ; $i < count($cmd) ; $i++)
				$texte .= $cmd[$i]." ";
			$texte = addslashes(trim($texte));
			
			// Date
			switch($cmd[2])
			{
				case "ajd": $date = date("Y-m-d"); break;
				case "demain": $date = date("Y-m-d", mktime(0,0,0,date("m"),date("d")+1,date("Y"))); break;
				case "apd": $date = date("Y-m-d", mktime(0,0,0,date("m"),date("d")+2,date("Y"))); break;
				default: $date = fdate($cmd[2],true); break;
			}
			
			// Heures début/fin
			$debut = $date." ".$cmd[3];
			$fin = $date." ".$cmd[4];
						
			// Insérer
			$sql = "INSERT INTO events (id,user,action,sdate,edate) VALUES ('','SYSTEM','$texte','$debut','$fin')";
			query($sql);
			
			// Affichage
			$output = "&Eacute;v&eacute;nement ajout&eacute; : le <b>".fdate($date)."</b> de <b>".$cmd[3]."</b> &agrave; <b>".$cmd[4]."</b> : <i>&laquo; ".stripslashes($texte)." &raquo;</i>";
			// Journalisation
			adminlog("Nouvel événement",$output);
		}
	break;

	// Stats
	case 'stats':
		if(!empty($cmd[2]))
		{
			$quser = $cmd[2];
						
			$nummpub = num_rows("SELECT * FROM messages WHERE pm = '0' AND command = '0' AND user='$quser'");
			$numcommandpub = num_rows("SELECT * FROM messages WHERE command = '1' AND user='$quser'");
			$numcommandpriv = num_rows("SELECT * FROM messages WHERE pm = '$quser' AND command = '1'");
			$numpmr = num_rows("SELECT * FROM messages WHERE pm = '$quser' AND command = '0'");
			$numpme = num_rows("SELECT * FROM messages WHERE command = '0' AND user='$quser' AND pm != '0'");
			
			$output = "Statistiques de l'utilisateur <b><u>$quser</u></b>"
					. "<br />Nombre de messages publics &eacute;crits : <b>$nummpub</b>"
					."<br />Nombre de commandes envoy&eacute;es : <b>$numcommandpub</b>"
					."<br />Nombre de messages syst&egrave;me re&ccedil;us : <b>$numcommandpriv</b>"
					."<br />Nombre de messages priv&eacute;s re&ccedil;us : <b>$numpmr</b>"
					."<br />Nombre de messages priv&eacute;s envoy&eacute;s : <b>$numpme</b>";
					
			$sql = "SELECT * FROM users_registered WHERE user = '$quser'";
			$count = num_rows($sql);
			if($count < 1)
				$output .= "<br />Utilisateur <u>non</u> enregistr&eacute;.";
			else
			{
				$req = query($sql);
				$data = mysql_fetch_assoc($req);
				if($data['admin']) $output .= "<br />L'utilisateur est administrateur.";
				$output .= "<br />Derni&egrave;re IP : ".$data['ip'];
			}
			// Journalisation
			adminlog("Statistiques de l'utilisateur","Victime : $quser");
		}
		else
		{
			$output = "<u><b>Statistiques de la base de donn&eacute;es</b></u><br />";
			// Taille de la base de données
			$sql = "SHOW TABLE STATUS";
			$result = query($sql);
			// Journalisation
			adminlog("Statistiques de la base de données");
			$dbSize = 0; // quelle taille ?
			while ($row = mysql_fetch_array($result))
			{
				// Pour le total
				$dbSize += $row['Data_length'] + $row['Index_length'];
				// Table par table
				switch($row['Name'])
				{
					case 'messages': 
						$count = num_rows("SELECT * FROM messages");
						$rowname = "Taille colonne <i>Messages</i>";
						$rowsize = $row['Data_length'] + $row['Index_length'];
						$rowsize = file_size_info($rowsize);
						$output .= "$rowname : <b>".$rowsize['size']." ".$rowsize['type']."</b> ($count lignes)<br />";
					break;
					case 'notes': 
						$count = num_rows("SELECT * FROM notes");
						$rowname = "Taille colonne <i>Notes</i>";
						$rowsize = $row['Data_length'] + $row['Index_length'];
						$rowsize = file_size_info($rowsize);
						$output .= "$rowname : <b>".$rowsize['size']." ".$rowsize['type']."</b> ($count lignes)<br />";
					break;
					case 'events': 
						$count = num_rows("SELECT * FROM events");
						$rowname = "Taille colonne <i>Calendriers</i>";
						$rowsize = $row['Data_length'] + $row['Index_length'];
						$rowsize = file_size_info($rowsize);
						$output .= "$rowname : <b>".$rowsize['size']." ".$rowsize['type']."</b> ($count lignes)<br />";
					break;
				}
			}
			$dbSizeKo = file_size_info($dbSize);
			$output .= "Taille de la base de donn&eacute;es : <b>".$dbSizeKo['size']." ".$dbSizeKo['type']."</b> ($dbSize Octets)";
			$numusers = num_rows("SELECT * FROM users_registered");
			$output .= "<br />Nombre d'utilisateurs enregistr&eacute;s : <b>$numusers</b>";
			$nummpub = num_rows("SELECT * FROM messages WHERE pm = '0'");
			$nummpriv = num_rows("SELECT * FROM messages WHERE pm != '0'");
			$output .= "<br />Nombre de messages publics : <b>$nummpub</b><br />Nombre de messages priv&eacute;s : <b>$nummpriv</b>";
		}
	break;
	
	default:
		$output = "Commande invalide";
	break;
}

// Envoi de la commande en MP
$pm['to'] = $user;
$user = "SYSTEM";

?>