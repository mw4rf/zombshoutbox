<?php

session_start();
header( 'Content-Type: text/xml; charset=ISO-8859-1' );
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
include_once("../classes/Censure.php");

if(!isset($_POST['action'])) die("Vous ne pouvez pas appeler cette page directement");

switch($_POST['action'])
{
	case 'mots':
		switch($_POST['option'])
		{
			case 'ajouter':
				$cens = new Censure();
				$cens->ajouter_mot(iconv("UTF-8","ISO-8859-1",$_POST['mot']));
			break;
			
			case 'modifier':
				$cens = new Censure();
				$cens->modifier_mot($_POST['id'],$_POST['mot']);
			break;
			
			case 'supprimer':
				$cens = new Censure();
				$cens->supprimer_mot($_POST['id']);
			break;
		}
	break;
	
	case 'users':
		switch($_POST['option'])
		{
			case 'ajouter':
				$cens = new Censure();
				$cens->ajouter_user($_POST['user'],$_POST['frequence'],$_POST['actif']);
			break;
			
			case 'modifier':
				$cens = new Censure();
				$cens->modifier_frequence($_POST['user'],$_POST['frequence']);
				if($_POST['actif']) $cens->activer($_POST['user']);
				else $cens->desactiver($_POST['user']);
			break;
			
			case 'supprimer':
					$cens = new Censure();
					$cens->supprimer_user($_POST['user']);
			break;
		}
	break;
}
?>