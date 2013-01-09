<?php
if(file_exists('../../config.inc.php')) include_once('../../config.inc.php');
if(file_exists('../../fonctions.inc.php')) include_once('../../fonctions.inc.php');

echo "<div class=\"prefs\" style=\"text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;\">Calendrier <b>".nommer_mois(date("m"))." ".date("Y")."</b></div>";

// Calendrier
calendrier();

// Légende
echo "<p class=\"prefs\">L&eacute;gende : 
<br /><span class=\"calendrier_disponible\">Disponible et actif | Autorisation de m'escorter en exp&eacute;dition</span>
<br /><span class=\"calendrier_chantiers\">Travail en ville aux chantiers | Laissez-moi en ville</span>
<br /><span class=\"calendrier_indisponible\">Absent ou indisponible | Autorisation de m'escorter en exp&eacute;dition</span></p>";
###################################################################################################
function calendrier()
{
// Récupération des données pour le calendrier
$sql = "SELECT * FROM events WHERE MONTH(sdate) = MONTH(NOW()) OR MONTH(edate) = MONTH(NOW())";
$req = query($sql);
$tab = array();
while($data = mysql_fetch_assoc($req))
{
	array_push($tab,$data);
}

// Remplissage du tableau des données du calendrier
$evtab = array();
foreach($tab as $key=>$val)
{
	// Extraire le jour
	$ej = getFDate($val['sdate'],'d');
	$sj = getFDate($val['edate'],'d');
	
	if($ej == $sj)
	{
		$evtab[$ej][$val['id']][0] = $val['user'];
		$evtab[$ej][$val['id']][1] = $val['action'];
	}
	else
	{
		$evtab[$sj][$val['id']][0] = $val['user'];
		$evtab[$sj][$val['id']][1] = $val['action'];
		$evtab[$ej][$val['id']][0] = $val['user'];
		$evtab[$ej][$val['id']][1] = $val['action'];
	}
}

// Variable constructeur	
$datetime = date("Y-m");

// Variable d'affichage
$calendrier = "";

// Variables internes pour la boucle
$seuil = 0;
$index = 1;

// Les jours de la semaine
$jours = Array("0", "1", "2", "3", "4", "5", "6", "0");
$nb_jour = Date("t", mktime(0, 0, 0, getFDate($datetime,'m'), 1, getFDate($datetime,'Y')));
$seuil = 0;
$index = 1;

// Affichage du mois et de l'année
$calendrier .= "<table class=\"calendrier\">\n";

// Le mois courant
/*
$calendrier .= "
<tr>
	\t<td class=\"cemois\" colspan=\"7\">".nommer_mois(getFDate($datetime,'m')) . " " . getFDate($datetime,'Y')."</td>
</tr>";
*/

// En-tête
$calendrier .= "
<tr id=\"jours\">
	\t<td>L</td>
	\t<td>M</td>
	\t<td>M</td>
	\t<td>J</td>
	\t<td>V</td>
	\t<td>S</td>
	\t<td>D</td>
</tr>";

// Tant que l'on n'a pas affecté tous les jours du mois traité
while ($seuil < $nb_jour) 
{
	$events = "";
	
	if ($index == 1) 
			$calendrier .= "\n\t<tr class=\"ligne\">";
	
	// Si le jour calendrier == jour de la semaine en cours
	if (Date("w", mktime(0, 0, 0, getFDate($datetime,'m'), 1 + $seuil, getFDate($datetime,'Y'))) == $jours[$index]) 
	{
		$JOUR = Date("j", mktime(0, 0, 0, getFDate($datetime,'m'), 1 + $seuil, getFDate($datetime,'Y')));
		
		// Si jour calendrier == aujourd'hui		
		$afficheJour = Date("j", mktime(0, 0, 0, getFDate($datetime,'m'), 1 + $seuil, getFDate($datetime,'Y')));
		if (Date("Y-m-d", mktime(0, 0, 0, getFDate($datetime,'m'), 1 + $seuil, getFDate($datetime,'Y'))) == Date("Y-m-d")) 
			$class = " class=\"itemCurrentItem\"";
		else
			$class = " class=\"itemExistingItem\"";
			
		// Traitement des événements du jour
			// Correction du bug foreach en l'absence de tout événement - 04/04/08
			if($evtab != false)
		foreach($evtab as $jour=>$tab)
			if($jour == $JOUR)
				foreach($tab as $id=>$item) // $item[0] = nom ; $item[1] = couleur du label
				{	
					$nom = $item[0];
					$action = $item[1];
					
					if($nom == "SYSTEM")
						$events .= "<span class=\"calendrier_system\">Coalition</span>";
					elseif($action == "expe")
						$events .= "<span class=\"calendrier_disponible\">$nom</span>";
					elseif($action == "chantiers")
						$events .= "<span class=\"calendrier_chantiers\">$nom</span>";
					else
						$events .= "<span class=\"calendrier_indisponible\">$nom</span>";
					
					$events .= " ";
				}
					
		// Ajout de la case avec la date
			$calendrier .= "\n\t\t<td$class style=\"width:14.2%;\"><div class=\"jour\">$JOUR</div>"
						."<div class=\"contenu\">$events</div></td>";
		$seuil++;
	}
	// Le jour du calendrier n'est pas le jour de la semaine en cours (dans la boucle)
	else 
	{
		// Ajout d'une case vide
		// NB : 14% de width car il y a 7 jours dans la semaine et 100% / 7 = 14,2%
		$calendrier .= "\n\t\t<td style=\"width:14.2%;\">&nbsp;</td>";
	}
	if ($index == 7 && $seuil < $nb_jour) { $calendrier .= "\n\t</tr>"; $index = 1;} else {$index++;}
} // endwhile

// Ajustement du tableau
for ($i = $index; $i <= 7; $i++) 
{
	 $calendrier .= "\n\t\t<td>&nbsp;</td>";
}
$calendrier .= "\n\t</tr>\n";
$calendrier .= "</table>";

echo $calendrier;
}
?>