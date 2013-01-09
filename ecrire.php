<?php
session_start();
header( 'Content-Type: text/xml; charset=ISO-8859-1' );
include_once("config.inc.php");
include_once("fonctions.inc.php");
include_once('classes/Censure.php');
include_once('classes/Rooms.php');

// Récupération des données du formulaire
$message = $_POST['message'];
$user = $_POST['user'];
$timestamp = time(); // timestamp actuel

// Données relatives à l'utilisateur
$sql = "SELECT * FROM users WHERE user='$user'";
$req = query($sql);
$data = mysql_fetch_assoc($req);
$key = $data['apikey'];
$voice = $data['voice'];

// Sortir d'AFK
$afk = $data['afk'];
if($afk and isset($_COOKIE['prefs_afk']) and $_COOKIE['prefs_afk'] == "Oui")
{
	$sqlafk = "UPDATE users SET afk='0' WHERE user='$user'";
	query($sqlafk);
	$sqlafk2 = "INSERT INTO messages VALUES ('','Vous n\'&ecirc;tes plus AFK','".time()."','SYSTEM','1','$user','yes','0')";
	query($sqlafk2);
}

//Est-ce une commande ?
$firstchar = substr($message, 0, 1); // récupère le premier caractère
if($firstchar == "/") $command = 1; else $command = 0;

$output = "";

// Système contre l'usurpation de pseudo
$badauth = false;
$sql = "SELECT * FROM users_registered WHERE user='$user'";
$count = num_rows($sql);
if($count > 0)
{
	$req = query($sql);
	$data = mysql_fetch_assoc($req);
	$dbip = $data['ip'];
	
	if (isset($_SERVER['HTTP_X_FORWARD_FOR']) and !empty($_SERVER['HTTP_X_FORWARD_FOR']))
		$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
	else
		$ip = $_SERVER['REMOTE_ADDR'];
		
	if($ip != $dbip or !isset($_SESSION['auth']) or $_SESSION['auth'] != 1)
		$badauth = true;
}

// Traitement de la commande
if($message == "/help") // aide
{
	$output = "Consultez l&#x27;aide en cliquant sur l&#x27;onglet &laquo; Aide &raquo; ci-dessus.";
	$pm['to'] = $user;
	$user = "SYSTEM";
	$command = 1;
}
elseif($badauth and substr($message, 0, 5) != "/auth") // utilisateur doit entrer son mot de passe
{
	$output = "Vous utilisez un pseudonyme enregistr&eacute;. Veuillez vous identifier &agrave; l&#x27;aide de la commande /auth.";
	$pm['to'] = $user;
	$user = "SYSTEM";
	$command = 1;
}
elseif($command == 1 and substr($message, 0, 2) == "/@") // commande administrateur
{
	include('admin.php');
}
elseif(!$voice) // utilisateur unvoiced
{
	$output = "Vous avez &eacute;t&eacute; priv&eacute; de parole sur ce canal.";
	$pm['to'] = $user;
	$user = "SYSTEM";
	$command = 1;
}
elseif($command == 0) // message normal
	$output = $message;
elseif(empty($key) and $command == 1) // commande normale refusée faute de clé API
{
	$output = "Veuillez indiquer votre cl&eacute; API lors de la connexion pour utiliser les commandes.";
	$pm['to'] = $user;
	$user = "SYSTEM";
}
else // commande normale
{	
	include_once('commandes.php');
}

// Pre-processing
$output = parsing($output);
$output = postparsing($output);

// schtroumpfer
$cens = new Censure();
$output = $cens->censurer($output);

// Charset
$output = iconv("UTF-8", "ISO-8859-1", $output);

// Envoi du message
$output = addslashes($output);

// Message privé ?
if(!empty($pm['to']))
{ 
	if(isset($pm['unread']) and $user != "SYSTEM") $pm_unread = 'yes'; else $pm_unread = 'no';
	
	if(is_array($pm['to']))
	{
		if(count($pm['to']) > 1)
		{
			$tomsg = "<i>@";
			foreach($pm['to'] as $destinataire) $tomsg .= "$destinataire|";
			$tomsg = trim($tomsg,"|");
			$output = $tomsg." </i>: ".$output;
		}
		
		foreach($pm['to'] as $pm)
		{			
			$sql = "INSERT INTO messages VALUES ('','$output','$timestamp','$user', '$command', '$pm', '$pm_unread','0')";
			query($sql);
		}
	}
	else
	{
		$sql = "INSERT INTO messages VALUES ('','$output','$timestamp','$user', '$command', '".$pm['to']."', '$pm_unread','0')";
		query($sql);
	}

}
// Message public
else
{
	
	// SAlles
	$rooms = new Rooms();
	$active_room = $rooms->getActiveRoom();
	
	$pm = 0;
	if(isset($pm['unread']) and $user != "SYSTEM") $pm_unread = 'yes'; else $pm_unread = 'no';
	$sql = "INSERT INTO messages VALUES ('','$output','$timestamp','$user', '$command', '$pm', '$pm_unread','$active_room')";
	query($sql);
}


?>