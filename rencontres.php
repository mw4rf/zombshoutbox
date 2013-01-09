<?php

function update()
{
	// Init
	$noms = array();
	$ville = "";
	 
	// Récupérer les données
	$doc = getxml($_COOKIE['key']);
	
	// Si le XML n'est pas disponible
	$error = "";
	$errs = $doc->getElementsByTagName('error');
	foreach($errs as $err)
		$error = $err->getAttribute('code');
		
	if($error == "not_in_game"  or $error == "horde_attacking")
		return false;
	
	// Liste des citoyens
	$citizens = $doc->getElementsByTagName('citizen');
	foreach($citizens as $citizen)
	{
		$noms[] = addslashes(iconv("UTF-8", "ISO-8859-1", $citizen->getAttribute('name')));
	}
	
	// Nom de la ville
	$cities = $doc->getElementsByTagName('city');
	foreach($cities as $city)
		$ville = $city->getAttribute('city');
	$ville = iconv("UTF-8", "ISO-8859-1", $ville);
	$ville = addslashes($ville);
	
	// Ajouter
	foreach($noms as $nom)
	{
		$sql = "SELECT * FROM xml_rencontres WHERE ville='$ville' AND nom='$nom'";
		$count = num_rows($sql);
		if($count < 1)
		{
			$sqlx = "INSERT INTO xml_rencontres VALUES ('','$nom','$ville','', '".date("Y-m-d")."')";
			query($sqlx);
		}
	}
	
	// Retour
	return true;
}

?>