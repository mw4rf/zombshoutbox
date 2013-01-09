<?php
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
$output = "";

// Limites
if(isset($_POST['mplimit']) and is_numeric($_POST['mplimit']))
{
	$mplimit = "LIMIT 0,".$_POST['mplimit'];
}
else
{
	if(isset($_COOKIE['prefs_mplimit']) and is_numeric($_COOKIE['prefs_mplimit']))
		$mplimit = "LIMIT 0,".$_COOKIE['prefs_mplimit'];
	else
		$mplimit = "LIMIT 0,20";
}

// Messages système
if(isset($_POST['mpsystem']))
{
	if($_POST['mpsystem'] == "Non")
		$mpsystem = "AND user != 'SYSTEM'";
	else
		$mpsystem = "";
}
else
{
	if(isset($_COOKIE['prefs_mpsystem']))
	{
		if($_COOKIE['prefs_mpsystem'] == "Non")
			$mpsystem = "AND user != 'SYSTEM'";
		else
			$mpsystem = "";
	}
	else
		$mpsystem = "";
}


$user = $_COOKIE['user'];
$sql = "SELECT * FROM messages WHERE pm != '0' AND (user='$user' OR pm='$user') $mpsystem ORDER BY id DESC $mplimit";
$req = query($sql);

$now = date("H:i:s");
$output .= "<div class=\"tabmaj\">Derni&egrave;re mise &agrave; jour &agrave; $now&nbsp;&nbsp;<input id=\"tab_mp_rb\" type=\"submit\" value=\"&#x238B; Actualiser\" onclick=\"ajaxrefresh('tab_mp')\" /></div>";

// Marquer lus et supprimer
$output .= "<div class=\"tabmaj\" style=\"clear:both;\">";
$output .= "<input type=\"submit\" onclick=\"mpread()\" value=\"Tout marquer comme lu\" />";

// Nombre de MP à afficher
$output .= "&nbsp;";
$limites = array(20,50,100,200,500,1000,'all');
$output .= "<select name=\"mplimit\" id=\"mplimit\" onchange=\"mpsort()\">";
foreach($limites as $lim)
	if(isset($_POST['mplimit']) and is_numeric($_POST['mplimit']) and $_POST['mplimit'] == $lim)
		$output .= "<option value=\"$lim\" selected>Afficher les $lim derniers messages priv&eacute;s</option>";
	elseif(isset($_POST['mplimit']) and !is_numeric($_POST['mplimit']) and $_POST['mplimit'] == $lim)
		$output .= "<option value=\"$lim\" selected>Afficher tous les messages priv&eacute;s</option>";
	elseif(isset($_POST['mplimit']) and $lim == 'all')
		$output .= "<option value=\"$lim\">Afficher tous les messages priv&eacute;s</option>";
	elseif(!isset($_POST['mplimit']) and $lim == 50)
		$output .= "<option value=\"$lim\" selected>Afficher les $lim derniers messages priv&eacute;s</option>";
	else
		if($lim == 'all')
			$output .= "<option value=\"$lim\">Afficher tous les messages priv&eacute;s</option>";
		else
			$output .= "<option value=\"$lim\">Afficher les $lim derniers messages priv&eacute;s</option>";
	
$output .= "</select>";

// Afficher les MP système ?
if(isset($_POST['mpsystem']) and $_POST['mpsystem'] == 1) 
	{ $syes = " selected"; $sno = ""; } else { $syes = ""; $sno = " selected";}
$output .= "&nbsp;<select name=\"mpsystem\" id=\"mpsystem\" onchange=\"mpsort()\">";
$output .= "<option value=\"Oui\"$syes>Afficher les messages syst&egrave;me</option>";
$output .= "<option value=\"Non\"$sno>Ne pas afficher les messages syst&egrave;me</option>";
$output .= "</select>";

$output .= "</div><br />";

// Suppression des MP. Cela fonctionne parfaitement, mais la fonction est désactivée pour le moment.
//"&nbsp;<input type=\"submit\" onclick=\"mpdelete()\" value=\"&#x2326; Supprimer les messages lus\"></div>";

// Les MP
while($data = mysql_fetch_assoc($req))
{
	$message = parsing(iconv("ISO-8859-1","UTF-8",$data['message']));
	$message = stripslashes($message);
	
	// Envoyé ou reçu ?
	if($data['user'] == $user)
		$pm = "<i>(&agrave; ".$data['pm'].")</i>&nbsp;"; 
	else
		$pm = "<i>(priv&eacute;)</i>&nbsp;";
	
	// Lu ou non lu ?
	if($data['pm_unread'] == "no")
		$class = "class = \"pm_old\"";
	else
		$class = "class = \"pm_new\"";
	
	// Suppression
	$delmp = "[<a href=\"#\" onclick=\"delmp('".$data['id']."','1');\">Suppr.</a>]";
	
	$output .= "\n<div id=\"".$data['id']."\" class=\"msg\" style=\"display:block;\"><div>";
	$output .= "<span class=\"msg_date\">".$delmp."[".date("d M - H:i:s",$data["timestamp"])."]</span>";
	$output	.="<span class=\"msg_user\">"
				."<span class=\"user_m\" onclick=\"mp('".$data['user']."')\">".$data["user"]."</span>&nbsp;$pm&gt;</span> ";
	$output	.="<span $class>".$message."</span></div>";
	$output .= "</div>";
}
echo $output;

?>