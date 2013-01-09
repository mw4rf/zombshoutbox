<?php
session_start();
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
include_once("../classes/HordesXML.php");

if(isset($_POST['a']) and $_POST['a'] == 'new')
{
	$sdate = fdate($_POST['sdate'],true)." ".$_POST['stime'].":00";
	$edate = fdate($_POST['edate'],true)." ".$_POST['etime'].":00";
	$action = $_POST['action'];
	$user = $_COOKIE['user'];
	
	// Combien de jours dans l'intervalle ?	
	$nbjours = round((strtotime($edate) - strtotime($sdate))/(60*60*24)-1) + 2;

	// Timestamp de fin
	$ets = fdatetime($edate,"H,i,s,m,d,Y");
	$ets = explode(",",$ets);
	
	// Timestamp de début
	$sts = fdatetime($sdate,"H,i,s,m,d,Y");
	$sts = explode(",",$sts);
	
	for($i = 0 ; $i < $nbjours ; $i++)
	{
		$edate = date("Y-m-d",mktime(0,0,0,$sts[3],$sts[4]+$i,$sts[5]))." ".$_POST['etime'].":00";
		$sdate = date("Y-m-d",mktime(0,0,0,$sts[3],$sts[4]+$i,$sts[5]))." ".$_POST['stime'].":00";
		
		$sql = "INSERT INTO events VALUES ('','$user','$action','$sdate','$edate')";
		query($sql);
	}
		
	echo "Pr&eacute;vision enregistr&eacute;e. Merci !";
}
if(isset($_POST['a']) and $_POST['a'] == 'newquick')
{
	if($_POST['date'] == 'ajd')
	{
		$sdate = date("Y-m-d H:i:s");
		$edate = date("Y-m-d 23:59:59");
	}
	elseif($_POST['date'] == 'demain')
	{
		$sdate = date("Y-m-d H:i:s",mktime(0,0,0,date("m"),date("d")+1,date("Y")));
		$edate = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")+1,date("Y")));
	}
	else return false;
	
	$action = $_POST['action'];
	$user = $_COOKIE['user'];
	
	$sql = "INSERT INTO events VALUES ('','$user','$action','$sdate','$edate')";
	query($sql);
		
	echo "Pr&eacute;vision enregistr&eacute;e. Merci !";
}
elseif(isset($_POST['a']) and isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['a'] == 'del')
{
	$id = $_POST['id'];
	$sql = "DELETE FROM events WHERE id='$id'";
	query($sql);		
}
elseif(isset($_POST['a']) and $_POST['a'] == 'newexpe')
{
	
	$trajet = $_POST['trajet'];
	$depart = fdate($_POST['depart_date'],true) . " " . $_POST['depart_time'] . ":00";
	$arrivee = fdate($_POST['depart_date'],true) . " " . $_POST['arrivee_time'] . ":00";
	$pps = trim($_POST['participants'],",");
	
	$expe = "";
	$hxml = new HordesXML();
	$trajets = $hxml->getExpeditions();
	foreach($trajets as $traj)
		if($traj['author'] == $trajet)
		{
			$leader = $trajet;
			$name = $traj['name'];
			$pa = $traj['length'];
		}
	
	if(empty($pps)) $pps = $_COOKIE['user'];
	
	$sql = "INSERT INTO expeditions VALUES ('','$leader','$pps','$name','$pa','$depart','$arrivee')";
	query($sql);	
	
	echo "Exp&eacute;dition cr&eacute;&eacute;e";
}
elseif(isset($_POST['a']) and isset($_POST['id']) and is_numeric($_POST['id']) and $_POST['a'] == 'delexpe')
{
	$id = $_POST['id'];
	$sql = "DELETE FROM expeditions WHERE id='$id'";
	query($sql);		
}


?>