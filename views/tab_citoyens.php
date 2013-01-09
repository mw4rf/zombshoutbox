<?php
//header( 'Content-Type: text/xml; charset=ISO-8859-1' );
if(file_exists('config.inc.php')) include_once('config.inc.php'); else include_once('../config.inc.php');
if(file_exists('fonctions.inc.php')) include_once('fonctions.inc.php'); else include_once('../fonctions.inc.php');

// L'utilisateur shoutbox est-il un membre de la méta ?
if(!isset($_COOKIE['user']))
	$ismembre = false;
else
{
	$user = $_COOKIE['user'];
	$sql = "SELECT * FROM coa_users WHERE name='$user'";
	$count = num_rows($sql);
	
	if($count) 
		$ismembre = true; 
	else 
		$ismembre = false;
}
?>
<div class="prefs" style="text-align:center;">
	<input id="tab_citoyens_rb" type="submit" value="&#x2318; Membres" onclick="ajaxrefresh('tab_citoyens')">
	&nbsp;
	<input type="submit" value="&#x2318; Historique" onclick="ajaxrefreshoption('tab_citoyens','historique')">
	<?php if($ismembre) { ?>
	&nbsp;
	<input type="submit" value="&#x2318; Modification des coalitions" onclick="ajaxrefreshoption('tab_citoyens','modif_coa')">
	<?php } ?>
</div>

<?php

if(isset($_COOKIE['prefs_height']) and is_numeric($_COOKIE['prefs_height']))
	echo "<div id=\"innercitoyens\" style=\"overflow:auto;height:".$_COOKIE['prefs_height']."px;\">";
else	
	echo "<div id=\"innercitoyens\" style=\"overflow:auto;height:500px;\">";

// LISTE DES MEMBRES
if(!isset($_GET['get_prop']))
{

//echo date("H:i:s");

// Stats
$coa_sql = "SELECT * FROM coa_users";
$countall = num_rows($coa_sql);
$coa_sql = "SELECT * FROM coa_users WHERE ville_id > 0";
$countactifs = num_rows($coa_sql);

echo "<div class=\"tabmaj\">Total des membres : $countall | Membres actifs : $countactifs</div>";

# COA
# CITOYEN | INFOS

// Récupérer les coa une par une
$coa_sql = "SELECT * FROM coa_coas ORDER BY id ASC";
$coa_req = query($coa_sql);

while($coa_data = mysql_fetch_assoc($coa_req))
{
	echo "\n<table class=\"citoyens\">\n";
	
	echo "<tr class=\"tbheader\">"
		."<td class=\"avatar\">Coa #".$coa_data['id']."</td>"
		."<td class=\"nom\">Citoyen</td>"
		."<td class=\"jdate\">Inscription</td>"
		."<td class=\"ville\">Ville</td>"
		."<td class=\"twitter\">Twitter</td>"
		."<td class=\"hero\">Statut</td>"
		."<td class=\"position\">Position</td>"
		."</tr>";
	
	$coa_id = $coa_data['id'];
	
	// Récupérer chaque citoyen de cette coa
	$cit_sql = "SELECT * FROM coa_users WHERE coa_id = '$coa_id'";
	$req = query($cit_sql);
	
	while($data = mysql_fetch_assoc($req))
	{
		// Données de la base de données
		$id		=	$data['id'];
		$name	=	$data['name'];
		$leader	=	$data['leader'];
		$jdate	=	fdate($data['join_date'], false);
		$apikey	=	$data['apikey'];
		$ville	=	$data['ville_id'];
		$twitter =	$data['twitter'];
		
		if(!empty($twitter))
			$twitter = "<a href=\"http://twitter.com/$twitter\" target=\"_blank\">@$twitter</a>";
		
		// Avatars
		if(!empty($data['avatar']))
			$avatar =	"<img src=\"http://imgup.motion-twin.com/".$data['avatar']."\" alt=\"Avatar\" width=\"90\" height=\"30\" />";
		else
			$avatar = "<img src=\"smilies/noavatar.jpg\" alt=\"Avatar\" width=\"90\" height=\"30\" />";
		
		if($leader) $leader = "leader"; else $leader = "membre";
		
		// Données du XML
		$ville_full	=	"Pas en jeu";
		$hero		=	"?";
		$pos		=	"?";
	
		// Récupérations des données du perso depuis le flux XML
		if(!empty($apikey))
		{
			$doc = getxml($data['apikey']);
			
			// Ne pas poursuivre si le joueur n'est pas en jeu
			$error = "";
			$errs = $doc->getElementsByTagName('error');
			foreach($errs as $err)
				$error = $err->getAttribute('code');
				
			if(empty($error) and $error != "not_in_game"  and $error != "horde_attacking")
			{
			
				// Nom de la ville
				$cities = $doc->getElementsByTagName('city');
				foreach($cities as $city)
				{
					$nom_ville = iconv("UTF-8","ISO-8859-1",$city->getAttribute('city'));
					$xville = $city->getAttribute('x');
					$yville = $city->getAttribute('y');
				}
				$games = $doc->getElementsByTagName('game');
					foreach($games as $game)
						$days = $game->getAttribute('days');
				if(empty($days)) $days = "(?)"; else $days = " (J$days)";
				$ville_full = $nom_ville . $days;
			
				// Récupération des données du citoyen
				$citizens = $doc->getElementsByTagName('citizen');
				foreach($citizens as $citizen)
					if($citizen->getAttribute('name') == $name)
					{
						// Ame de héro et profession
						$hero = $citizen->getAttribute('job');
						switch($hero)
						{
							case 'guardian': 
								$hero = "<img src=\"smilies/h_guard.gif\" alt=\"logo\" />&nbsp;Gardien"; 
								$ame = "guard";
							break;
							case 'collec': 
								$hero = "<img src=\"smilies/h_collec.gif\" alt=\"logo\" />&nbsp;Fouineur";
								$ame = "collec";
							break;
							case 'eclair': 
								$hero = "<img src=\"smilies/h_ranger.gif\" alt=\"logo\" />&nbsp;Éclaireur";
								$ame = "eclair";
							break;
							case 'basic': 
								$hero = "Citoyen"; 
								$ame = "0";
							break;
							case '':
								$hero = "Héro en devenir"; 
								$ame = "0";
							break;
							default: 
								$hero = ""; 
								$ame = "0";
							break;
						}
						// Avatar
						$avatarXML = $citizen->getAttribute('avatar');
						// Si avatar existe dans la bdd
						if(!empty($data['avatar']))
						{
							// Si avatar existe dans XML
							if(!empty($avatarXML))
								if($avatarXML == $data['avatar']) // Si avatar XML = bdd (= utiliser bdd)
								{
									$avatarraw = $data['avatar'];
									$avatar = "<img src=\"http://imgup.motion-twin.com/".$data['avatar']."\" alt=\"Avatar\" width=\"90\" height=\"30\" />";
								}
								else // Si avatar XML != bdd (= mettre à jour bdd)
								{
									$sql_avatar = "UPDATE coa_users SET avatar = '$avatarXML' WHERE id = '".$data['id']."'";
									query($sql_avatar);
									$avatar = "<img src=\"http://imgup.motion-twin.com/$avatarXML\" alt=\"Avatar\" width=\"90\" height=\"30\" />";
								}
							else // si avatar XML n'est pas défini (= utiliser avatar bdd)
							{
								$avatarraw = $data['avatar'];
								$avatar = "<img src=\"http://imgup.motion-twin.com/".$data['avatar']."\" alt=\"Avatar\" width=\"90\" height=\"30\" />";
							}
						}
						// Si avatar n'existe pas dans la bdd
						else
						{
							if(!empty($avatarXML)) // si avatar XML est défini (= l'insérer dans la bdd)
							{
								$avatarraw = $avatarXML;
								$avatar = "<img src=\"http://imgup.motion-twin.com/$avatarXML\" alt=\"Avatar\" width=\"90\" height=\"30\" />";
								$sql_avatar = "UPDATE coa_users SET avatar = '$avatarraw' WHERE id = '".$data['id']."'";
								query($sql_avatar);
							}
							else // si avatar XML n'est pas défini (= pas d'avatar)
							{
								$avatar = "<img src=\"smilies/noavatar.jpg\" alt=\"Avatar\" width=\"90\" height=\"30\" />";
							}			

						}
						
						// Position sur la carte
						if($citizen->getAttribute('x') == $xville and $citizen->getAttribute('y') == $yville)
							$pos = "En Ville";
						elseif($citizen->getAttribute('out') == 1)
							$pos = "[".$citizen->getAttribute('x').",".$citizen->getAttribute('y')."]";
						else
							$pos = "?";
					}
			
				// Mettre à jour l'association ville-joueur, si nécessaire
			
				// Vérifier si la ville existe
				$sql_ville_1 = "SELECT * FROM coa_villes WHERE name='".addslashes($nom_ville)."'";
				$count = num_rows($sql_ville_1);
				$ville_id = "";
					// La ville n'existe pas, la créer
					if($count < 1 and !empty($nom_ville))
					{
						$today = date("Y-m-d");
						$sql_ville_2 = "INSERT INTO coa_villes VALUES ('','".addslashes($nom_ville)."', '$today','')";
						query($sql_ville_2);
						$sql_ville_3 = "SELECT * FROM coa_villes WHERE name='".addslashes($nom_ville)."'";
						$req_ville_3 = query($sql_ville_3);
						$data_ville_3 = mysql_fetch_assoc($req_ville_3);
						$ville_id = $data_ville_3['id'];
					}
					// La ville existe, récupérer son id
					{
						$sql_ville_3 = "SELECT * FROM coa_villes WHERE name='".addslashes($nom_ville)."'";
						$req_ville_3 = query($sql_ville_3);
						$data_ville_3 = mysql_fetch_assoc($req_ville_3);
						$ville_id = $data_ville_3['id'];
					}
				// Comparer l'id de la ville avec celle de la ville du joueur
				if($ville_id != $ville)
				{
					// Mettre à jour le joueur avec l'id de sa nouvelle ville
					$sql_ville_4 = "UPDATE coa_users SET ville_id = '$ville_id' WHERE id = '$id'";
					query($sql_ville_4);
				}
				
				// Insérer la relation ville<=>joueur si elle n'existe pas déjà
				$sql_ville_5 = "SELECT * FROM coa_users_villes WHERE ville_id = '$ville_id' AND user_id = '$id'";
				$count = num_rows($sql_ville_5);
				if($count < 1)
				{
					$sql_ville_6 = "INSERT INTO coa_users_villes VALUES ('','$ville_id','$id','$ame')";
					query($sql_ville_6);
				}
				
			}
		}
		
		echo "<tr class=\"$leader\">"
			."<td class=\"avatar\">$avatar</td>"
			."<td class=\"nom\">$name</td>"
			."<td class=\"jdate\">$jdate</td>"
			."<td class=\"ville\">".htmlentities($ville_full)."</td>"
			."<td class=\"twitter\">$twitter</td>"
			."<td class=\"hero\">$hero</td>"
			."<td class=\"position\">$pos</td>"
			."</tr>";	
	}
	
	echo "</table><p>&nbsp;</p>";
}
} // fin Liste des membres
######################################################################################################
elseif($_GET['get_prop'] == 'historique')
{
	$sql = "SELECT * FROM coa_villes ORDER BY id DESC";
	$req = query($sql);
	while($data = mysql_fetch_assoc($req))
	{
		$vid = $data['id'];
		$vnom = $data['name'];
		$start = fdate($data['start'],false);
		if(!empty($data['end']) and $data['end'] != "0000-00-00")
		{
			$end = fdate($data['end'],false);
			$dur = round( ( strtotime($data['end']) - strtotime($data['start']) ) /60/60/24 );
			$dur .= " J :";
		}
		else
		{
			$end = "en cours";
			$dur = "";
		}
		$citoyens = "";	

		// Récupérer les citoyens de cette ville
		$sql2 = "SELECT * FROM coa_users_villes WHERE ville_id = '$vid'";
		$req2 = query($sql2);
		while($data2 = mysql_fetch_assoc($req2))
		{
			$cid = $data2['user_id'];
			$ame = $data2['ame'];

			// Récupérer les données du citoyen
			$sql3 = "SELECT * FROM coa_users WHERE id='$cid' ORDER BY coa_id ASC";
			$req3 = query($sql3);
			while($data3 = mysql_fetch_assoc($req3))
			{
				if($ame)
					$ame = "<img src=\"smilies/h_$ame.gif\" alt=\"$ame\" />";
				else
					$ame = "";

				$citoyens .= $data3['name']."$ame, ";
			}
		}
		$citoyens = trim($citoyens,", ");
		

		// Affichage de la ville
		if(!empty($citoyens))
		echo	 "<table class=\"citoyens\" cellspacing=0>"
				."<tr class=\"tbheader\">"
					."<td width=\"60%\">".htmlentities($vnom)."</td>"
					."<td width=\"40%\" style=\"text-align:right;\">($dur $start &#x2192; $end)</td></tr>"
				."<tr><td colspan=\"2\">$citoyens</td></tr>"
				."</table><p>&nbsp;</p>";
	}
} // fins historique
######################################################################################################
elseif($_GET['get_prop'] == 'modif_coa' and $ismembre)
{
	// Liste des coalitions
	$coalitions = "";
	$sql = "SELECT * FROM coa_coas ORDER BY id DESC";
	$req = query($sql);
	while($data = mysql_fetch_assoc($req))
	{
		$cit = "";
		$sql2 = "SELECT * FROM coa_users WHERE coa_id = '".$data['id']."'";
		$count = num_rows($sql2);
		if($count < 5 and $count > 0) // n'afficher que les coalitions qui ont encore une place de libre
		{
			$req2 = query($sql2);
			while($data2 = mysql_fetch_assoc($req2))
				$cit .= $data2['name']."/";
			$cit = trim($cit,"/");
			$coalitions .= "<option value=\"".$data['id']."\">#".$data['id']." $cit</option>";
		}
		elseif($count == 0)
		{
			$coalitions .= "<option value=\"".$data['id']."\">".$data['leader']."</option>";
		}
	}
	// Liste des utilisateurs
	$coausers = array();
	$sql = "SELECT * FROM coa_users ORDER BY name ASC";
	$req = query($sql);
	while($data = mysql_fetch_assoc($req))
		$coausers[] = stripslashes($data['name']);
	
	?>
	<div id="coas_result" class="msg_text" style="text-align:center;"></div>
	<div class="prefs">
		Cr&eacute;er une nouvelle coalition avec 
		<select id='cit_coa_new_membre'>
		<?php
		foreach($coausers as $cu)
			echo "<option value=\"$cu\">$cu</option>";
		?>
		</select>
		pour leader
		&nbsp;
		<input type="submit" value="Go &#x2192;" onclick="cit_coas('new');" />
	</div>
	
	<div class="prefs">
		Placer 
		<select id='cit_coa_modif_membre'>
		<?php
		foreach($coausers as $cu)
			echo "<option value=\"$cu\">$cu</option>";
		?>
		</select>
		dans la coalition
		<select id="cit_coa_modif_coas">
		<?php echo $coalitions; ?>
		</select>
		&nbsp;
		<input type="submit" value="Go &#x2192;" onclick="cit_coas('modif');" />
	</div>
	
	<div class="prefs">
		Dissoudre la coalition
		<select id="cit_coa_del_coas">
		<?php echo $coalitions; ?>
		</select>
		&nbsp;
		<input type="submit" value="Go &#x2192;" onclick="cit_coas('del');" />
	</div>
	<?php
	// membres orphelins
	$sql = "SELECT * FROM coa_users WHERE coa_id NOT IN (SELECT id FROM coa_coas)";
	if(num_rows($sql) > 0)
	{
		echo "<div class=\"prefs\">";
		echo "<div style=\"text-align:center;\"><u></b>Joueurs orphelins</b></u></div>";
		$req = query($sql);
		$out = "";
		while($data = mysql_fetch_assoc($req))
		{
			$out .= htmlentities(stripslashes($data['name'])).", ";
		}
		$out = trim($out,", ");
		echo $out;
		echo "</div>";
	}	
}
?>
</div>