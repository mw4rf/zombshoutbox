<?php
session_start();
header( 'Content-Type: text/xml; charset=ISO-8859-1' );
include_once("../config.inc.php");
include_once("../fonctions.inc.php");

if(isset($_POST['a']) and $_POST['a'] == 'new')
{
	$id = $_POST['id'];
	$note = addslashes(iconv("UTF-8","ISO-8859-1",$_POST['note']));
	$ville = "?";
	$adate = date("Y-m-d");
	
	// Ville
	if(!empty($_COOKIE['key']))
	{
		$doc = getxml($_COOKIE['key']);
		$error = "";
		$errs = $doc->getElementsByTagName('error');
		foreach($errs as $err)
			$error = $err->getAttribute('code');	
		if(empty($error) and $error != "not_in_game" and $error != "horde_attacking")
		{			
			// Nom de la ville
			$cities = $doc->getElementsByTagName('city');
			foreach($cities as $city)
				$ville = $city->getAttribute('city');
			$ville = iconv("UTF-8", "ISO-8859-1", $ville);
			$ville = addslashes($ville);
		}
	}
		
	$sql = "UPDATE xml_rencontres SET note='$note' WHERE id='$id'";
	query($sql);		
}
else
{
	echo "Vous ne pouvez pas appeler cette page directement.";
}


?>