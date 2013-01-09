<?php
//header( 'Content-Type: text/xml; charset=ISO-8859-1' );
if(file_exists('config.inc.php')) include_once('config.inc.php'); else include_once('../config.inc.php');
if(file_exists('fonctions.inc.php')) include_once('fonctions.inc.php'); else include_once('../fonctions.inc.php');
?>
<ul id="innertabs_profil" class="tabs">
	<li class="tab" id="tabprofil1"><a href="#innertab_profil_1"><img style="vertical-align: middle;" src="smilies/toolbar/profil/statistiques.png" />&nbsp;Statistiques</a></li>
	<li class="tab" id="tabprofil2"><a href="#innertab_profil_2"><img style="vertical-align: middle;" src="smilies/toolbar/profil/preferences.png" />&nbsp;Préférences générales</a></li>
	<li class="tab" id="tabprofil3"><a href="#innertab_profil_3"><img style="vertical-align: middle;" src="smilies/toolbar/profil/scripts.png" />&nbsp;Scripts personnalisés</a></li>
</ul>
<?php
if(isset($_COOKIE['user']))
{
	$stats = true;
	$user = $_COOKIE['user'];
	
	// Nombre total de messages publics
	$mtotal = num_rows("SELECT * FROM messages WHERE pm='0' AND command='0'");
	// Nombre de messages publics de l'utilisateur
	$mpub = num_rows("SELECT * FROM messages WHERE user='$user' AND pm='0' AND command='0'");
	// Proportion messages publics de l'utilisateur / nombre total de messages publics
	$mpubp = ceil($mpub * 100 / $mtotal);
	
	// Nombre de messages privés de l'utilisateur
	$mpriv = num_rows("SELECT * FROM messages WHERE (user='$user' AND pm!='0') OR (pm='$user' AND user!='SYSTEM') AND command='0'");
	// Nombre de messages privés ENVOYES par l'utilisateur
	$mprive = num_rows("SELECT * FROM messages WHERE user='$user' AND pm!='0' AND command='0'");
	// Nombre de messages privés RECUS par l'utilisateur
	$mprivr = num_rows("SELECT * FROM messages WHERE pm='$user' AND user!='SYSTEM' AND command='0'");
	// Nombre de messages SYSTEM reçus
	$mprivs = num_rows("SELECT * FROM messages WHERE pm='$user' AND user='SYSTEM'");
	
	// Nombre total de commandes
	$ctotal = num_rows("SELECT * FROM messages WHERE command='1' AND user!='SYSTEM'");
	// Nombre de commandes de l'utilisateur
	$cpub = num_rows("SELECT * FROM messages WHERE user='$user' AND command='1'");
	// Proportion messages publics de l'utilisateur / nombre total de messages publics
	$cpubp = ceil($mpub * 100 / $mtotal);
	
	// Nombre de vilains dénoncés
	$cvilains = num_rows("SELECT * FROM parias WHERE user='$user'");
	
	// Nombre d'entrées dans le calendrier
	$cevents = num_rows("SELECT * FROM events WHERE user='$user'");
	
	// Nombre de notes prises
	$cnotes = num_rows("SELECT * FROM notes WHERE user='$user'");
	
	
} else $stats = false;

// IP de l'utilisateur
if (isset($_SERVER['HTTP_X_FORWARD_FOR']) and !empty($_SERVER['HTTP_X_FORWARD_FOR']))
	$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
else
	$ip = $_SERVER['REMOTE_ADDR'];


$hostaddress = gethostbyaddr($ip);

	
// Navigateur de l'utilisateur
$browser = $_SERVER['HTTP_USER_AGENT'];
?>

<div id="tab_prefs">
<form action="set_prefs.php" method="post">
	
<div id="innertab_profil_1" class="intab" style="width:100%">
<div class="prefs" style="margin-bottom:5px;text-align:center;"><input id="tab_prefs_rb" type="submit" value="&#x238B; Actualiser" onclick="ajaxrefreshspecial('tab6','tab_prefs')"></div>
	<!--// Table du profil -->
	<table width="100%">
	
	<tr class="prefs">	
		<td width="40%">
		Votre pseudonyme : <b><?php echo $_COOKIE['user']; ?></b>
		<?php
		$sql = "SELECT * FROM users_registered WHERE user='".$_COOKIE['user']."'";
		$count = num_rows($sql);
		if($count > 0)
		{
			if(isset($_SESSION['auth']) and $_SESSION['auth'] == 1)
				{ echo " [Enregistr&eacute; et identifi&eacute;]"; }
			else
				{ echo " [Enregistr&eacute;]"; }
			
			$req = query($sql);
			$data = mysql_fetch_assoc($req);	
			if($data['admin'])
				{ echo " <u>Administrateur</u>"; }
		}
		?>
		</td>	
		<td width="60%">
		Votre clé API Hordes : 
		<?php
		if(!empty($_COOKIE['key']))
			echo "<input type=\"text\" size=\"35\" name=\"apikey\" value=\"".$_COOKIE['key']."\" />";
		else
			echo "<input type=\"text\" size=\"35\" name=\"apikey\" value=\"[non fournie]\" />";
		?>
		<input type="submit" name="prefs_valid" value="Modifier &rarr;"/>
		</td>
	</tr>
	
	<tr class="prefs">	
		<td>Votre adresse IP : <b><?php echo $ip; ?></b><br /><i><?php echo $hostaddress; ?></i></td>
		<td>Votre navigateur : <i><?php echo $browser; ?></i></td>
	</tr>
	
	<tr class="prefs">	
		<td>Nombre de messages publics : <b><?php if($stats) echo $mpub; else echo "?"; ?></b></td>
		<td>soit <b><?php if($stats) echo $mpubp; else echo "?"; ?>%</b> du total de messages publics</td>
	</tr>
	
	<tr class="prefs">
		<td>Nombre de messages privés : <b><?php if($stats) echo $mpriv; else echo "?"; ?></b></td>
		<td>dont <b><?php if($stats) echo $mprive; else echo "?"; ?></b> envoyés et <b><?php if($stats) echo $mprivr; else echo "?"; ?></b> reçus</td>
	</tr>
	
	<tr class="prefs">	
		<td>Nombre de commandes envoyées : <b><?php if($stats) echo $cpub; else echo "?"; ?></b></td>
		<td>soit <b><?php if($stats) echo $cpubp; else echo "?"; ?>%</b> du total des commandes traitées</td>
	</tr>
	
	<tr class="prefs">
		<td>Nombre de messages système reçus : <b><?php if($stats) echo $mprivs; else echo "?"; ?></b></td>
		<td>Nombre actuel de notes : <b><?php if($stats) echo $cnotes; else echo "?"; ?></b></td>
	</tr>
	
	<tr class="prefs">
		<td>Nombre de vilains dénoncés : <b><?php if($stats) echo $cvilains; else echo "?"; ?></b></td>
		<td>Nombre d'entrées ajoutées au calendrier : <b><?php if($stats) echo $cevents; else echo "?"; ?></b></td>
	</tr>
	
	<!--// Fin table du profil -->
	</table>
	</div><!--// intab -->
	
<div id="innertab_profil_2" class="intab" style="width:100%">
<!--// Table des préférences -->
<table width="100%">
	
	<tr class="prefs">
		<td>&nbsp;</td>
		<td style="text-align:center;"><input type="submit" name="prefs_valid" value="Enregistrer les préférences &rarr;"/></td>
		<td>&nbsp;</td>
	</tr>
	
	<tr class="prefs">
	<td>Thème graphique de la ShoutBox</td>
	<td>
	<select style="width:100%;" name="prefs_skincss">
		<?php
		$skins = array("Hordes", "Valhalla", "Sobre", "Minimal","Terminal","Crepuscule");
		foreach($skins as $skin)
			if($skin == "Hordes" and !isset($_COOKIE['prefs_skincss']))
				echo "<option value=\"$skin\" selected>$skin (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_skincss']) and $skin == $_COOKIE['prefs_skincss'])
				echo "<option value=\"$skin\" selected>$skin (votre choix)</option>";
			elseif($skin == "Hordes" and isset($_COOKIE['prefs_skincss']))
				echo "<option value=\"$skin\">$skin (par défaut)</option>";
			else
				echo "<option value=\"$skin\">$skin</option>";
		?>
	</select>
	</td>
	<td>Global</td>
	</tr>

	<tr class="prefs">
	<td>Taille des polices</td>
	<td>
	<select style="width:100%;" name="prefs_sizecss">
		<?php
		$skins = array("Minuscule", "Petit", "Normal", "Grand", "Enorme");
		foreach($skins as $skin)
			if($skin == "Petit" and !isset($_COOKIE['prefs_sizecss']))
				echo "<option value=\"$skin\" selected>$skin (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_sizecss']) and $skin == $_COOKIE['prefs_sizecss'])
				echo "<option value=\"$skin\" selected>$skin (votre choix)</option>";
			elseif($skin == "Petit" and isset($_COOKIE['prefs_sizecss']))
				echo "<option value=\"$skin\">$skin (par défaut)</option>";
			else
				echo "<option value=\"$skin\">$skin</option>";
		?>
	</select>
	</td>
	<td>Global</td>
	</tr>
	
	<tr class="prefs">
	<td>Afficher les annonces en haut à gauche</td>
	<td>
	<select style="width:100%;" name="prefs_annonces">
		<?php
		$anns = array("Oui", "Non");
		foreach($anns as $ann)
			if($ann == "Non" and !isset($_COOKIE['prefs_annonces']))
				echo "<option value=\"Non\" selected>Non (par défaut)</option>";
			elseif($ann == "Non" and isset($_COOKIE['prefs_annonces']) and $_COOKIE['prefs_annonces']=="Non")
				echo "<option value=\"$ann\" selected>$ann (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_annonces']) and $ann == $_COOKIE['prefs_annonces'])
				echo "<option value=\"$ann\" selected>$ann (votre choix)</option>";
			else
				echo "<option value=\"$ann\">$ann</option>";
		?>
	</select>
	</td>
	<td>Global</td>
	</tr>

	<tr class="prefs">
	<td>Hauteur de la zone d'affichage</td>
	<td>
	<select style="width:100%;" name="prefs_height">
		<?php
		if(!isset($_COOKIE['prefs_height'])) $selected = " selected"; 
		elseif($_COOKIE['prefs_height'] == 500) $selected = " selected"; 
		else $selected = "";
		
		for($i = 200 ; $i < 1600 ; $i += 25)
		{
			if($i == 500)
				echo "<option value=\"$i\"$selected>$i pixels (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_height']) and $_COOKIE['prefs_height'] == $i)
				echo "<option value=\"$i\" selected>$i pixels (votre choix)</option>";
			else
				echo "<option value=\"$i\">$i pixels</option>";
		}
		?>
	</select>
	</td>
	<td>ShoutBox/Aujourd'hui/Msg Priv</td>
	</tr>
	
	<tr class="prefs">
	<td>Largeur de la zone d'affichage</td>
	<td>
	<select style="width:100%;" name="prefs_width">
		<?php
		if(!isset($_COOKIE['prefs_width'])) $selected = " selected"; 
		elseif($_COOKIE['prefs_width'] == 70) $selected = " selected"; 
		else $selected = "";
		
		for($i = 40 ; $i < 90 ; $i += 5)
		{
			if($i == 60)
				echo "<option value=\"$i\"$selected>$i% (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_width']) and $_COOKIE['prefs_width'] == $i)
				echo "<option value=\"$i\" selected>$i% (votre choix)</option>";
			else
				echo "<option value=\"$i\">$i%</option>";
		}
		?>
	</select>
	</td>
	<td>Global</td>
	</tr>
	
	<tr class="prefs">
	<td>Activer le rafraîchissement automatique de la ShoutBox</td>
	<td>
	<select style="width:100%;" name="prefs_msg_refresh">
		<?php
		$anns = array("Oui", "Non");
		foreach($anns as $ann)
			if($ann == "Non" and !isset($_COOKIE['prefs_msg_refresh']))
				echo "<option value=\"Non\" selected>Non (par défaut)</option>";
			elseif($ann == "Non" and isset($_COOKIE['prefs_msg_refresh']) and $_COOKIE['prefs_msg_refresh']=="Non")
				echo "<option value=\"$ann\" selected>$ann (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_msg_refresh']) and $ann == $_COOKIE['prefs_msg_refresh'])
				echo "<option value=\"$ann\" selected>$ann (votre choix)</option>";
			else
				echo "<option value=\"$ann\">$ann</option>";
		?>
	</select>
	</td>
	<td>ShoutBox</td>
	</tr>
	
	<tr class="prefs">
	<td>Nombre de messages avant rafraîchissement</td>
	<td>
	<select style="width:100%;" name="prefs_msg_refresh_count">
		<?php
		if(!isset($_COOKIE['prefs_msg_refresh_count'])) $selected = " selected"; 
		elseif($_COOKIE['prefs_msg_refresh_count'] == 50) $selected = " selected"; 
		else $selected = "";
		
		for($i = 10 ; $i < 200 ; $i += 10)
		{
			if($i == 50)
				echo "<option value=\"$i\"$selected>$i (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_msg_refresh_count']) and $_COOKIE['prefs_msg_refresh_count'] == $i)
				echo "<option value=\"$i\" selected>$i (votre choix)</option>";
			else
				echo "<option value=\"$i\">$i</option>";
		}
		?>
	</select>
	</td>
	<td>ShoutBox</td>
	</tr>
	
	<tr class="prefs">
	<td>Nombre de messages <i>Twitter</i> à afficher</td>
	<td>
	<select style="width:100%;" name="prefs_tweets">
		<?php
		if(!isset($_COOKIE['prefs_tweets'])) $selected = " selected"; 
		elseif($_COOKIE['prefs_tweets'] == 5) $selected = " selected"; 
		else $selected = "";
		
		for($i = 1 ; $i < 20 ; $i += 1)
		{
			if($i == 5)
				echo "<option value=\"$i\"$selected>$i (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_tweets']) and $_COOKIE['prefs_tweets'] == $i)
				echo "<option value=\"$i\" selected>$i (votre choix)</option>";
			else
				echo "<option value=\"$i\">$i</option>";
		}
		?>
	</select>
	</td>
	<td>ShoutBox</td>
	</tr>
	
	<tr class="prefs">
	<td>Afficher les boutons de formatage</td>
	<td>
	<select style="width:100%;" name="prefs_displayformatbuttons">
		<?php
		$disps = array("Non", "Oui");
		foreach($disps as $disp)
			if($disp == "Non" and !isset($_COOKIE['prefs_displayformatbuttons']))
				echo "<option value=\"Non\" selected>Non (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_displayformatbuttons']) and $disp == $_COOKIE['prefs_displayformatbuttons'])
				echo "<option value=\"$disp\" selected>$disp (votre choix)</option>";
			elseif($disp == "Non" and isset($_COOKIE['prefs_displayformatbuttons']))
				echo "<option value=\"$disp\">$disp (par défaut)</option>";
			else
				echo "<option value=\"$disp\">$disp</option>";
		?>
	</select>
	</td>
	<td>ShoutBox</td>
	</tr>
	
	<tr class="prefs">
	<td>Écrire un message fait quitter l'état AFK</td>
	<td>
	<select style="width:100%;" name="prefs_afk">
		<?php
		$disps = array("Oui", "Non");
		foreach($disps as $disp)
			if($disp == "Non" and !isset($_COOKIE['prefs_afk']))
				echo "<option value=\"Non\" selected>Non (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_afk']) and $disp == $_COOKIE['prefs_afk'])
				echo "<option value=\"$disp\" selected>$disp (votre choix)</option>";
			elseif($disp == "Non" and isset($_COOKIE['prefs_afk']))
				echo "<option value=\"$disp\">$disp (par défaut)</option>";
			else
				echo "<option value=\"$disp\">$disp</option>";
		?>
	</select>
	</td>
	<td>ShoutBox</td>
	</tr>
	
	<tr class="prefs">
	<td>Afficher les images (mode maison/travail)</td>
	<td>
	<select style="width:100%;" name="prefs_images">
		<?php
		$images = array("Oui", "Non");
		foreach($images as $image)
			if($image == "Oui" and !isset($_COOKIE['prefs_images']))
				echo "<option value=\"Oui\" selected>Oui (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_images']) and $image == $_COOKIE['prefs_images'])
				echo "<option value=\"$image\" selected>$image (votre choix)</option>";
			elseif($image == "Oui" and isset($_COOKIE['prefs_images']))
				echo "<option value=\"$image\">$image (par défaut)</option>";
			else
				echo "<option value=\"$image\">$image</option>";
		?>
	</select>
	</td>
	<td>ShoutBox/Global</td>
	</tr>
	
	<tr class="prefs">
	<td>Afficher les messages arrivée/départ</td>
	<td>
	<select style="width:100%;" name="prefs_msgad">
		<?php
		$msgads = array("Oui", "Non");
		foreach($msgads as $msgad)
			if($msgad == "Non" and !isset($_COOKIE['prefs_msgad']))
				echo "<option value=\"Non\" selected>Non (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_msgad']) and $msgad == $_COOKIE['prefs_msgad'])
				echo "<option value=\"$msgad\" selected>$msgad (votre choix)</option>";
			elseif($msgad == "Non" and isset($_COOKIE['prefs_msgad']))
				echo "<option value=\"$msgad\">$msgad (par défaut)</option>";
			else
				echo "<option value=\"$msgad\">$msgad</option>";
		?>
	</select>
	</td>
	<td>ShoutBox</td>
	</tr>
	
	<tr class="prefs">
	<td>Faire clignotter les MP dans la ShoutBox</td>
	<td>
	<select style="width:100%;" name="prefs_mpblink">
		<?php
		$mpsystem = array("Oui", "Non");
		foreach($mpsystem as $mps)
			if($mps == "Non" and !isset($_COOKIE['prefs_mpblink']))
				echo "<option value=\"Non\" selected>Non (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_mpblink']) and $mps == $_COOKIE['prefs_mpblink'])
				echo "<option value=\"$mps\" selected>$mps (votre choix)</option>";
			elseif($mps == "Non" and isset($_COOKIE['prefs_mpblink']))
				echo "<option value=\"$mps\">$mps (par défaut)</option>";
			else
				echo "<option value=\"$mps\">$mps</option>";
		?>
	</select>
	</td>
	<td>ShoutBox</td>
	</tr>
	
	<tr class="prefs">
	<td>Nombre de jours à afficher sur la frise</td>
	<td>
	<select style="width:100%;" name="prefs_frise">
		<?php
		for($i = 1 ; $i < 8 ; $i++)
		{
			if($i == 2 and !isset($_COOKIE['prefs_frise']))
				echo "<option value=\"$i\" selected>$i jours (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_frise']) and $_COOKIE['prefs_frise'] == $i)
				echo "<option value=\"$i\" selected>$i jours (votre choix)</option>";
			elseif($i == 2 and isset($_COOKIE['prefs_frise']))
				echo "<option value=\"$i\">$i (par défaut)</option>";
			else
				echo "<option value=\"$i\">$i jours</option>";
		}
		?>
	</select>
	</td>
	<td>Calendrier</td>
	</tr>
	
	<tr class="prefs">
	<td>Largeur des graphiques</td>
	<td>
	<select style="width:100%;" name="prefs_graph_width">
		<?php
		if(!isset($_COOKIE['prefs_graph_width'])) $selected = " selected"; 
		elseif($_COOKIE['prefs_graph_width'] == 700) $selected = " selected"; 
		else $selected = "";
		
		for($i = 400 ; $i < 1600 ; $i += 50)
		{
			if($i == 700)
				echo "<option value=\"$i\"$selected>$i pixels (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_graph_width']) and $_COOKIE['prefs_graph_width'] == $i)
				echo "<option value=\"$i\" selected>$i pixels (votre choix)</option>";
			else
				echo "<option value=\"$i\">$i pixels</option>";
		}
		?>
	</select>
	</td>
	<td>Ville</td>
	</tr>
	
	<tr class="prefs">
	<td>Hauteur des graphiques</td>
	<td>
	<select style="width:100%;" name="prefs_graph_height">
		<?php
		if(!isset($_COOKIE['prefs_graph_height'])) $selected = " selected"; 
		elseif($_COOKIE['prefs_graph_height'] == 250) $selected = " selected"; 
		else $selected = "";
		
		for($i = 50 ; $i < 600 ; $i += 50)
		{
			if($i == 250)
				echo "<option value=\"$i\"$selected>$i pixels (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_graph_height']) and $_COOKIE['prefs_graph_height'] == $i)
				echo "<option value=\"$i\" selected>$i pixels (votre choix)</option>";
			else
				echo "<option value=\"$i\">$i pixels</option>";
		}
		?>
	</select>
	</td>
	<td>Ville</td>
	</tr>
	
	<tr class="prefs">
	<td>Mode d'affichage des graphiques</td>
	<td>
	<select style="width:100%;" name="prefs_graphfull">
		<?php
		$opts = array(1,2);
		foreach($opts as $opt)
			if($opt == 1 and !isset($_COOKIE['prefs_graphfull']))
				echo "<option value=\"1\" selected>Simplifié (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_graphfull']) and $opt == $_COOKIE['prefs_graphfull'] and $opt == 1)
				echo "<option value=\"1\" selected>Simplifié (votre choix)</option>";
			elseif(isset($_COOKIE['prefs_graphfull']) and $opt == $_COOKIE['prefs_graphfull'] and $opt == 2)
				echo "<option value=\"2\" selected>Complet (votre choix)</option>";
			elseif($opt == 2)
				echo "<option value=\"2\">Complet</option>";
			elseif($opt == 1)
				echo "<option value=\"1\">Simplifié (Par défaut)</option>";
		?>
	</select>
	</td>
	<td>Ville</td>
	</tr>
	
	<tr class="prefs">
	<td>Afficher l'historique de la banque</td>
	<td>
	<select style="width:100%;" name="prefs_archives_banque">
		<?php
		$abanque = array(1,2);
		foreach($abanque as $ab)
			if($ab == 1 and !isset($_COOKIE['prefs_archives_banque']))
				echo "<option value=\"1\" selected>Aujourd'hui (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_archives_banque']) and $ab == $_COOKIE['prefs_archives_banque'] and $ab == 1)
				echo "<option value=\"1\" selected>Aujourd'hui (votre choix)</option>";
			elseif(isset($_COOKIE['prefs_archives_banque']) and $ab == $_COOKIE['prefs_archives_banque'] and $ab == 2)
				echo "<option value=\"2\" selected>Depuis le début (votre choix)</option>";
			elseif($ab == 2)
				echo "<option value=\"2\">Depuis le début</option>";
			elseif($ab == 1)
				echo "<option value=\"1\">Aujourd'hui (Par défaut)</option>";
		?>
	</select>
	</td>
	<td>Ville</td>
	</tr>

	<tr class="prefs">
	<td>Afficher les messages système parmi les MP</td>
	<td>
	<select style="width:100%;" name="prefs_mpsystem">
		<?php
		$mpsystem = array("Oui", "Non");
		foreach($mpsystem as $mps)
			if($mps == "Oui" and !isset($_COOKIE['prefs_mpsystem']))
				echo "<option value=\"Oui\" selected>Oui (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_mpsystem']) and $mps == $_COOKIE['prefs_mpsystem'])
				echo "<option value=\"$mps\" selected>$mps (votre choix)</option>";
			elseif($mps == "Oui" and isset($_COOKIE['prefs_mpsystem']))
				echo "<option value=\"$mps\">$mps (par défaut)</option>";
			else
				echo "<option value=\"$mps\">$mps</option>";
		?>
	</select> 
	</td>
	<td>Msg Privés</td>
	</tr>
	
	<tr class="prefs">
	<td>Nombre de MP à afficher par défaut</td>
	<td>
	<select style="width:100%;" name="prefs_mplimit">
		<?php
		if(!isset($_COOKIE['prefs_mplimit'])) $selected = " selected"; 
		elseif($_COOKIE['prefs_mplimit'] == 20) $selected = " selected"; 
		else $selected = "";
		
		$is = array(5,10,20,30,50,100,150,200,300,500,1000);
		foreach($is as $i)
		{
			if($i == 20)
				echo "<option value=\"$i\"$selected>$i messages (par défaut)</option>";
			elseif(isset($_COOKIE['prefs_mplimit']) and $_COOKIE['prefs_mplimit'] == $i)
				echo "<option value=\"$i\" selected>$i messages (votre choix)</option>";
			else
				echo "<option value=\"$i\">$i messages</option>";
		}
		?>
	</select>
	</td>
	<td>Msg Privés</td>
	</tr>
<!--// Fin table des préférences -->
</table>
</div><!--// intab -->

<div id="innertab_profil_3" class="intab" style="width:100%">
		<div class="prefs">
		Activer les scripts personnalisés : 
		<select name="prefs_activescripts">
			<?php
			$activescripts = array("Oui", "Non");
			foreach($activescripts as $acs)
				if($acs == "Non" and !isset($_COOKIE['prefs_activescripts']))
					echo "<option value=\"Non\" selected>Non (par défaut)</option>";
				elseif(isset($_COOKIE['prefs_activescripts']) and $acs == $_COOKIE['prefs_activescripts'])
					echo "<option value=\"$acs\" selected>$acs (votre choix)</option>";
				elseif($acs == "Non" and isset($_COOKIE['prefs_activescripts']))
					echo "<option value=\"$acs\">$acs (par défaut)</option>";
				else
					echo "<option value=\"$acs\">$acs</option>";
			?>
		</select>
		Consultez l'<a href="http://hordes.valhalla.fr/forum/comments.php?DiscussionID=20" target="_blank">aide à la rédaction des scripts</a>.
		</div>
	
		<div class="prefs" style="text-align:center;">
		<?php
		$user = $_COOKIE['user'];
		$sql = "SELECT * FROM scripts WHERE user='$user' ORDER BY staticid ASC";
		$req = query($sql);
		$scripts = '';
		while($data = mysql_fetch_assoc($req))
			$scripts .= $data['script']."\n";
		$scripts = trim(stripslashes($scripts),"\n");
		?>
		<textarea name="prefs_scripts" style="width:90%;" rows="3" wrap="off"><?php echo $scripts; ?></textarea>
		<br /><i><b><u>ATTENTION</u> : l'usage d'un script personnalisé mal écrit peut rendre la ShoutBox inutilisable pour vous !</b></i>
		<br /><input type="submit" name="prefs_valid" value="Enregistrer les scripts personnalisés &rarr;"/>
		</div>

<div class="help"><b><u>Scripts personnalisés</u></b> <a href="http://hordes.valhalla.fr/forum/comments.php?DiscussionID=20" target="_blank">(Aide)</a>
<br />Les scripts vous permettent de personnaliser l&#x27;affichage de la ShoutBox sur votre &eacute;cran. Ils n&#x27;ont aucun effet sur les autres utilisateurs. Contrairement aux autres pr&eacute;f&eacute;rences, les scripts ne sont pas li&eacute;s &agrave; un ordinateurs mais &agrave; un utilisateur (c&#x27;est-&agrave;-dire que vous retrouverez au bureau les scripts &eacute;crits &agrave; la maison). Toutefois, en raison de la dangerosité potentielle des scripts, ceux-ci sont désactivés par défaut. Une option des préférences vous permet de les réactiver, et cette préférence est spécifique à chaque ordinateur.
<br /><i><b><u>ATTENTION</u> : l'usage d'un script personnalisé mal écrit peut rendre la ShoutBox inutilisable pour vous ! L'usage de nombreux scripts personnalisés peut ralentir considérablement l'affichage des messages. Utilisez les scripts avec parcimonie !</b></i><br />

<br />
Chaque ligne doit contenir <u>une et une seule</u> instruction. Ne laissez <u>aucune ligne vide</u> entre les instructions.
<br />Une ligne qui débute par le symbole # ne sera pas interprétée : elle n'aura aucun effet. Vous pouvez donc utiliser le symbole # pour prendre des notes sur les instructions précédentes ou suivantes, ou pour désactiver ponctuellement une instruction.
<br />Les scripts se construisent généralement selon le schéma : <i>Condition - Variable - Action - Paramètre</i>.
<br /><br />

<u>Conditions</u> :
<ul>
	<li /><i>SI ... ALORS</i> : exécute l'action qui suit ALORS lorsque la condition entre SI et ALORS est satisfaite.
	<li /><i>NON ...</i> : condition négative (à utiliser à l'intérieur de SI...ALORS)
	<li /><i>... ET ...</i> : vérifie si les deux conditions sont remplies
	<li /><i>... OU ...</i> : vérifie si l'une ou l'autre des conditions est remplie
	<li /><i>... EST ...</i> : compare deux éléments et détermine s'ils sont identiques
	<li /><i>... DIFFERENT DE ...</i> : compare deux éléments et détermine s'ils sont différents
	<li /><i>... CONTIENT "..."</i> : compare deux éléments et détermine si le premier est présent dans le second 
		<ul>
			<li />Les guillemets autour du second élément sont obligatoires ;
			<li />Les éléments ne doivent pas contenir de caractère accentué.
		</ul>
</ul>

<u>Variables</u> :
<ul>
	<li /><i>AUTEUR</i> : l'auteur du message dans la ShoutBox
	<li /><i>MOI</i> : vous êtes l'auteur du message
	<li /><i>MESSAGE</i> : un message dans la ShoutBox
	<li /><i>MP</i> : détermine si un message est privé ou non
	<li /><i>COMMANDE</i> : détermine si un message est une commande ou non
	<li /><i>SALON</i> : le nom du salon dans lequel le message est envoyé
</ul>

<u>Actions</u> :
<ul>
	<li /><i>AFFICHER [&lt;Param&egrave;tre&gt;]</i> : modifie l'affichage du message dans la ShoutBox
	<li /><i>IGNORER [AUTEUR]</i> : ignore les messages dans la ShoutBox
	<li /><i>REMPLACER [&lt;Variable&gt;] PAR ...</i> : remplace le contenu d'une variable par une donnée prédéfinie
	<li /><i>REMPLACER ... PAR ... DANS [&lt;Variable&gt;]</i> : remplace une donnée par une autre à l'intérieur d'une variable
</ul>

<u>Paramètres</u> :
<ul>
	<li /><i>COULEUR [black|white|blue|red|...]</i> : la couleur du texte du message dans la ShoutBox (v. la <a href="http://www.w3schools.com/Html/html_colorvalues.asp" target="_blank">liste des couleurs</a>)
	<li /><i>FOND [black|white|blue|red|...]</i> : la couleur de fond du message dans la ShoutBox (v. la <a href="http://www.w3schools.com/Html/html_colorvalues.asp" target="_blank">liste des couleurs</a>)
	<li /><i>STYLE [italic|normal]</i> : affiche le message en <i>italique</i> ou en normal
	<li /><i>POIDS [bold|normal]</i> : affiche le message en <i>gras</i> ou en normal
	<li /><i>DECORATION [underline|line-through|overline|blink]</i> : affiche un trait <u>sous</u>, <s>au milieu</s> ou <span style="text-decoration:overline;">au dessus</span> du message, ou fait <span style="text-decoration:blink;">clignoter</span> le message.
	<li /><i>TAILLE [&lt;nombre&gt;][pt|px|em|%]</i> : change la taille du texte en fonction de l'unité indiquée
</ul>

<u>Exemples</u> : 
<ul>
<li /><i>SI AUTEUR EST Grymor ALORS AFFICHER COULEUR pink</i>
<li /><i>SI AUTEUR EST Grymor ET NON MP ALORS AFFICHER COULEUR pink</i>
<li /><i>SI AUTEUR DIFFERENT DE MOI ALORS AFFICHER STYLE italic</i>
<li /><i>SI AUTEUR EST TheNanaki ALORS REMPLACER AUTEUR PAR TheNanakekette</i>
<li /><i>SI AUTEUR EST Corbeau ALORS IGNORER</i>
<li /><i>SI AUTEUR EST Corbeau ALORS AFFICHER DECORATION line-through</i>
<li /><i>SI MESSAGE CONTIENT "je suis bloqu" ALORS AFFICHER COULEUR red</i>
<li /><i>SI MESSAGE CONTIENT "bonjour" ALORS AFFICHER TAILLE 18pt</i>
<li /><i>REMPLACER mouarf PAR mw4rf DANS MESSAGE</i>
<li /><i>SI SALON EST MonSavon ALORS AFFICHER COULEUR pink</i>
</ul>
</div>

</div> <!--// intab -->
</form>
</div> <!--// tab_prefs -->
<?php
// Charger le javascript permettant de mettre en place les TABS
// Il faut le faire ici, car si on le fait dans le fichier fonctions.js, la méthode dom:loaded
// de prototype est appelée après le chargement de index.php mais AVANT le chargement de tab_prefs.php
// Conséquence : le javascript ne trouve pas le div sur lequel il est censé agir (qui est défini sur cette page)
echo "<script>innertabs_profil = new Control.Tabs('innertabs_profil');</script>";
?>