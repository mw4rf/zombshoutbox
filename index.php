<?php
session_start();
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // anti-cache : date passée
header("Cache-Control: no-cache"); // anti-cache
header("Pragma: no-cache"); // anti-cache
error_reporting(0);
date_default_timezone_set('Europe/Paris');
include("config.inc.php");
include("fonctions.inc.php");
include_once("classes/Censure.php");

// Administrateur ? Changé en 1 plus tard, si l'utilisateur est effectivement admin.
$isadmin = 0;

// IP de l'utilisateur
if (isset($_SERVER['HTTP_X_FORWARD_FOR']) and !empty($_SERVER['HTTP_X_FORWARD_FOR']))
	$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
else
	$ip = $_SERVER['REMOTE_ADDR'];

// Adieu bannis
if(isset($_GET['login']) or isset($_COOKIE['user']))
{
	if(isset($_GET['login'])) $user = $_GET['login'];
	else $user = $_COOKIE['user'];
	
	$sql = "SELECT * FROM banlist WHERE (user='$user' OR ip='$ip') AND (end > NOW() OR forever='1') ORDER BY id DESC LIMIT 0,1";
	
	if(num_rows($sql) > 0)
	{
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
	
		$sdate = fdatetime($data['start'],"d/m/Y");
		$edate = fdatetime($data['end'],"d/m/Y");
		$eheure = fdatetime($data['end'],"H\hi");
		$admin = $data['admin'];
		$reason = stripslashes($data['reason']);
		
		if($data['forever'])
			$msg = "Vous avez &eacute;t&eacute; banni (adresse ip : $ip) <u>pour toujours</u>. Adieu !<br />Raison : <i>$reason</i>";
		else
			$msg = "Vous avez &eacute;t&eacute; banni par $admin le $sdate jusqu'au $edate &agrave; $eheure<br />Raison : <i>$reason</i>";
		
		die($msg);
	}
}

// Réception du formulaire de connexion et connexion.
if(isset($_GET['login']))
{
	$login = $_GET['login'];
	
	// Protection du login !! (20/11/2009)
	$login = strip_tags($login);
	$login = htmlentities($login);
	$login = str_replace(" ", "_", $login);
	
	// Définir les cookies
	setcookie('user', $login, (time() + 3600*60*24*30), '/', $domain); // expiration 30 jours
	// Clé API
	if(!empty($_GET['key'])) 
	{
		$key = $_GET['key'];
		setcookie('key', $key, (time() + 3600*60*24*30), '/', $domain);
	}
	// On est connecté
	setcookie('login', "1", (time() + 3600*60*24*30), '/', $domain);
	// Recharger la page
	header('Location: index.php');
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<title>Zomb'ShoutBox</title>
	<meta http-equiv="expires" content="0">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="cache-control" content="no-cache">
	<script language="Javascript">
	 <!--//
		if(navigator.appName == 'Microsoft Internet Explorer') 
			alert("La ShoutBox n'est pas compatible avec Microsoft Internet Explorer. Veuillez utiliser Firefox, Safari, Google Chrome ou Opera.");
		<?php
			if(isset($_COOKIE['prefs_msg_refresh']) and $_COOKIE['prefs_msg_refresh'] == "Oui")
			{
				echo "var MSG_REFRESH = true;";
				if(isset($_COOKIE['prefs_msg_refresh_count']) 
					and is_numeric($_COOKIE['prefs_msg_refresh_count']))
				{
					echo "var MSG_CEIL = ".$_COOKIE['prefs_msg_refresh_count'].";";
				}
				else
					echo "var MSG_CEIL = 50;"; // par défaut
			}
			else
			{
				echo "var MSG_REFRESH = false;";
				echo "var MSG_CEIL = 0;";
			}
		?>
	// -->
	</script>
	<?php
	// Skins
	if(isset($_COOKIE['prefs_skincss']))
	{
		echo "<link rel=\"stylesheet\" href=\"styles/".$_COOKIE['prefs_skincss'].".css\" type=\"text/css\" media=\"screen\" />";
		echo "<script type=\"text/javascript\" src=\"styles/".$_COOKIE['prefs_skincss'].".js\"></script>";
	}
	else
	{
		echo "<link rel=\"stylesheet\" href=\"styles/Hordes.css\" type=\"text/css\" media=\"screen\" />";
		echo "<script type=\"text/javascript\" src=\"styles/Hordes.js\"></script>";
	}
	// Size
	if(isset($_COOKIE['prefs_sizecss']))
		echo "<link rel=\"stylesheet\" href=\"styles/".$_COOKIE['prefs_sizecss'].".css\" type=\"text/css\" media=\"screen\" />";
	else
		echo "<link rel=\"stylesheet\" href=\"styles/Petit.css\" type=\"text/css\" media=\"screen\" />";
	?>
	<script type="text/javascript" src="api/prototype.js"></script>
	<script type="text/javascript" src="api/scriptaculous.js"></script>
	<script type="text/javascript" src="api/jscolor.js"></script>
	<script type="text/javascript" src="api/livepipe.js"></script>
	<script type="text/javascript" src="api/tabs.js"></script>
	<script type="text/javascript" src="api/contextmenu.js"></script>
	<script type="text/javascript" src="api/sweettitles/addEvent.js"></script>
	<script type="text/javascript" src="api/sweettitles/sweetTitles.js"></script>
	<script type="text/javascript" src="fonctions.js"></script>
</head>

<?php if(isset($_COOKIE['user']) and isset($_COOKIE['login'])) { ?>
<body onload="cron();waitwl();" onbeforeunload="quit();" onfocus="setfocus(1);" onblur="setfocus(0);">
	<div id="preload_container">
		<div id="preload"> 
			Connexion en cours
		</div>
	</div>
<?php } else { ?>
<body>	
<?php } ?>

<div id="head">
	<?php 
		if(isset($_COOKIE['prefs_images']) and $_COOKIE['prefs_images'] == "Non")
			echo "&nbsp;";
		elseif(isset($sitename))
			echo $sitename;
		else
			echo "&#x2192;&nbsp;Zomb'ShoutBox&nbsp;&#x2190;";
	?>
</div>
<?php
// Formulaire d'Identification
if(!isset($_COOKIE['login']) and !isset($_GET['login']))
{	
?>
	<div id="loginbox">
		<p>&nbsp;</p>
	<form action="index.php" method="get">
		Pseudo : 
		<br /><input name="login" type="text" id="login" value="<?php if(isset($_COOKIE['user'])) echo $_COOKIE['user']; ?>">
		<br /><br />
		Clé API <i>(facultatif)</i> : 
		<br /><input name="key" type="text" id="key" value="<?php if(isset($_COOKIE['key'])) echo $_COOKIE['key']; ?>">
		<br /><br /><br />
		<input type="submit" value="Connexion &rarr;" style="font-size:2em;">
		<br /><br /><br />
	</form>
	<form action="deco.php" method="get">
		<input type="hidden" value="1" name="delcookies">
		<input type="submit" value="Supprimer les cookies &#x2326;">
	</form>
	</div>
<?php
}
if(isset($_COOKIE['user']) and isset($_COOKIE['login']))
{
	$login = $_COOKIE['user'];
	$tmp = time();
	// Avertir qu'un nouveau utilisateur vient de se connecter
	$join = "[".date("H:i")."] <b>$login</b> vient d\'arriver";
	$sql = "INSERT INTO messages VALUES('','$join','$tmp','SYSTEM','1','0','no','0')";
	query($sql);
	// Récupération des messages anciens à afficher
	$sql = "SELECT id FROM messages ORDER BY id DESC LIMIT 0,1";
	$req = query($sql);
	$data = mysql_fetch_assoc($req);
	$lastmsg = $data['id'];
	// Modifications de la table user
	$sql = "SELECT * FROM users WHERE user='$login'";
	$count = num_rows($sql);
	// Si la ligne existe, la mettre à jour
	if($count > 0)
	{ 
		if(!empty($_COOKIE['key']))
			$sql = "UPDATE users SET refresh='$lastmsg', lastaction='$tmp', apikey='".$_COOKIE['key']."', ip='$ip', online='1', afk='0', focus='1', kicked='0' WHERE user='$login'";
		else
			$sql = "UPDATE users SET refresh='$lastmsg', lastaction='$tmp', ip='$ip', online='1', afk='0', focus='1', kicked='0' WHERE user='$login'";
		query($sql);
	}
	// Créer une ligne pour l'utilisateur
	else
	{
		if(!empty($_COOKIE['key']))
			$sql = "INSERT INTO users (id,user,refresh,lastaction,apikey,ip,online) VALUES ('','$login','$lastmsg','$tmp', '".$_COOKIE['key']."','$ip', '1')";
		else
			$sql = "INSERT INTO users (id,user,refresh,lastaction,ip,online) VALUES ('','$login','$lastmsg','$tmp','$ip', '1')";
		query($sql);
	}
	
	// Rechercher si l'utilisateur est enregistré, afficher le message de Bienvenue en fonction
	$sql = "SELECT * FROM users_registered WHERE user='$login'";
	$count = num_rows($sql);
	if($count > 0)
	{
		// Administrateur ?
		$reqa = query($sql);
		$data = mysql_fetch_assoc($reqa);
		$isadmin = $data['admin'];
		
		// Message de bienvenue
		$welcomemsg = "Bienvenue, <b>$login</b> !";
		if(!isset($_SESSION['auth']) or $_SESSION['auth'] != 1) $welcomemsg .= "<br />Vous utilisez un pseudonyme enregistr&eacute;. Veuillez vous identifier &agrave; l&#x27;aide de la commande /auth";
		$welcomemsg = addslashes($welcomemsg);
		$sql = "INSERT INTO messages VALUES ('','$welcomemsg','".time()."','SYSTEM','1','$login','yes','0')";
		query($sql);
	}
	else
	{
		$welcomemsg = "Bienvenue, <b>$login</b> !";
		$welcomemsg = addslashes($welcomemsg);
		$sql = "INSERT INTO messages VALUES ('','$welcomemsg','".time()."','SYSTEM','1','$login','yes','0')";
		query($sql);
	}
	
	// Annonces
	$sql = "SELECT * FROM annonces WHERE afficher='yes'";
	$count = num_rows($sql);
	if($count > 0)
	{
		$annonceout = "<b>Annonces de la M&eacute;ta</b>";
		$req = query($sql);
		while($data = mysql_fetch_assoc($req))
			$annonceout .= "<br />".$data['annonce'];
		//$annonceout = iconv("UTF-8","ISO-8859-1",$annonceout);
		$sqlx = "INSERT INTO messages VALUES ('','$annonceout','".time()."','SYSTEM','1','$login','yes','0')";
		query($sqlx);
	}
	
	// Préférences
	if(isset($_COOKIE['prefs_height'])) 
		$height = "style=\"height:".$_COOKIE['prefs_height']."px;\""; 
	else $height = "";
	
	if(isset($_COOKIE['prefs_width'])) 
		$width = " style=\"width:".$_COOKIE['prefs_width']."%;\""; 
	else $width = " style=\"width:70%;\"";
?>
<div id="sidebar">
	<div id="sidebartop"></div>
	<div id="twitterbar" style="display:none;">
		<div style="text-align:center;font-weight:bold;" class="tnamealt">Zomb'Tweets</div>
		<div id="twitter_content"></div>
	</div>
	<div id="citationsbar"></div>
</div>
<div id="rightbar"></div>
<?php 
if(isset($_COOKIE['prefs_annonces']) and $_COOKIE['prefs_annonces'] == "Oui") 
{
	echo "<div id=\"annonces\">";
	include_once("annonces.php");
	echo "</div>";
}
?>

<div id="tabs_container"<?php echo $width; ?>>
<ul id="tabs" class="tabs">
	<li class="tab" id="ttab1"><a href="#tab1" style="font-weight:bold;"><img style="vertical-align: middle;" src="smilies/toolbar/main/sb.png" />&nbsp;ShoutBox</a></li>
	<li class="tab" id="ttab2"><a href="#tab2"><img style="vertical-align: middle;" src="smilies/toolbar/main/ajd.png" />&nbsp;Aujourd'hui</a></li>
	<li class="tab" id="ttab4"><a href="#tab4"><img style="vertical-align: middle;" src="smilies/toolbar/main/mp.png" />&nbsp;Msg Privés</a></li>
	<li class="tab" id="ttab3"><a href="#tab3"><img style="vertical-align: middle;" src="smilies/toolbar/main/recherche.png" />&nbsp;Recherche</a></li>
	<li class="tab" id="ttab5"><a href="#tab5" style="font-weight:bold;"><img style="vertical-align: middle;" src="smilies/toolbar/main/ville.png" />&nbsp;Ma Ville</a></li>
	<li class="tab" id="ttab8"><a href="#tab8"><img style="vertical-align: middle;" src="smilies/toolbar/main/notes.png" />&nbsp;Notes</a></li>
	<li class="tab" id="ttab11"><a href="#tab11"><img style="vertical-align: middle;" src="smilies/toolbar/main/coa.png" />&nbsp;Coa</a></li>
	<li class="tab" id="ttab9"><a href="#tab9"><img style="vertical-align: middle;" src="smilies/toolbar/main/calendrier.png" />&nbsp;Calendrier</a></li>
	<li class="tab" id="ttab6"><a href="#tab6"><img style="vertical-align: middle;" src="smilies/toolbar/main/profil.png" />&nbsp;Profil</a></li>
	<li class="tab" id="ttab7"><a href="#tab7"><img style="vertical-align: middle;" src="smilies/toolbar/main/aide.png" />&nbsp;Aide</a></li>
	<li class="tab" id="ttab12"><a href="#tab12" style="color:red;"><?php if($isadmin) echo "<img style=\"vertical-align: middle;\" src=\"smilies/toolbar/main/admin.png\" />&nbsp;ADMIN"; ?></a></li>
</ul>
</div>

<div class="intab" id="tab1"<?php echo $width; ?>>
	<div>&nbsp;</div> <!--// évite le décalage de tous les messages s'il y a 2 lignes d'onglets -->
	<div id="shoutbox"<?php echo $height; ?>></div>

	<div id="inputbox">
			<input name="user" type="hidden" id="user" value="<?php echo $_COOKIE['user']; ?>">
			<input name="alreadydone" type="hidden" id="alreadydone" value="">
			<input name="message" autocomplete = "off" type="text" id="message" onkeypress="enterk(window.event)">
			<br />
			<input type="submit" name="Submit" value="&#x21E7; Envoyer" onclick="ecrire()">
			<input type="submit" name="Refresh" value="&#x238B; Recharger" onclick="window.location='index.php'">
			<input type="submit" name="Exit" value="Déconnexion &rarr;" onclick="deco()">
			<?php if(isset($_COOKIE["prefs_displayformatbuttons"]) and $_COOKIE["prefs_displayformatbuttons"] == "Oui") {?>
			<p>
				<input type="submit" name="Submit" id="f_bold" value="Gras" onclick="tformat('b')">
				<input type="submit" name="Submit" id="f_italic" value="Italique" onclick="tformat('i')">
				<input type="submit" name="Submit" id="f_underline" value="Souligné" onclick="tformat('u')">
				<input type="submit" name="Submit" id="f_color" value="Couleurs" onclick="showhide('colorpicker', 'f_color')">
			</p>
			<p id="colorpicker" style="display:none;">
			<select id="colorappli">
				<option value="fg">Couleur du texte</option><option value="bg">Couleur du fond</option>
			</select>
			<input type="input" name="Submit" id="jscolorpicker" value="Couleur" class="color {pickerPosition:'top',pickerFaceColor:'transparent',pickerFace:3,pickerBorder:0,pickerInsetColor:'black'}" onclick="">
			<input type="submit" name="Submit" id="g_colorapp" value="Appliquer &#x2192;" onclick="insertcolor()">
			</p>
			<?php } ?>
	</div>
</div>
<div class="intab" id="tab2"<?php echo $width; ?>><div id="tab_today"></div></div>
<div class="intab" id="tab3"<?php echo $width; ?>><div id="tab_search"></div></div>
<div class="intab" id="tab4"<?php echo $width; ?>><div id="tab_mp"></div></div>
<div class="intab" id="tab5"<?php echo $width; ?>><div id="tab_xml"></div></div>
<div class="intab" id="tab6"<?php echo $width; ?>><?php include('views/tab_prefs.php'); ?></div>
<div class="intab" id="tab7"<?php echo $width; ?>><?php include('views/tab_help.php'); ?></div>
<div class="intab" id="tab8"<?php echo $width; ?>><div id="tab_notes"></div></div>
<div class="intab" id="tab9"<?php echo $width; ?>><div id="tab_calendrier"></div></div>
<div class="intab" id="tab11"<?php echo $width; ?>><div id="tab_citoyens"><?php include('views/tab_citoyens.php'); ?></div></div>
<div class="intab" id="tab12"<?php echo $width; ?>><?php if($isadmin) { ?><?php include('views/tab_admin.php'); ?><?php } ?></div>
<?php } ?>

</body>
</html>