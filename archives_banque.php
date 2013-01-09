<?php
include_once("config.inc.php");
include_once("fonctions.inc.php");

function archives_banque()
{
	
$key = $_COOKIE['key'];
$doc = getxml($key);

// Ma ville
$cities = $doc->getElementsByTagName('city');
foreach($cities as $city)
	$ville = $city->getAttribute('city');
$ville = iconv("UTF-8", "ISO-8859-1", $ville);
$ville = addslashes($ville);

//      Récupérer les archives

// Choix de l'affichage
if(isset($_COOKIE['prefs_archives_banque']))
{
	if($_COOKIE['prefs_archives_banque'] == 1)
		$critere = "AND DAY(adate) = DAY(NOW())";
	else
		$critere = "";
}
else
	$critere = "AND DAY(adate) = DAY(NOW())";

// Formation de la requête
$sql = "SELECT DISTINCT(banque), id, ville, adate FROM xml_banque WHERE ville='$ville' $critere ORDER BY id DESC";
$req = query($sql);
$banque = "";
$supercompteur = 0;
while($data = mysql_fetch_assoc($req))
{
	if(fdatetime($data['adate'],"d/m/Y") == date("d/m/Y"))	
		$adate = "Aujourd'hui &agrave; ".fdatetime($data['adate'],"H\hi");
	else 
		$adate = fdatetime($data['adate'],"d M Y, H\hi");
	
	$objets = "";
	$compteur = 0;
	$abanque = explode(",",$data['banque']);
	
	foreach($abanque as $a)
	{
		$item = explode(":",$a);
		$objets[$compteur]['name'] = $item[1];
		$objets[$compteur]['icon'] = $item[2];
		$objets[$compteur]['count'] = $item[0];
		$objets[$compteur]['cat'] = $item[3];
		if(isset($item[4])) $objets[$compteur]['broken'] = $item[4]; else $objets[$compteur]['broken'] = 0;
		$compteur++;
	}
	$banque[$supercompteur][0] = $adate;
	$banque[$supercompteur][1] = $objets;
	$supercompteur++;
}

// Comparaison des objets (les nouveaux, et ceux dont le nombre a changé)
$lignes = "";
for($now = 0 ; $now < count($banque) ; $now++)
{
	$b = $banque[$now]; // dernier enregistrement
	$it = $now + 1; if(!isset($banque[$it])) continue;
	$oa = $banque[$it][1]; // avant-dernier enregistrement
	$output = $b[0]."<ul>";
	$display = false;
	for($i = 0 ; $i < count($b[1]) ; $i++)
	{
		$o = $b[1][$i]; // dernier enregistrement
		$icon = "<img src=\"http://data.hordes.fr/gfx/icons/item_".$o['icon'].".gif\" />";
		$new = $o['count'];
			
		foreach($oa as $a)
			if($a['name'] == $o['name'] and $a['broken'] == $o['broken'])
			{
				$old = $a['count'];
				$a['done'] = true;
				break;
			}
			else
				$old = 0; // cet objet est nouveau, il n'existait pas dans l'enregistrement précédent
		
		$diff = $new - $old;
		
		// Affichage
		if($diff < 0)
			{ $output .= "<li style=\"color:red;\" />$diff $icon [Total $new]"; $display = true; }
		elseif($diff > 0)
			{ $output .= "<li style=\"color:green;\" />+$diff $icon [Total $new]"; $display = true; }
		elseif($diff == 0) {}
	}
	
	foreach($oa as $a)
	{
		$exists = false;
		foreach($b[1] as $n)
		{
			if($a['name'] == $n['name'] and $a['broken'] == $a['broken'])
				$exists = true;
		}
		if(!$exists)
		{
			$diff = -$a['count'];
			$icon = "<img src=\"http://data.hordes.fr/gfx/icons/item_".$a['icon'].".gif\" />";
			$new = 0;
			$output .= "<li style=\"color:red;\" />$diff $icon [Total $new]"; 
			$display = true;
		}
	}
	
	$output .= "</ul><br />";
	if($display) $lignes[] = $output;
}
// Affichage
if(!empty($lignes))
{
	$archives_banque = "";
	foreach($lignes as $l)
		$archives_banque .= $l;

	$archives_banque = "<div class=\"bank_cat_header\">Historique de la banque</div><br />"
						.$archives_banque;

	return $archives_banque;
}
else return "";
}	
	//debug
	//archives_banque();

?>