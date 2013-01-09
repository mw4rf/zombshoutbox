<?php
session_start();
header( 'Content-Type: text/xml; charset=ISO-8859-1' );
include_once("../config.inc.php");
include_once("../fonctions.inc.php");

if(isset($_POST['a']) and $_POST['a'] == 'new')
{
	$nom = $_POST['nom'];
	$raison = addslashes(iconv("UTF-8","ISO-8859-1",$_POST['raison']));
	$priorite = $_POST['priorite'];
	$user = $_COOKIE['user'];
	$note = 1;
	$ville = "?";
	$adate = date("Y-m-d H:i:s");
	
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
			$cities = $doc->getElementsByTagName('city');
			foreach($cities as $city)
				$ville = addslashes(iconv("UTF-8", "ISO-8859-1",$city->getAttribute('city')));
		}
	}
		
	$sql = "INSERT INTO parias VALUES ('','$nom','$ville','$raison','$user', '$note', '$priorite', '$adate', '$user')";
	query($sql);
		
	echo "Vilain enregistr&eacute;, merci !";
}
elseif(isset($_POST['a']) and $_POST['a'] == 'note' and isset($_COOKIE['user']) and isset($_POST['note']))
{
	$user = $_COOKIE['user'];
	$id = $_POST['id'];
	
	if($_POST['note'] == '+')
	{
		$sql = "UPDATE parias SET note = (note+1) WHERE id='$id'";
		query($sql);	
	
		$sql = "SELECT votes FROM parias WHERE id='$id'";
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		$votes = $data['votes'];
	
		if($votes == "")
			$votes .= addslashes($user);
		else
			$votes .= ",".addslashes($user);
		
		$votes = ltrim(trim($votes,","),",");
		
		$sql = "UPDATE parias SET votes = '$votes' WHERE id='$id'";
		query($sql);
	}
	elseif($_POST['note'] == '-')
	{
		$sql = "UPDATE parias SET note = (note-1) WHERE id='$id'";
		query($sql);	
	
		$sql = "SELECT votes FROM parias WHERE id='$id'";
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		$votes = $data['votes'];
	
		$nvotes = "";
		$votes = explode(",",$votes);
		foreach($votes as $vote)
			if($vote != $user)
				$nvotes .= $vote.",";
		$nvotes = ltrim(trim($nvotes,","),",");
		
		$sql = "UPDATE parias SET votes = '$nvotes' WHERE id='$id'";
		query($sql);
	}
}
elseif(isset($_POST['a']) and $_POST['a'] == 'rmv')
{
	$id = $_POST['id'];
	$sql = "DELETE FROM parias WHERE id='$id'";
	query($sql);	
}
else
{
	echo "Vous ne pouvez pas appeler cette page directement.";
}


?>