<?php
include_once("../config.inc.php");
include_once("../fonctions.inc.php");

if(isset($_POST['a']) and $_POST['a'] == 'new')
{
	$membre = addslashes(iconv("UTF-8", "ISO-8859-1", $_POST['membre']));
	$sql = "INSERT INTO coa_coas VALUES ('','$membre')";
	query($sql);
	
	$sql = "SELECT * FROM coa_coas ORDER BY id DESC LIMIT 1";
	$req = query($sql);
	$data = mysql_fetch_assoc($req);
	$coaid = $data['id'];
	
	$sql = "UPDATE coa_users SET coa_id = '$coaid', leader='1' WHERE name = '$membre'";
	query($sql);
	
	echo "Coalition <b>n&deg;$coaid</b> cr&eacute;&eacute;e, avec <b>$membre</b> pour leader.";
}
elseif(isset($_POST['a']) and $_POST['a'] == 'modif')
{
	$membre = addslashes(iconv("UTF-8", "ISO-8859-1", $_POST['membre']));
	$coaid = $_POST['coa'];
	
	$sql = "UPDATE coa_users SET coa_id='$coaid' WHERE name='$membre'";
	query($sql);
	
	echo "<b>$membre</b> plac&eacute; dans la coa n&deg;$coaid";
}
elseif(isset($_POST['a']) and $_POST['a'] == 'del')
{
	$coaid = $_POST['coa'];
	
	$sql = "UPDATE coa_users SET leader='0' WHERE name=(SELECT leader FROM coa_coas WHERE id='$coaid')";
	query($sql);
	
	$sql = "DELETE FROM coa_coas WHERE id='$coaid'";
	query($sql);
	
	echo "Coalition <b>n&deg;$coaid</b> supprim&eacute;e";
}

?>