<?php
session_start();
header( 'Content-Type: text/xml; charset=ISO-8859-1' );
if(file_exists('config.inc.php'))
	include_once('config.inc.php'); // chargement direct
else
	include_once("../config.inc.php"); // chargement ajax
	
include_once($rootpath."fonctions.inc.php");
include_once($rootpath."classes/Censure.php");
include_once($rootpath."classes/Rooms.php");

if(!isset($_COOKIE['user'])) die("Vous ne pouvez pas appeler cette page directement");
$user = $_COOKIE['user'];

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
if(!$isadmin) die("<div class=\"help\">Pour accéder au contenu de cette page, veuillez:<br />1) Vous identifier à l'aide de la commande /auth<br />2) Recharger la page (CTRL+R)</div>");

?>

<ul id="innertabs_admin" class="tabs">
	<li class="tab" id="tabadmin1"><a href="#innertab_admin_1"><img style="vertical-align: middle;" src="smilies/toolbar/admin/annonces.png" />&nbsp;Annonces</a></li>
	<li class="tab" id="tabadmin2"><a href="#innertab_admin_2"><img style="vertical-align: middle;" src="smilies/toolbar/admin/salons.png" />&nbsp;Salons</a></li>
	<li class="tab" id="tabadmin3"><a href="#innertab_admin_3"><img style="vertical-align: middle;" src="smilies/toolbar/admin/sondages.png" />&nbsp;Sondages</a></li>
	<li class="tab" id="tabadmin4"><a href="#innertab_admin_4"><img style="vertical-align: middle;" src="smilies/toolbar/admin/schtroumpf.png" />&nbsp;Schtroumpf</a></li>
	<li class="tab" id="tabadmin5"><a href="#innertab_admin_5"><img style="vertical-align: middle;" src="smilies/toolbar/admin/statistiques.png" />&nbsp;Statistiques</a></li>
	<li class="tab" id="tabadmin6"><a href="#innertab_admin_6"><img style="vertical-align: middle;" src="smilies/toolbar/admin/scripts.png" />&nbsp;Scripts</a></li>
	<li class="tab" id="tabadmin7"><a href="#innertab_admin_7"><img style="vertical-align: middle;" src="smilies/toolbar/admin/banlist.png" />&nbsp;Banlist</a></li>
	<li class="tab" id="tabadmin8"><a href="#innertab_admin_8"><img style="vertical-align: middle;" src="smilies/toolbar/admin/sql.png" />&nbsp;Requêtes SQL</a></li>
	<li class="tab" id="tabadmin9"><a href="#innertab_admin_9"><img style="vertical-align: middle;" src="smilies/toolbar/admin/journal.png" />&nbsp;Journal</a></li>
	<li class="tab" id="tabadmin10"><a href="#innertab_admin_10"><img style="vertical-align: middle;" src="smilies/toolbar/admin/bdd.png" />&nbsp;Base de données</a></li>
	<li class="tab" id="tabadmin11"><a href="#innertab_admin_11"><img style="vertical-align: middle;" src="smilies/toolbar/admin/commandes.png" />&nbsp;Commandes</a></li>
</ul>

<script type="text/javascript" src="admin.js"></script>

<!--// Annonces en ligne -->
<div id="innertab_admin_1" class="intab" style="width:100%"><div>&nbsp;</div>
	<div class="prefs" style="text-align:center;"><input type="submit" id="tab_admin_rb" value="&#x238B; Actualiser tous les onglets" onclick="ajaxrefreshspecial('tab12','tab_admin')">[Dernière mise à jour: <?php echo date("H:i:s"); ?>]</div>

	<div style="text-align:center;" class="msg_text" id="res_admin_annonces"></div>
	<?php
	$sql = "SELECT * FROM annonces WHERE afficher='yes' ORDER BY id DESC";
	$req = query($sql);
	$out = "";
	while($data = mysql_fetch_assoc($req))
		$out .= "<tr>
					<td width=\"80%\"><input type=\"text\" id=\"admin_annonces_text_".$data['id']."\" style=\"width:90%\" value=\"".htmlentities(stripslashes($data['annonce']))."\"/></td>
					<td width=\"10%\"><input type=\"submit\" style=\"width:90%\" value=\"Modifier\" onclick=\"admin_annonces_modifier('".$data['id']."')\" /></td>
					<td width=\"10%\"><input type=\"submit\" style=\"width:90%\" value=\"Retirer\" onclick=\"admin_annonces_retirer('".$data['id']."')\" /></td>
				</tr>";
	?>
	<table width="100%" cellspacing=0 cellpadding=0>
		<?php echo $out; ?>
	</table>
</div>

<!--// Rooms -->
<div id="innertab_admin_2" class="intab" style="width:100%"><div>&nbsp;</div>
	<div style="text-align:center;" class="msg_text" id="res_admin_rooms"></div>

	<div class="msg_text">
	<?php
	$rooms = new Rooms();
	$rs = $rooms->getRoomsName(true,true);
	foreach($rs as $r)
	{
		$rn = $r[1]; // nom du salon
		$rid = $r[0]; // id du salon
	
		// Members
		$m_all = $rooms->getRoomMembers($rid);
		$m_joined = $rooms->getRoomMembers($rid,true);
		$m_active = $rooms->getRoomMembers($rid,false,true);
	
		$c_all = count($m_all);
		$c_joined = count($m_joined);
		$c_active = count($m_active);
	
		$pwd = stripslashes($rooms->getRoomPassword($rid));
	
				echo "<br />";
				echo "Salon : <input type=\"text\" value=\"$rn\" id=\"room_name_$rid\" />";
				echo "&nbsp;<input type=\"submit\" value=\"Renommer\" onclick=\"admin_rooms_rename('$rid')\" />";
				echo "&nbsp;<input type=\"submit\" value=\"Vider/Fermer\" onclick=\"admin_rooms_close('$rid')\" />";
			
				if($rooms->isRoomPrivate($rid))
					echo "&nbsp;<input type=\"submit\" value=\"Rendre public\" onclick=\"admin_rooms_publicize('$rid')\" />&nbsp;<input type=\"submit\" value=\"Changer le mot de passe\" onclick=\"admin_rooms_changepassword('$rid')\" />&nbsp;:&nbsp;<input type=\"text\" value=\"$pwd\" id=\"room_changepassword_$rid\" />";
				else
					echo "&nbsp;<input type=\"submit\" value=\"Rendre privé\" onclick=\"admin_rooms_privatize('$rid')\" /> : <input type=\"text\" value=\"mot_de_passe\" id=\"room_pass_$rid\" />"; 
	
		if($c_all) echo "<br /><u>Membres</u>: "; foreach($m_all as $oo) echo $oo." ";
		if($c_joined) echo "<br /><u>Membres dans ce salon</u>: "; foreach($m_joined as $oo) echo $oo." ";
		if($c_active) echo "<br /><u>Membres avec ce salon actif</u>: "; foreach($m_active as $oo) echo $oo." ";
		echo "<br />";
	}
	?>
	</div>
</div>

<!--// Sondages -->
<div id="innertab_admin_3" class="intab" style="width:100%"><div>&nbsp;</div>
	<div style="text-align:center;" class="msg_text" id="res_admin_sondages"></div>
	<?php
	$sql = "SELECT * FROM poll_titres WHERE TO_DAYS(expiration) - TO_DAYS(NOW()) > -1 ORDER BY id DESC";
	$req = query($sql);
	$out = "";
	while($data = mysql_fetch_assoc($req))
	{
		$out .= "<input type=\"text\" id=\"admin_poll_titre_".$data['id']."\" style=\"width:60%\" value=\"".htmlentities(stripslashes($data['question']))."\"/><input type=\"text\" id=\"admin_poll_expiration_".$data['id']."\" size=\"10\" value=\"".fdate($data['expiration'])."\"/><input type=\"submit\" value=\"Modifier\" onclick=\"admin_poll_titre_modifier('".$data['id']."')\" /><input type=\"submit\" value=\"Retirer\" onclick=\"admin_poll_titre_retirer('".$data['id']."')\" />";
		
		$out .= "<ul style=\"list-style-type:none;\">";
		
			$sqlx = "SELECT * FROM poll_options WHERE sondage_id='".$data['id']."'";
			$reqx = query($sqlx);
			while($datax = mysql_fetch_assoc($reqx))
				$out .= "<li><input type=\"text\" id=\"admin_poll_option_".$datax['id']."\" style=\"width:60%\" value=\"".htmlentities(stripslashes($datax['option']))."\"/><input type=\"submit\" value=\"Modifier\" onclick=\"admin_poll_option_modifier('".$datax['id']."')\" /><input type=\"submit\" value=\"Retirer\" onclick=\"admin_poll_option_retirer('".$datax['id']."')\" /></li>";
		
		$out .= "</ul>";
	}

	echo $out;
	?>
</div>

<!--// Schtroumpfement -->
<div id="innertab_admin_4" class="intab" style="width:100%"><div>&nbsp;</div>
	<div style="text-align:center;" class="msg_text" id="res_admin_censure"></div>
	<table width="100%" border=0 style="vertical-align:top;" class="msg_text small"><tr><td style="vertical-align:top;">
	<table>
	<tr>
		<td><input type="text" id="mot_nouveau" size="40" /></td>
		<td><input type="submit" value="Ajouter" onclick="admin_censure_mots('ajouter','nouveau')" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Mot</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<?php
	$cens = new Censure();
	$mots = $cens->get_mots_list();
	if($mots)
	foreach($mots as $mot)
		echo "<tr>
				<td><input type=\"text\" id=\"mot_".$mot['id']."\" value=\"".$mot['mot']."\" size=\"40\"/></td>
				<td><input type=\"submit\" value=\"Modifier\" onclick=\"admin_censure_mots('modifier','".$mot['id']."')\" /></td>
				<td><input type=\"submit\" value=\"Supprimer\" onclick=\"admin_censure_mots('supprimer','".$mot['id']."')\" /></td>
			 </tr>";
	?>
	</table></td>
	<td style="vertical-align:top;">
	<table>
	<tr>
		<td><input type="text" id="user_censure_user_nouveau" /></td>
		<td><input type="text" id="user_censure_frequence_nouveau" size="3" />%</td>
		<td>&nbsp;</td>
		<td><input type="submit" value="Ajouter" onclick="admin_censure_users('ajouter','nouveau')" /></td>
	</tr>
	<tr>
		<td>Pseudo</td>
		<td>Fréq.</td>
		<td>Actif</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<?php
	$users = $cens->get_users_list();
	if($users)
	foreach($users as $us)
	{
		if($us['actif']) $act = "checked"; else $act = "";
	
		echo "<tr>
				<td>".$us['user']."</td>
				<td><input type=\"text\" id=\"user_censure_frequence_".$us['user']."\" size=\"3\" value=\"".$us['frequence']."\"/>%</td>
				<td><input type=\"checkbox\" id=\"user_censure_actif_".$us['user']."\" $act /></td>
				<td><input type=\"submit\" value=\"Modifier\" onclick=\"admin_censure_users('modifier','".$us['user']."')\" /></td>
				<td><input type=\"submit\" value=\"Supprimer\" onclick=\"admin_censure_users('supprimer','".$us['user']."')\" /></td>
			  </tr>";
	}
	?>
	</table>
	</td></tr></table>
</div>

<!--// Utilisateurs enregistrés -->
<div id="innertab_admin_5" class="intab" style="width:100%"><div>&nbsp;</div>
	<div style="width:100%;text-align:center;"><input type="submit" value="&#x2318; Calculer les statistiques" onclick="admin_stats()"></div>
	<div id="admin_stats" style="overflow:auto;"><div style="text-align:center;width:100%" class="msg_text">N.B.: Le calcul des statistiques peut prendre plusieurs minutes...</div></div>
</div>

<!--// Admin Scripts -->
<div id="innertab_admin_6" class="intab" style="width:100%"><div>&nbsp;</div>
	<div class="prefs" style="text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;"><span style="font-size:0.8em;font-weight:normal;">ATTENTION : scripts applicables à TOUS les utilisateurs !<br />Pour la syntaxe du langage de scripts, veuillez vous reporter à l'onglet <b>Profil</b> &#x2192; <b>Scripts personnalisés</b></span></div>
	<div style="text-align:center;" class="msg_text" id="res_admin_scripts"></div>
	<?php
	$sql_as = "SELECT * FROM scripts_admin ORDER BY id ASC";
	$req_as = query($sql_as);
	$scs = "";
	while($data_as = mysql_fetch_assoc($req_as))
			$scs .= trim(stripslashes($data_as['script']))."\n";		
	?>
	<div style="text-align:center;">
	<textarea id="admin_scripts_textarea" style="width:90%;" rows="5" wrap="off"><?php echo $scs; ?></textarea>
	<br /><br />
	<input type="submit" value="&#x21E7; Enregistrer les scripts" onclick="admin_scripts()">
	</div>
</div>


<!--// Utilisateurs bannis -->
<div id="innertab_admin_7" class="intab" style="width:100%"><div>&nbsp;</div>
	<?php
	$sql = "SELECT * FROM banlist WHERE end > NOW() OR forever='1' ORDER BY id DESC";
	$req = query($sql);
	$out = "";
	$count = num_rows($sql);
	if($count > 0)
		$out .= "<tr>
					<td style=\"text-align:center;font-weight:bold;\">Pseudo banni</td>
					<td style=\"text-align:center;font-weight:bold;\">IP</td>
					<td style=\"text-align:center;font-weight:bold;\">Début</td>
					<td style=\"text-align:center;font-weight:bold;\">Fin</td>
					<td style=\"text-align:center;font-weight:bold;\">Permanent ?</td>
					<td style=\"text-align:center;font-weight:bold;\">Admin</td>
					<td style=\"font-weight:bold;\">Motif</td>
					<td>-</td>
				</tr>";
	else
		$out .= "<tr><td>Aucun utilisateur banni actuellement.</td></tr>";
	while($data = mysql_fetch_assoc($req))
	{
		$buser = $data['user'];
		$bip = $data['ip'];
		if($data['forever']) $bforever = "OUI"; else $bforever = "Non";
		$bstart = fdatetime($data['start'],"d/m/Y H:i:s");
		if($data['forever']) $bend = "-"; else $bend = fdatetime($data['end'],"d/m/Y H:i:s");
		$badmin = $data['admin'];
		$breason = stripslashes($data['reason']);
	
		$out .= "<tr>
					<td style=\"border:1px solid;text-align:center;\">$buser</td>
					<td style=\"border:1px solid;text-align:center;\">$bip</td>
					<td style=\"border:1px solid;text-align:center;\">$bstart</td>
					<td style=\"border:1px solid;text-align:center;\">$bend</td>
					<td style=\"border:1px solid;text-align:center;\">$bforever</td>
					<td style=\"border:1px solid;text-align:center;\">$badmin</td>
					<td style=\"border:1px solid;\">$breason</td>
					<td><input type=\"submit\" value=\"&#x232B; UnBan\" onclick=\"admin_unban('$buser')\"></td>
				</tr>";
	}
	?>
	<table class="msg_text" width="100%">
		<?php echo $out; ?>
	</table>
	<div style="overflow:auto;" class="msg_text small" id="res_admin_ban"></div>
</div>

<!--// Recherche SQL -->
<div id="innertab_admin_8" class="intab" style="width:100%"><div>&nbsp;</div>
	<table width="100%" class="msg_text small">
		<tr>
			<td width="30%">
				<div style="width:100%;height:12em;overflow:auto;">
					<?php
					$req = query("SHOW TABLES");
					$dbtables = array();
					while(list($tbname) = mysql_fetch_row($req))
					{
						$dbtables[] = $tbname;
						echo "<u>$tbname</u>";
						echo "<ul>";
							$req2 = query("SHOW COLUMNS FROM $tbname");
							while($clname = mysql_fetch_row($req2))
								echo "<li><b>".$clname[0]."</b> [".$clname[1]."]</li>";
						echo "</ul>";
					}
				
					?>
				</div>
			</td>
			<td width="70%" style="text-align:center;">
				SELECT * FROM
				<select id="admin_sql_table">
					<?php
					foreach($dbtables as $tb)
						echo "<option value=\"$tb\">$tb</option>";
					?>
				</select>
				WHERE
				<br /><br />
				<textarea id="admin_sql_query" style="width:90%;" rows="5"></textarea>
				<br />
				<input type="submit" value="&#x21E7; Effectuer la requête" onclick="admin_sql()" />
			</td>
		</tr>
	</table>
	<div style="overflow:auto;" class="msg_text small" id="res_admin_sql"></div>
</div>

<!--// Journal d'administration -->
<div id="innertab_admin_9" class="intab" style="width:100%"><div>&nbsp;</div>
	<div style="text-align:center;margin-top:5px;">
		<input type="submit" value="Afficher le journal complet" onclick="admin_showlog()" />
	</div>
	<div id="res_admin_log" class="msg_text" style="overflow:auto;height:300px;">
		<?php
		$sql = "SELECT * FROM log_admin ORDER BY id DESC LIMIT 0,15";
		$req = query($sql);
		while($data = mysql_fetch_assoc($req))
			echo fdatetime($data['adt'])." | ".$data['user']." | ".$data['action']."<br />";
		?>
	</div>
</div>

<!--// Base de données -->
<div id="innertab_admin_10" class="intab" style="width:100%"><div>&nbsp;</div>
	<!--// Graphique des tables -->
	<div class="prefs" style="text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;">Statistiques des tables</div>
	<img src="graphiques/admin_tablesize.php">
	
	<!--// Maintenance -->
	<div class="prefs" style="text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;">Maintenance MySQL</div>
	<div style="text-align:center;">
		<input type="submit" value="Analyser" onclick="admin_maintenance('analyze')" />
		<input type="submit" value="Optimiser" onclick="admin_maintenance('optimize')" />
		<input type="submit" value="Vérifier" onclick="admin_maintenance('check')" />
		<input type="submit" value="Réparer" onclick="admin_maintenance('repair')" />
	</div>
	<div style="text-align:center;margin-top:5px;">
		<input type="submit" value="Supprimer les messages système" onclick="admin_maintenance_delsystem()" />
	</div>
	<div id="admin_maintenance" class="msg_text" style="overflow:auto;height:300px;"></div>
</div>

<!--// AIDE -->
<div id="innertab_admin_11" class="intab" style="width:100%"><div>&nbsp;</div>
	<div class="prefs" style="text-align:center;font-size:1.1em;font-weight:bold;margin-top:0.5em;">Commandes d'administration</div>
	<?php
	echo "<div class=\"msg_text\" style=\"font-size:0.9em;margin-top:-1em;\">
	<br /><span class=\"comm\">/@ annonce</span> : retire toutes les annonces actives
	<br /><span class=\"comm\">/@ annonce &lt;texte&gt;</span> : ajoute une annonce supplémentaire et l'active
	<br /><span class=\"comm\">/@ +/-v &lt;pseudo&gt;</span> : voice ou unvoice un utilisateur ; omettre le paramètre <pseudo> pour appliquer la commande à tous les utilisateurs.
	<br /><span class=\"comm\">/@ stats</span> : affiche les statistiques de la base de donn&eacute;es
	<br /><span class=\"comm\">/@ stats &lt;pseudo&gt;</span> : affiche les statistiques de l'utilisateur
	<br /><span class=\"comm\">/@ event</span> : aide sur l'ajout de nouveaux événements et syntaxe de la commande <i>event</i>
	<br /><span class=\"comm\">/@ event &lt;date&gt; &lt;heure d&eacute;but&gt; &lt;heure fin&gt; &lt;texte&gt;</span> : ajoute un nouvel &eacute;v&eacute;nement de coalition
	<br /><span class=\"comm\">/@ kick &lt;pseudo&gt;</span> : &eacute;jecte l'utilisateur vis&eacute; (qui peut se reconnecter imm&eacute;diatement)
	<br /><span class=\"comm\">/@ ban &lt;pseudo&gt; &lt;dur&eacute;e en minutes&gt; &lt;motif&gt;</span>. NB: la durée peut être exprimée à l'aide une opération telle que 60*2 pour 2 heures. Le motif est facultatif.
	<br /><span class=\"comm\">/@ unban [pseudo|IP]</span> : retire le bannissement d'un utilisateur ou d'une adresse IP.
	<br /><span class=\"comm\">/@ banlist</span> : liste des bannissements actifs.
	</div>";
?>
</div>

<?php
// Charger le javascript permettant de mettre en place les TABS
// Il faut le faire ici, car si on le fait dans le fichier fonctions.js, la méthode dom:loaded
// de prototype est appelée après le chargement de index.php mais AVANT le chargement de tab_xml.php
// Conséquence : le javascript ne trouve pas le div sur lequel il est censé agir (qui est défini sur cette page)
echo "<script>innertabs_admin = new Control.Tabs('innertabs_admin');</script>";
?>

