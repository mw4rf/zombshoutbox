<?php
// Exécuter si l'utilisateur a fourni sa clé API
if(isset($_COOKIE['key']) and !empty($_COOKIE['key']))
{
	$stats = getstats();
		
	if(is_array($stats) and $stats != false) 
	{
	
	// 1. ATTAQUE
	$sql = "SELECT * FROM xml_attaque WHERE adate='".date("Y-m-d")."' AND ville='".$stats['ville']."'";
	$req = query($sql);
	$data = mysql_fetch_assoc($req);
	
	// Nouvelle entrée
	if(num_rows($sql) < 1)
	{
		if(is_array($stats))
		{
			$sql = "INSERT INTO xml_attaque VALUES ('','".$stats['ville']."','".$stats['jour']."','".$stats['adate']."','".$stats['min']."','".$stats['max']."','".$stats['def']."')";
			query($sql);
		}
	}
	// Mise à jour
	elseif($data['min'] != $stats['min'] or $data['max'] != $stats['max'] or $data['def'] != $stats['def'])
	{
		$sql = "UPDATE xml_attaque SET min='".$stats['min']."', max='".$stats['max']."', def='".$stats['def']."' WHERE ville='".$stats['ville']."' AND adate = '".date("Y-m-d")."'";
		query($sql);
	}
	
	// 2. RESSOURCES
	$sql = "SELECT * FROM xml_ressources WHERE adate='".date("Y-m-d")."' AND ville='".$stats['ville']."'";
	$req = query($sql);
	$data = mysql_fetch_assoc($req);
	// Nouvelle entrée
	if(num_rows($sql) < 1)
	{ 
		if(is_array($stats))
		{	
			$sql = "INSERT INTO xml_ressources VALUES ('','".$stats['ville']."','".$stats['jour']."','".$stats['adate']."','".$stats['od']."','".$stats['ressources']."','".$stats['objets']."','".$stats['armes']."','".$stats['eaup']."','".$stats['eaub']."')";
			query($sql);
		}
	}
	// Mise à jour
	elseif($data['od'] != $stats['od'] or $data['ressources'] != $stats['ressources'] or $data['objets'] != $stats['objets'] or $data['armes'] != $stats['armes'] or $data['eaup'] != $stats['eaup'] or $data['eaub'] != $stats['eaub'])
	{
		$sql = "UPDATE xml_ressources SET od='".$stats['od']."', ressources='".$stats['ressources']."', objets='".$stats['objets']."', armes='".$stats['armes']."', eaup='".$stats['eaup']."', eaub='".$stats['eaub']."' WHERE ville='".$stats['ville']."' AND adate = '".date("Y-m-d")."'";
		query($sql);
	}
	
	// 3. CITOYENS ET HÉROS
	$sql = "SELECT * FROM xml_citoyens WHERE adate='".date("Y-m-d")."' AND ville='".$stats['ville']."'";
	$req = query($sql);
	$data = mysql_fetch_assoc($req);
	
	// Nouvelle entrée
	if(num_rows($sql) < 1)
	{
		if(is_array($stats))
		{
			$sql = "INSERT INTO xml_citoyens VALUES ('','".$stats['ville']."','".$stats['jour']."','".$stats['adate']."','".$stats['citoyens']."','".$stats['gardiens']."','".$stats['eclaireurs']."','".$stats['fouineurs']."','".$stats['vivants']."','".$stats['bannis']."')";
			query($sql);
		}
	}
	// Mise à jour
	elseif($data['citoyens'] != $stats['citoyens'] or $data['gardiens'] != $stats['gardiens'] or $data['eclaireurs'] != $stats['eclaireurs'] or $data['fouineurs'] != $stats['fouineurs'] or $data['vivants'] != $stats['vivants'] or $data['bannis'] != $stats['bannis'])
	{
		$sql = "UPDATE xml_citoyens SET citoyens='".$stats['citoyens']."',gardiens='".$stats['gardiens']."',eclaireurs='".$stats['eclaireurs']."',fouineurs='".$stats['fouineurs']."',vivants='".$stats['vivants']."',bannis='".$stats['bannis']."' WHERE ville='".$stats['ville']."' AND adate = '".date("Y-m-d")."'";
		query($sql);
	}
	
	// 3. CASES VISITÉES
	$sql = "SELECT * FROM xml_map WHERE adate='".date("Y-m-d")."' AND ville='".$stats['ville']."'";
	$req = query($sql);
	$data = mysql_fetch_assoc($req);
	
	// Nouvelle entrée
	if(num_rows($sql) < 1)
	{
		if(is_array($stats))
		{
			$sql = "INSERT INTO xml_map VALUES ('','".$stats['ville']."','".$stats['jour']."','".$stats['adate']."','".$stats['cases']."')";
			query($sql);
		}
	}
	// Mise à jour
	elseif($data['cases'] != $stats['cases'])
	{
		$sql = "UPDATE xml_map SET cases='".$stats['cases']."' WHERE ville='".$stats['ville']."' AND adate = '".date("Y-m-d")."'";
		query($sql);
	}
}// end if (!$stats)
} // end if isset(api key)

function getstats()
{
	// Récupérer les données
	$doc = getxml($_COOKIE['key']);
	
	// Si le XML n'est pas disponible
	$error = "";
	$errs = $doc->getElementsByTagName('error');
	foreach($errs as $err)
		$error = $err->getAttribute('code');
			
	if($error == "not_in_game"  or $error == "horde_attacking")
		return false;
		
	// Pour corriger du bug de l'attaque, ne pas mettre à jour les stats avant 2h du matin
	if(date("H") < 1)
		return false;
	
	// Date
	$adate = date("Y-m-d");
	
	// Jour de jeu
	$games = $doc->getElementsByTagName('game');
	foreach($games as $game)
		$jour = $game->getAttribute('days');
	
	if(!isset($jour) or empty($jour) or !is_numeric($jour) or !$jour)
		return false;
		
	// Nom de la ville
	$cities = $doc->getElementsByTagName('city');
	foreach($cities as $city)
		$ville = $city->getAttribute('city');
	$ville = iconv("UTF-8", "ISO-8859-1", $ville);
	$ville = addslashes($ville);
				
	// Estimation
	$attqs = $doc->getElementsByTagName('e');
	foreach($attqs as $attq)
	{
		$max = $attq->getAttribute('max');
		$min = $attq->getAttribute('min');
		$maxed = $attq->getAttribute('maxed');
		break; // pour éviter la deuxième extimation (planificateur)
	}
		
	// Estimation provisoire ?
	//if($maxed != 1) return false;
	
	// Estimation indisponible ?
	if(empty($max)) { $max = 0; $min = 0; }
	
	// Défenses
	$od = 0;
	$defs = $doc->getElementsByTagName('defense');
	foreach($defs as $def)
	{
		$defense = $def->getAttribute('total');
		$od = $def->getAttribute('items');
	}
	
	// Citoyens
	$citoyens = 0; $gardiens = 0; $eclaireurs = 0; $fouineurs = 0; $vivants = 0; $bannis = 0;
	$citizens = $doc->getElementsByTagName('citizen');
	foreach($citizens as $citizen)
	{
		$job = $citizen->getAttribute('job');
		switch($job)
		{
			case 'guardian': $gardiens++; break;
			case 'collec': $fouineurs++; break;
			case 'eclair': $eclaireurs++; break;
			case 'basic': $citoyens++; break;
		}
		$ban = $citizen->getAttribute('ban');
		if($ban != 0) $bannis++;
		$vivants++;
	}
	
	// Cases explorées
	$zones = $doc->getElementsByTagName('zone');
	$cases = 0;
	foreach($zones as $zone)
	{
		$nvt = $zone->getAttribute('nvt');
		if(!$nvt)
			$cases++;
	}
	
	// Eau
	$eaup = 0; $eaub = 0;
	$cities = $doc->getElementsByTagName('city');
	foreach($cities as $city)
		$eaup = $city->getAttribute('water');
	$items = $doc->getElementsByTagName('item');
	foreach($items as $item)
		if($item->getAttribute('id') == 1)
			$eaub = $item->getAttribute('count');

	// Ressources
	$objets = 0; $ressources = 0; $armes = 0;
	$items = $doc->getElementsByTagName('item');
	foreach($items as $item)
	{
		$objets = $objets + $item->getAttribute('count');
		if($item->getAttribute('cat') == "Weapon")
			$armes = $armes + $item->getAttribute('count');
		elseif($item->getAttribute('cat') == "Rsc")
			$ressources = $ressources + $item->getAttribute('count');
	}
	
	// Corriger le bug XML dans l'affichage du jour de jeu
	/*
	$sql = "SELECT * FROM attaque WHERE ville = '$ville' ORDER BY adate LIMIT 0,1";
	$req = query($sql);
	$count = num_rows($sql);
	$data = mysql_fetch_assoc($req);
	if($count > 0)
	{
		$hjour = $data['jour'];
		$hdate = $data['adate'];
		if($hjour == $jour and $hdate != $adate)
			$jour++;
	}
	*/
	
	// Créer la sortie
	$res['ville'] = $ville;
	$res['jour'] = $jour;
	$res['adate'] = $adate;
	$res['min'] = $min;
	$res['max'] = $max;
	$res['def'] = $defense;
	$res['od'] = $od;
	$res['eaup'] = $eaup;
	$res['eaub'] = $eaub;
	$res['objets'] = $objets;
	$res['armes'] = $armes;
	$res['ressources'] = $ressources;
	$res['citoyens'] = $citoyens;
	$res['gardiens'] = $gardiens;
	$res['eclaireurs'] = $eclaireurs;
	$res['fouineurs'] = $fouineurs;
	$res['vivants'] = $vivants;
	$res['bannis'] = $bannis;
	$res['cases'] = $cases;
	
	return $res;
}

?>