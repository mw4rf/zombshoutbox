<?php
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
?>

<div id="pariascontainer">
	
<table id="pariastbl">
	<tr>
		<td class="pariastbl_header" width="10%">Vilain</td>
		<td class="pariastbl_header" width="30%">Raison</td>
		<td class="pariastbl_header" width="20%">Ville</td>
		<td class="pariastbl_header" width="10%">D&eacute;lateur</td>
		<td class="pariastbl_header" width="10%" style=\text-align:center;\>Priorit&eacute;</td>
		<td class="pariastbl_header" width="2%" style=\text-align:center;\>Votes</td>
		<td class="pariastbl_header" width="7%">&nbsp;</td>
		<td class="pariastbl_header" width="10%">Date</td>
	</tr>
<?php
	// Pouvoirs d'admin
	$sql = "SELECT * FROM users_registered WHERE user='".$_COOKIE['user']."'";
	$req = query($sql);
	$data = mysql_fetch_assoc($req);
	$isadmin = $data['admin'];
	
	// Liste des citoyens de la ville
	if(isset($_COOKIE['key']))
	{
		$doc = getxml($_COOKIE['key']);
		$citizens = $doc->getElementsByTagName('citizen');
		$citoyens = array();
		foreach($citizens as $citizen)
		{
			$nom = $citizen->getAttribute('name');
			$banni = $citizen->getAttribute('ban');
			$citoyens[$nom] = $banni;
		}
	}
	
	// Nom de la ville
	$cities = $doc->getElementsByTagName('city');
	foreach($cities as $city)
		$ville = iconv("UTF-8","ISO-8859-1",$city->getAttribute('city'));
	
	// Parcourir les entrées de la bdd
	$sql = "SELECT * FROM parias WHERE note > 0 AND ville='".addslashes($ville)."' ORDER BY priorite DESC";
	$req = query($sql);
	$count = num_rows($sql);
	if($count < 1)
		echo "<tr><td colspan=\"8\" style=\"text-align:center;\">Aucun Vilain signal&eacute; pour cette ville.</td></tr>";
	else
	while($data = mysql_fetch_assoc($req))
	{
		switch($data['priorite'])
		{
			case 1: $priorite = "<span class=\"prio1\">P&eacute;nible</span>"; break;
			case 2: $priorite = "<span class=\"prio2\">D&eacute;sagr&eacute;able</span>"; break;
			case 3: $priorite = "<span class=\"prio3\">Vilain</span>"; break;
			case 4: $priorite = "<span class=\"prio4\">Mis&eacute;rable</span>"; break;
			case 5: $priorite = "<span class=\"prio5\">Immonde</span>"; break;
			default: $priorite = "<span class=\"prio3\">Vilain</span>"; break;
		}
		
		$date = fdatetime($data['adate'],"d M");
		
		// L'utilisateur a-t-il déjà voté ?
		if(isset($_COOKIE['user']))
		{
			$user = $_COOKIE['user'];
			$hasvoted = strripos($data['votes'],$user);
			if($hasvoted === false)
				$formvote = "<input type=\"submit\" value=\"+\" onclick=\"noteparia('".$data['id']."','+')\">";
			else
				$formvote = "<input type=\"submit\" value=\"-\" onclick=\"noteparia('".$data['id']."','-')\">";
		}
		else
			$formvote = "";
			
		// Banni ?
		if( isset($_COOKIE['key']) and isset($citoyens[$data['nom']]) and  $citoyens[$data['nom']] == 1)
			$ban = "<img src=\"smilies/h_ban.gif\" />";
		else
			$ban = "";
		
		// Commandes admin
		if($isadmin)
		{
			$id = "[".$data['id']."] ";
			$rmv = "&nbsp;<input type=\"submit\" value=\"X\" onclick=\"rmvparia('".$data['id']."')\">";
		}
		else
		{
			$id = "";
			$rmv = "";
		}
		
		$res = "\n<tr>"
			."<td>$ban<b>".$data['nom']."</b></td>"
			."<td>".stripslashes($data['raison'])."</td>"
			."<td>".$data['ville']."</td>"
			."<td>".$data['user']."</td>"
			."<td style=\"text-align:center;\">$priorite</td>"
			."<td style=\"text-align:center;\">".$data['note']."</td>"
			."<td style=\"text-align:center;\">$formvote</td>"
			."<td>$date$rmv</td>"
			."</tr>";
		echo iconv("ISO-8859-1", "UTF-8", $res);
	}
?>
</table>
</div>