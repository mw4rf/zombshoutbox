<?php

if(file_exists('../../config.inc.php'))
	include_once('../../config.inc.php');
else
	include_once('config.inc.php');
	
include_once($rootpath.'fonctions.inc.php');
include_once($rootpath.'classes/Chantiers.php');
include_once($rootpath.'classes/Objets.php');

$c = new Chantiers();
$chantiers = array();

if(isset($_POST['chantier']))
	$chantiers[] = stripslashes($_POST['chantier']);
elseif(isset($_POST['all']) and $_POST['all'])
	$chantiers = $c->getAllNames();
elseif(isset($input_chantier))
	$chantiers[] = $input_chantier;
else
	die("Vous ne pouvez pas appeler cette page directement");
	
foreach($chantiers as $chantier)
	if(isset($_POST['chantier']) or isset($_POST['all']))
		echo "<div class=\"note\">".printChantier($chantier)."</div>";
	else
	{
		if($c->existe($chantier))
			$output_chantier = printChantier($chantier);
		elseif($c->existe(ucfirst($chantier))) // si l'utilisateur oublie la majuscule sur la première lettre
			$output_chantier = printChantier(ucfirst($chantier));
		else
			$output_chantier = "Le chantier <b>$chantier</b> n'existe pas";
	}

function printChantier($chantier)
{
	$c = new Chantiers();
	$o = new Objets(); // icones des ressources
	
	// Récupération des données
	$ressources = $c->getRessources($chantier);
	$pa = $c->getPA($chantier,"ISO-8859-1");
	$parent = $c->getParent($chantier,"ISO-8859-1");
	$defense= $c->getDefense($chantier,"ISO-8859-1");
	$temporaire = $c->isTemporaire($chantier,"ISO-8859-1");

	// Defense
	if($defense > 0)
		$def = "<br />Défense : $defense <img src=\"smilies/h_guard.gif\" />";
	else
		$def = "";
		
	// Formatage et calcul des données
	if($defense) $ratiodef = round( ($pa / $defense) * 100 ); // X = PA par pt de défense * 100
	else $ratiodef = 0;
	
	$ratiopa = round($defense / $pa * 100); // X = points de défense par PA investi dans ce chantier

	if(empty($parent)) $parent = "Aucun";

	if($temporaire) 
		$temporaire = "<br /><img src=\"smilies/h_warning.gif\" /> Chantier temporaire"; 
	else 
		$temporaire = "";


	$res = "Ressources :<table>";
	foreach($ressources as $nom=>$nombre)
			$res .= "<tr>
						<td>$nombre</td>
						<td><img src=\"smilies/items/item_".$o->getImage($nom).".gif\" /></td>
						<td>".iconv("UTF-8", "ISO-8859-1", $nom)."</td>
					</tr>";
	$res .= "</table>";

		
	$chantier = iconv("UTF-8", "ISO-8859-1", $chantier);

	$out= 		"Informations sur le chantier : <b>$chantier</b>
		  		  <br />Coût : $pa <img src=\"smilies/h_pa.gif\" />
		  		  $def";

	if($ratiodef or $ratiopa)
		$out .= "<br />Rentabilité : $ratiopa %";
	  
		$out .= "<br />Parent : $parent $temporaire";
		$out .= "<br />$res";
	
	return iconv("ISO-8859-1", "UTF-8", $out);
}
?>