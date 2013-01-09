<?php
if(file_exists('../../config.inc.php')) include_once('../../config.inc.php');
if(file_exists('../../fonctions.inc.php')) include_once('../../fonctions.inc.php');

// Frise
if(!empty($_COOKIE['prefs_frise']) and is_numeric($_COOKIE['prefs_frise']))
	$nbjrs = $_COOKIE['prefs_frise'];
else
	$nbjrs = 2;

if($nbjrs == 1) { $nblb = "demain"; } else { $nblb = "les $nbjrs prochains jours"; }

// Frise
echo "<div class=\"prefs\" style=\"text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;\">Frise des disponibilit&eacute;s individuelles pour $nblb</div>";
for($i = 0 ; $i < $nbjrs ; $i++)
	frise(date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+$i, date("Y"))));
	
#################################################################################
function frise($jour)
{
// Récupération des données pour la frise
$sql = "SELECT * FROM events WHERE user != 'SYSTEM' AND ( TO_DAYS(sdate) = TO_DAYS('$jour') OR TO_DAYS(edate) = TO_DAYS('$jour') )";

$jour = explode("-",$jour);
$jour = $jour[2];

$req = query($sql);
$tab = array();
while($data = mysql_fetch_assoc($req))
{
	array_push($tab,$data);
}

// Remplissage du tableau des données de la frise
$intab = array();
$indice = 0;
foreach($tab as $key=>$val)
{
	// Extraire l'heure et les minutes
	$sh = getFDate($val['sdate'], "H");
	$eh = getFDate($val['edate'], "H");
	$sm = getFDate($val['sdate'], "i");
	$em = getFDate($val['edate'], "i");
	
	// Si la période débute un jour antérieur, la faire débuter à 0h00 aujourd'hui
	if(getFDate($val['sdate'], "d") != $jour)
	{
		$sh = 0;
		$sm = 0;
	}
	
	// Si la période se termine un jour ultérieur, la faire se terminer à 23h59 aujourd'hui
	if(getFDate($val['edate'], "d") != $jour)
	{
		$eh = 23;
		$em = 59;
	}
	
	// Remplir le tableau
	$intab[$indice][$val['user']]['sh'] = $sh;
	$intab[$indice][$val['user']]['sm'] = $sm;
	$intab[$indice][$val['user']]['eh'] = $eh;
	$intab[$indice][$val['user']]['em'] = $em;
	$intab[$indice][$val['user']]['start'] = $sh."h".$sm;
	$intab[$indice][$val['user']]['end'] = $eh."h".$em;
	$intab[$indice][$val['user']]['action'] = $val['action'];
	
	$indice++;
}
// Trier le tableau
$frtab = array();
foreach($intab as $indice=>$innertab)
	foreach($innertab as $user=>$subtab)
		$frtab[$user][$indice] = $subtab;

// Variable d'affichage
$frise = "";

// Tableau des heures et en-tête
$thisday = $jour." ".date("M");
$frise .= "<table class=\"frise\" cellspacing=0><tr><td width=\"10%\" class=\"frise_header_alt\">$thisday</td>";
for($i = 0 ; $i < 24 ; $i++)
	$frise .= "<td class=\"frise_header\" width=\"3.75%\">".$i."h</td>";
$frise .= "</tr>";

// Afficher une ligne par utilisateur
foreach($frtab as $user=>$innertab)
{		
	// Heure par heure
	$frise .= "<tr><td width=\"10%\" class=\"frise_header\">$user</td>";
	
	$cases = array();
	for($i = 0 ; $i < 24 ; $i++)
	{
		foreach($innertab as $tps)
		{
			// Disponibilité
			if($tps['action'] == "expe")
				$action = "frise_disponible";
			elseif($tps['action'] == "chantiers")
				$action = "frise_chantiers";
			else
				$action = "frise_indisponible";
				
			if($i == $tps['sh'])
				$cases[$i][] = "<td width=\"3.75%\" class=\"$action frise_start\">".$i."h</td>";
			elseif($i > $tps['sh'] && $i < $tps['eh'])
				$cases[$i][] = "<td width=\"3.75%\" class=\"$action\">&nbsp;</td>";
			elseif($i == $tps['eh'])
				$cases[$i][] = "<td width=\"3.75%\" class=\"$action frise_stop\">".$i."h</td>";
			else
				$cases[$i][] = "VOID";
		}
	}
	
	foreach($cases as $allcases)
	{
		$output = false;
		foreach($allcases as $case)
		{
			if($case != "VOID")
			{
				$frise .= $case;
				$output = true;
			}
		}
		if(!$output)
			$frise .= "<td width=\"3.75%\">&nbsp;</td>";
	}
}


// Affichage
$frise .= "</table>";

echo $frise;
echo "<br />";
}

?>