<?php
// Sécurité
if(!isset($_POST)) die("Vous ne pouvez pas appeler cette page directement");

include_once("config.inc.php");
include_once("fonctions.inc.php");

// Utilisateur
$user = $_COOKIE['user'];

// Récupérer les données du formulaire
$height = $_POST['prefs_height'];
$width = $_POST['prefs_width'];
$skincss = $_POST['prefs_skincss'];
$sizecss = $_POST['prefs_sizecss'];
$frise = $_POST['prefs_frise'];
$graph_height = $_POST['prefs_graph_height'];
$graph_width = $_POST['prefs_graph_width'];
$images = $_POST['prefs_images'];
$mplimit = $_POST['prefs_mplimit'];
$mpsystem = $_POST['prefs_mpsystem'];
$mpsystem = $_POST['prefs_displayformatbuttons'];
$mpblink = $_POST['prefs_mpblink'];
$afk = $_POST['prefs_afk'];
$tweets = $_POST['prefs_tweets'];
$activescripts = $_POST['prefs_activescripts'];
$archives_banque = $_POST['prefs_archives_banque'];
$annonces = $_POST['prefs_annonces'];
$msgad = $_POST['prefs_msgad'];
$msgr = $_POST['prefs_msg_refresh'];
$msgrc = $_POST['prefs_msg_refresh_count'];
$graphfull = $_POST['prefs_graphfull'];
$apikey = $_POST['apikey'];

// Définir les cookies (expiration plusieurs années -- étrange !--)
setcookie('prefs_height', $height, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_width', $width, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_skincss', $skincss, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_sizecss', $sizecss, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_frise', $frise, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_graph_height', $graph_height, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_graph_width', $graph_width, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_images', $images, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_mplimit', $mplimit, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_mpsystem', $mpsystem, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_mpblink', $mpblink, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_displayformatbuttons', $mpsystem, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_afk', $afk, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_tweets', $tweets, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_activescripts', $activescripts, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_archives_banque', $archives_banque, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_annonces', $annonces, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_msgad', $msgad, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_msg_refresh', $msgr, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_msg_refresh_count', $msgrc, (time() + 3600*60*24*30), '/', $domain);
setcookie('prefs_graphfull', $graphfull, (time() + 3600*60*24*30), '/', $domain);

if($apikey != "[non fournie]")
	setcookie('key', $apikey, (time() + 3600*60*24*30), '/', $domain);

// Scripts perso
$scripts = $_POST['prefs_scripts'];
$scripts = explode("\n",$scripts);

$sql = "DELETE FROM scripts WHERE user='$user'";
query($sql);

foreach($scripts as $script)
{
	$script = ltrim(trim(addslashes($script)));
	$sql = "INSERT INTO scripts VALUES ('','$user','$script')";
	if(!empty($script)) query($sql);
}

// Rediriger
header('Location: index.php');
?>