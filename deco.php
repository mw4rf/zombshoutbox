<?php
session_start();
session_unset();
session_destroy();

include("config.inc.php");
include("fonctions.inc.php");

/*
DEPRECATED : cela fait double emploi avec deco_soft.php qui est appelé lorsque la fenêtre est fermée ou que l'utilisateur change de page : lorsqu'il clique sur le bouton déconnexion, il arrive sur cette page, il change donc de page et deco_soft.php est lancé...
On le lance qd même pour éviter que le message "xxx vient de partir arrive après celui xxx vient d'arriver lorsque xxx recharge la page........... " */
if(isset($_COOKIE['user']))
{
	//$sql = "UPDATE users SET online='0' WHERE user='".$_COOKIE['user']."'";
	//query($sql);
	
	$quit = "[".date("H:i")."] <b>".$_COOKIE['user']."</b> vient de partir";
	$tmp = time();
	$sql = "INSERT INTO messages VALUES('','$quit','$tmp','SYSTEM','1','0','no','0')";
	query($sql);
}

if(isset($_GET["delcookies"]) and $_GET['delcookies'] = 1)
{

	$_COOKIE['user'] = '';
	setcookie('user', '', time() - 3600, '/', $domain);

	$_COOKIE['key'] = '';
	setcookie('key', '', time() - 3600, '/', $domain);
}

$_COOKIE['login'] = '';
setcookie('login', '', time() - 3600, '/', $domain);

header("Location: index.php");
?>
