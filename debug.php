<?php
include('fonctions.inc.php');
$output_desert = '';
//////////////// VIEWMAP
// Récupération des données
$doc = getxml($_COOKIE['key']); // ici, la clé API de l'utilisateur

// tag = identifiant du marqueur => 
	// 0 : RAS 
	// 1 : citoyen bloqué 
	// 2 : ressources abandonnées 
	// 3 : objet divers 
	// 4 : zone épuisée 
	// 5 : zone sécurisée 
	// 6 : zone à déblayer 
	// 7 : entre 5 et 8 zombies 
	// 8 : 9 zombies et plus
// nvt = not vivited today (pas visités aujourd'hui)
// danger = estimation du nombre de zombie
	// noeud absent : aucun zombie ou zone pas explorée (en fonction du nvt)
	// 1 : zombies isolés (<3)
	// 2 : meute de zombies (<5)
	// 3 : horde de zombies
	
// Récupérer chaque case de la carte, avec ses propriétés
$hzones = $doc->getElementsByTagName('zone');
$zones = array();
foreach($hzones as $hzone)
{
	$x = $hzone->getAttribute('x'); // abs
	$y = $hzone->getAttribute('y'); // ord
	$z = $hzone->getAttribute('z'); // nombre de zomb, si connu
	
	if(!empty($z))
		$zones["$x.$y"]['z'] = $hzone->getAttribute('z'); 
	
	$zones["$x.$y"]['nvt'] = $hzone->getAttribute('nvt'); 
	$zones["$x.$y"]['danger'] = $hzone->getAttribute('danger');
	$zones["$x.$y"]['tag'] = $hzone->getAttribute('tag');
	
	$bats = $hzone->getElementsByTagName('building'); // bâtiment
	foreach($bats as $bat)
		if(!empty($bat))
			$zones["$x.$y"]['bat'] = 1; 
}

// Ville
$cities = $doc->getElementsByTagName('city');
foreach($cities as $city)
	$ville = $city->getAttribute('x').".".$city->getAttribute('y');

// Combien de cases ? // parfois 11, parfois 12 parfois 13 !
$maps = $doc->getElementsByTagName('map');
foreach($maps as $map)
{
	$theight = $map->getAttribute('hei');
	$twidth = $map->getAttribute('wid');
}

$x = array(); // largeur de la map
for($i = 0 ; $i < $twidth ; $i++)
	$x[$i] = $i;
	
$y = array(); // hauteur de la map
for($i = 0 ; $i < $theight ; $i++)
	$y[$i] = $i;
	
//$x = array_reverse($x);
//$y = array_reverse($y);
	
// Construction de la carte

//$output_desert .= "<table cellspacing=0 style=\"width:100%;\" id=\"map\">";
$output_desert .= "<table cellspacing=0 cellpadding=0>";

foreach($x as $line)
{
	$output_desert .= "<tr>";
	foreach($y as $col)
	{
		$case = $col.".".$line;
		$classes = "zone";
		
		if($case == $ville)
			$classes .= " villemap";
		
		if(isset($zones[$case]))
		{	
			if(!$zones[$case]['nvt'])
				$classes .= " nvt";
				
			if(isset($zones[$case]['z']))
				$classes .= " zomb";
				
			if(isset($zones[$case]['danger']))
				$classes .= " d".$zones[$case]['danger'];		
				
			if(isset($zones[$case]['bat']) and $zones[$case]['bat'] == 1)
				$classes .= " bat";		
				
			if(isset($zones[$case]['tag']))
				if($zones[$case]['tag'] == 5)
					$classes .= " epuisee";
				elseif($zones[$case]['tag'] == 1)
					$classes .= " mhelp";
		}
		else
			$classes .= " unknown";
		
		//$output_desert .= "<td class=\"$classes\">$case</td>";
		
		$output_desert .= "<td><img src=\"graphiques/mapcell.php?x=$col&y=$line\" /></td>";
	}
	$output_desert .= "</tr>";
}
$output_desert .= "</table>";

//////////////////

echo $output_desert;

?>