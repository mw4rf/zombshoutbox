<?php

if(file_exists('../../config.inc.php'))
	include_once('../../config.inc.php');
else
	include_once('config.inc.php');
	
include_once($rootpath.'fonctions.inc.php');
include_once($rootpath.'classes/Objets.php');

$o = new Objets();
$objets = array();

if(isset($_POST['objet']))
	$objets[] = stripslashes($_POST['objet']);
elseif(isset($_POST['all']) and $_POST['all'])
	$objets = $o->getAllNames();
elseif(isset($input_objet))
	$objets[] = $input_objet;
else
	die("Vous ne pouvez pas appeler cette page directement");

foreach($objets as $objet)
	if(isset($_POST['objet']) or isset($_POST['all'])) // depuis le formulaire de recherche
		echo "<div class=\"note\">".printObjet($objet)."</div>";
	else // depuis la commande /objet ; printObjet() avec FALSE pour ne pas afficher les utilisations de l'objet
	{
		if($o->existe($objet))
			$output_objet = printObjet($objet,false);
		elseif($o->existe(ucfirst($objet))) // si l'utilisateur oublie la majuscule sur la premi�re lettre
			$output_objet = printObjet(ucfirst($objet),false);
		else
			$output_objet = "L'objet <b>$objet</b> n'existe pas";
	}

function printObjet($objet,$display_utilisation=true)
{
	$o = new Objets();
	
	// R�cup�ration des donn�es
	$image = "<img src=\"smilies/items/item_".$o->getImage($objet).".gif \" />";

	$valeur = $o->getValeur($objet);
	switch($valeur)
	{
		case '1': $valeur = "<img src=\"smilies/rating_plus_r1.gif\" />"; break;
		case '2': $valeur = "<img src=\"smilies/rating_plus_r2.gif\" />"; break;
		case '3': $valeur = "<img src=\"smilies/rating_plus_r3.gif\" />"; break;
		case '4': $valeur = "<img src=\"smilies/rating_plus_r4.gif\" />"; break;
		case '5': $valeur = "<img src=\"smilies/rating_plus_r5.gif\" />"; break;
		default: $valeur  = "<img src=\"smilies/h_warning.gif\" />"; 	 break;
	}

	$categorie = $o->getCategorie($objet);
	switch($categorie)
	{
		case 'Rsc':			$categorie = "Ressources"; 		break;
		case 'Weapon':		$categorie = "Armes"; 			break;
		case 'Armor':		$categorie = "D&eacute;fense"; 	break;
		case 'Misc':		$categorie = "Divers"; 			break;
		case 'Drug':		$categorie = "Drogues"; 		break;
		case 'Furniture':	$categorie = "Meubles"; 		break;
		case 'Food': 		$categorie = "Nourriture"; 		break;
		case 'Box': 		$categorie = "Conteneurs"; 		break;
		default: 			$categorie = "?";				break;
	}

	$isForChantiers = $o->isForChantiers($objet);
	if($isForChantiers)
		$isForChantiers = "<br /><img src=\"smilies/h_door.gif\" /> Objet utile aux chantiers";
	else
		$isForChantiers = '';

	$isForDefense = $o->isForDefense($objet);
	if($isForDefense)
		$isForDefense = "<br /><img src=\"smilies/h_guard.gif\" /> Objet apportant des points de d�fense";
	else
		$isForDefense = '';	

	$isTransformable = $o->isTransformable($objet);
	if($isTransformable)
		$isTransformable = "<br /><img src=\"smilies/h_refine.gif\" /> Peut �tre transform� � l'atelier";
	else
		$isTransformable = '';
	
	$isConsommable = $o->isConsommable($objet);
	if($isConsommable)
		$isConsommable = "<br /><img src=\"smilies/h_arrow.gif\" /> Peut �tre consomm� (dispara�t apr�s utilisation)";
	else
		$isConsommable = '';

	$isAssemblable = $o->isAssemblable($objet);
	if($isAssemblable)
		$isAssemblable = "<br /><img src=\"smilies/h_arrow.gif\" /> Peut �tre assembl� avec un autre objet";
	else
		$isAssemblable = '';

	$isArme = $o->isArme($objet);
	if($isArme)
		$isArme = "<br /><img src=\"smilies/h_zombie.gif\" /> Peut �tre utilis� comme une arme";
	else
		$isArme = '';

	$isCassable = $o->isCassable($objet);
	if($isCassable)
		$isCassable = "<br /><img src=\"smilies/h_arrow.gif\" /> Peut �tre cass�";
	else
		$isCassable = '';
	
	$isForMaison = $o->isForMaison($objet);
	if($isForMaison)
		$isForMaison = "<br /><img src=\"smilies/h_home.gif\" /> Objet utile aux am�liorations de la maison";
	else
		$isForMaison = '';
	
	$isEncombrant = $o->isEncombrant($objet);
	if($isEncombrant)
		$isEncombrant = "<br /><img src=\"smilies/h_bag.gif\" /> Objet encombrant";
	else
		$isEncombrant = '';
	
	$isUtilisable = $o->isUtilisable($objet);
	if($isUtilisable)
		$isUtilisable = "<br /><img src=\"smilies/h_arrow.gif\" /> Objet utilisable par ou sur un citoyen";
	else
		$isUtilisable = '';
	
	$isDangereux = $o->isDangereux($objet);
	if($isDangereux)
		$isDangereux = "<br /><img src=\"smilies/h_warning.gif\" /> <b>Objet dangereux</b>";
	else
		$isDangereux = '';

	$pa = $o->getPA($objet);
	if(is_numeric($pa))
		$pa = "<br />La consommation de cet objet rend $pa <img src=\"smilies/h_pa.gif\" />";
	else
		$pa = "";

	$note = $o->getNote($objet,"ISO-8859-1");

	$objet = iconv("UTF-8", "ISO-8859-1", $objet);
	
	// Utilisations de l'objet
	$ut = "";
	if($display_utilisation)
	{
		$utilisations = $o->getUtilisations($objet);
		if(is_array($utilisations))
			foreach($utilisations as $utilisation => $stab)
			{
				$ut .= "<ul style=\"list-style:none;\">";
					foreach($stab as $fct => $sstab)
					{
						$ut .= "<li>";
						$avec = "";
						$pour = "";
						if($fct == "pour")
						{
							foreach($sstab as $index => $item)
								$ut .= "Permet de fabriquer : <img src=\"smilies/items/item_".$o->getImage($item).
										".gif \" /> <b>".iconv("UTF-8", "ISO-8859-1", $item)."</b>";
						}
						elseif($fct == "avec")
						{
							$ut .= "S'assemble avec : ";
							foreach($sstab as $index => $item)
							{
								$ut .= "<img src=\"smilies/items/item_".$o->getImage($item).".gif \" /> <i>"
									.iconv("UTF-8", "ISO-8859-1", $item)."</i> ";
							}
							$ut = trim($ut);
						}
						$ut .= "</li>";
					}
				$ut .= "</ul>";
			}
		}
		else
		{
			$ut .= "<p><i>Pour voir les diff�rentes possibilit�s d'assemblage de cet objet, veuillez utiliser l'onglet \"Recherche\".</i></p>";
		}
	
	
	$out = "$image <b>$objet</b> <br />Utilit� : $valeur <br /> Cat�gorie : $categorie"
			."$isUtilisable $isConsommable $pa $isForDefense $isForChantiers $isForMaison $isAssemblable $isTransformable $isEncombrant $isArme $isCassable $isDangereux<p>$note</p>$ut";


	return iconv("ISO-8859-1", "UTF-8", $out);
}
?>
