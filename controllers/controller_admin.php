<?php
session_start();
header( 'Content-Type: text/xml; charset=ISO-8859-1' );
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
include_once('../classes/Rooms.php');

if(!isset($_POST['action'])) die("Vous ne pouvez pas appeler cette page directement");

switch($_POST['action'])
{
	// Modifier une annonce
	case 'annonces_modifier':
		$id = $_POST['id'];
		$annonce = $_POST['annonce'];
		$annonce = addslashes(iconv("UTF-8", "ISO-8859-1", $annonce));
		$sql = "UPDATE annonces SET annonce='$annonce' WHERE id='$id'";
		query($sql);
		echo "Annonce modifi&eacute;e";
		// Journalisation
		adminlog("Modification d'une annonce",$sql);
	break;
	
	// Retirer une annonce
	case 'annonces_retirer':
		$id = $_POST['id'];
		$sql = "UPDATE annonces SET afficher='no' WHERE id='$id'";
		query($sql);
		// Journalisation
		adminlog("Retrait d'une annonce",$sql);
	break;
	
	// Modifier un sondage
	case 'poll_titre_modifier':
		$id = $_POST['id'];
		$expiration = fdate($_POST['expiration'],true);
		$titre = $_POST['titre'];
		$titre = addslashes(iconv("UTF-8", "ISO-8859-1", $titre));
		$sql = "UPDATE poll_titres SET question='$titre', expiration='$expiration' WHERE id='$id'";
		query($sql);
		// Journalisation
		adminlog("Modification du titre d'un sondage",$sql);
	break;
	
	// Retirer un sondage
	case 'poll_titre_retirer':
		$id = $_POST['id'];
		$expiration = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));
		$sql = "UPDATE poll_titres SET expiration='$expiration' WHERE id='$id'";
		query($sql);
		// Journalisation
		adminlog("Retrait d'un sondage",$sql);
	break;
	
	// Modifier une option d'un sondage
	case 'poll_option_modifier':
		$id = $_POST['id'];
		$option = $_POST['option'];
		$option = addslashes(iconv("UTF-8", "ISO-8859-1", $option));
		$sql = "UPDATE poll_options SET poll_options.option='$option' WHERE id='$id'";
		query($sql);
		// Journalisation
		adminlog("Modification d'une option d'un sondage",$sql);
	break;
	
	// Retirer une option d'un sondage
	case 'poll_option_retirer':
		$id = $_POST['id'];
		$sql = "DELETE FROM poll_options WHERE id='$id'";
		query($sql);
		// Journalisation
		adminlog("Retrait d'une option de sondage",$sql);
	break;
	
	// Renommer un salon
	case 'rooms_rename':
		$rooms = new Rooms();
		$rn = $rooms->getRoomName($_POST['room_id']);
		$rooms->renameRoom($_POST['room_id'],$_POST['name']);
		$rs = "Salon <i>$rn</i> renomm&eacute; ".$_POST['name'];
		echo $rs;
		// Journalisation
		adminlog("Renommage d'un salon",$rs);
	break;
	
	// Fermer un salon
	case 'rooms_close';
		$rooms = new Rooms();
		$rn = $rooms->getRoomName($_POST['room_id']);
		$rooms->closeRoom($_POST['room_id']);
		$rs = "Salon <i>$rn</i> ferm&eacute;";
		echo $rs;
		// Journalisation
		adminlog("Fermeture d'un salon",$rs);
	break;
	
	// Publiciser un salon
	case 'rooms_publicize':
		$rooms = new Rooms();
		$rn = $rooms->getRoomName($_POST['room_id']);
		$rooms->publicizeRoom($_POST['room_id']);
		$rs = "Salon <i>$rn</i> rendu public";
		echo $rs;
		// Journalisation
		adminlog("Publicisation d'un salon",$rs);
	break;
	
	// Privatiser un salon
	case 'rooms_privatize':
		$rooms = new Rooms();
		$rn = $rooms->getRoomName($_POST['room_id']);
		$rooms->privatizeRoom($_POST['room_id'],$_POST['pass']);
		$rs = "Salon <i>$rn</i> rendu priv&eacute; et prot&eacute;g&eacute; par le mot de passe <i>".$_POST['pass']."</i>";
		echo $rs;
		// Journalisation
		adminlog("Privatisation d'un salon",$rs);
	break;
	
	// Changer le mdp d'un salon privé
	case 'rooms_changepassword':
		$rooms = new Rooms();
		$rn = $rooms->getRoomName($_POST['room_id']);
		$rooms->privatizeRoom($_POST['room_id'],$_POST['newpassword']);
		$rs = "Mot de passe du salon <i>$rn</i> chang&eacute; pour : ".$_POST['newpassword'];
		echo $rs;
		// Journalisation
		adminlog("Changement du mot de passe d'un salon",$rs);
	break;
	
	// Affichage du journal d'administration
	case 'showlog':
		$sql = "SELECT * FROM log_admin ORDER BY id DESC";
		$req = query($sql);
		while($data = mysql_fetch_assoc($req))
			echo fdatetime($data['adt'])." | ".$data['user']." | ".$data['action']."<br />";
	break;
	
	// Analyse des tables
	case 'maintenance':
		$option = $_POST['option'];
		switch($option)
		{
			case 'analyze': $option = "ANALYZE"; $option2 = ""; adminlog("Maintenance : ANALYZE"); break;
			case 'optimize': $option = "OPTIMIZE"; $option2 = ""; adminlog("Maintenance : OPTIMIZE"); break;
			case 'check': $option = "CHECK"; $option2 = "EXTENDED"; adminlog("Maintenance : CHECK EXTENDED"); break;
			case 'repair': $option = "REPAIR"; $option2 = ""; adminlog("Maintenance : REPAIR"); break;
			default : $option = "ANALYZE"; adminlog("Maintenance : ANALYZE"); break;
		}
		echo "<div style=\"text-align:center;margin-top:0.5em;\" class=\"prefs\">Maintenance MySQL : $option $option2</div>";
		
		$rstbl2 = query("SHOW TABLES");
		while (list($tname2) = mysql_fetch_row($rstbl2)) 
		{				
			// Vérification
			$sql2 = "$option TABLE $tname2 $option2";
			$res2 = query($sql2);
						
			// Parcourir toutes les colonnes
			mysql_data_seek($res2, mysql_num_rows($res2)-1);
			$rowst2 = mysql_fetch_assoc($res2);
		
			// Affichage des résultats
			echo " Table {$rowst2['Table']}: ";
			echo "       {$rowst2['Msg_type']} -> {$rowst2['Msg_text']}<br />\n";
		}
	break;
	
	// Scripts
	case 'scripts':
		query("DELETE FROM scripts_admin");
		if(empty($_POST['scripts'])) break;
		$sc = $_POST['scripts'];
		$scs = explode("\n",$sc);
		$adlog = "";
		foreach($scs as $s)
		{
			$s = addslashes(iconv("UTF-8","ISO-8859-1",$s));
			$sql = "INSERT INTO scripts_admin VALUES ('','$s')";
			query($sql);
			$adlog .= "$sql\n";
		}
		echo "Scripts enregistr&eacute;s";
		// Journalisation
		adminlog("Enregistrement des scripts",$adlog);
	break;
	
	// SQL
	case 'sql':
		$query = stripslashes($_POST['query']);
		$tbname = $_POST['table'];
		$sql = "SELECT * FROM $tbname WHERE $query";
		
		echo "<div class=\"prefs\" style=\"text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;\"><u>R&eacute;sultats de la requ&ecirc;te</u><br />$sql</div>";
		
		
		// Protection contre les injections
		if(strripos($query,';') !== FALSE 
		or strripos($query,'DELETE') !== FALSE 
		or strpos($query,'UPDATE') !== FALSE)
			die("Requête non autorisée");
		
		// Protection contre les requêtes vides
		if(empty($query)) die("Veuillez écrire une requête");
		
		// Affichage
		echo "<table style=\"border:1px solid #fff;border-collapse:collapse;\" border=1><tr>";
		
		// Colonnes de la table sélectionnée
		$reqtbl = query("SHOW COLUMNS FROM $tbname");
		while($clname = mysql_fetch_row($reqtbl))
			echo "<td><b><u>".$clname[0]."</u></b></td>";
			
		echo "</tr>";
		// Journalisation
		adminlog("Requête SQL",$sql);
		// Effectuer la requête
		$rows = array();
		$req = mysql_query($sql) or die("<u>Requ&ecirc;te : </u>".$sql."<br /><u>Erreur</u> : ".mysql_error());
		while($data = mysql_fetch_row($req))
		{
			echo "<tr>";
			foreach($data as $d)
				echo "<td>".stripslashes(htmlentities($d))."</td>";
			echo "</tr>";
		}
		echo "</table>";
	break;
	
	// Unban
	case 'unban':
		if(!isset($_POST['victim'])) die("Erreur");
		$victim = $_POST['victim'];
		$sql = "DELETE FROM banlist WHERE user='$victim'";
		query($sql);
		$output = "Bannissement lev&eacute; sur l'utilisateur <u>$victim</u>.";
		// Journalisation
		adminlog("Un-Ban d'un utilisateur",$output);
		//
		$output .= " Veuillez <input type=\"submit\" value=\"recharger l'onglet Admin\" onclick=\"ajaxrefreshspecial('tab12','tab_admin')\" /> pour mettre la liste des bannissement &agrave; jour.";
		echo $output;
	break;
	
	// Supprimer les messages système
	case 'delsystem':
		// Nombre de messages
		echo "Calcul du nombre de messages...<br /><br />";
		$dat = mysql_fetch_assoc(query("SELECT COUNT(id) as cid FROM messages"));
		$total = $dat['cid'];
		$dat = mysql_fetch_assoc(query("SELECT COUNT(id) as cid FROM messages WHERE user='SYSTEM'"));
		$system = $dat['cid'];
		$ratio = round( ($system * 100) / $total );
		// Taille de la table
		$sql = "SHOW TABLE STATUS";
		$result = query($sql);
		$dbSize = 0; // quelle taille ?
		while ($row = mysql_fetch_array($result))
		{
			if($row['Name'] == 'messages')
			{
				$rowsize = $row['Data_length'] + $row['Index_length'];
				$rawsize1 = $rowsize;
				$rowsize = file_size_info($rowsize);
			}
		}
		$tbsize = $rowsize['size'];
		$tbunit = $rowsize['type'];
		// Affichage
		echo "Nombre total de messages :   $total<br />
			  Nombre de messages système : $system ($ratio%)<br />
			  Taille de la table <i>messages</i> : $tbsize $tbunit";
		
		// SUPPRESSION !!
		echo "<br /><br />Suppression des messages système...";
		query("DELETE FROM messages WHERE user='SYSTEM'");
		echo "<br /><br /><b>Suppression effectuée.</b><br /><br />";
		
		// Nombre de messages
		echo "Calcul du nouveau de messages...<br /><br />";
		$dat = mysql_fetch_assoc(query("SELECT COUNT(id) as cid FROM messages"));
		$total2 = $dat['cid'];
		$dat = mysql_fetch_assoc(query("SELECT COUNT(id) as cid FROM messages WHERE user='SYSTEM'"));
		$system2 = $dat['cid'];
		$ratio2 = round( ($system * 100) / $total );
		// Taille de la table
		$sql = "SHOW TABLE STATUS";
		$result = query($sql);
		$dbSize = 0; // quelle taille ?
		while ($row = mysql_fetch_array($result))
		{
			if($row['Name'] == 'messages')
			{
				$rowsize = $row['Data_length'] + $row['Index_length'];
				$rawsize2 = $rowsize;
				$rowsize = file_size_info($rowsize);
			}
		}
		$totaldiff = $total - $total2;
		$tbsize2 = $rowsize['size'];
		$tbunit2 = $rowsize['type'];
		$tbdiff = $rawsize1 - $rawsize2;
		$tbdiff = file_size_info($tbdiff);
		$tbdiff = $tbdiff['size']." ".$tbdiff['type'];
		// Affichage
		$re= "Nombre total de messages :   $total2<br />
			  Nombre de messages système : $system2 ($ratio2%)<br />
			  Taille de la table <i>messages</i> : $tbsize2 $tbunit2<br />
			  <b>Nombre de messages supprimés : $totaldiff</b><br />
			  <b>Gain de place : $tbdiff</b>";
		echo $re;
		// Journalisation
		adminlog("Suppression des messages système",$re);
	break;
}


?>