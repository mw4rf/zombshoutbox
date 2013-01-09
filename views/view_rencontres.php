<?php
if(isset($_POST['ville'])) // appel par AJAX depuis la racine
{
	include_once("config.inc.php");
	include_once("fonctions.inc.php");
	include_once("classes/MWLXML.php");	
}
else // appel depuis tab_xml.php
{
	include_once("../config.inc.php");
	include_once("../fonctions.inc.php");
	include_once("../classes/MWLXML.php");
}
##########################################################################################
// Liste des citoyens de la ville
// Ville
$key = $_COOKIE['key'];
	
if(isset($_POST['ville']))
{
	$ville = iconv("UTF-8", "ISO-8859-1", $_POST['ville']); // v. rev 30, passage par AJAX donc encoding utf8
	$txt = "Liste des citoyens de la ville <i><b>".stripslashes($ville)."</b></i>";
}
else
{
	$doc = getxml($_COOKIE['key']);
	$cities = $doc->getElementsByTagName('city');
	foreach($cities as $city)
		$ville = $city->getAttribute('city');
	$txt = "Liste des citoyens de la ville <i><b>$ville</b></i>";
	$ville = iconv("UTF-8", "ISO-8859-1", $ville);
	$ville = addslashes($ville);
}
		
$output = "";

// Construction de la liste
$msg = "<table width=\"100%\" cellspacing=0>";
$msg .= "<tr>"
    ."<td width=\"20%\"></td>"
	."<td width=\"10%\"></td>"
	."<td width=\"50%\"></td>"
	."<td width=\"10%\"></td>"
	."<td width=\"5%\" class=\"small d3\" style=\"text-align:center;\">MWL<br />Plaintes</td>"
	."<td width=\"5%\" class=\"small d1\" style=\"text-align:center;\">MWL<br />Recoms.</td>"
	."</tr>";
$ma_ville = $ville; // Pour plus tard... va savoir pourquoi, le contenu de $ville semble être corrompu (encoding) après ici...
$sql = "SELECT * FROM xml_rencontres WHERE ville='$ville'";
$req = query($sql);
$citoyens = array();
while($data = mysql_fetch_assoc($req))
{
	$nom = trim(ltrim($data['nom']));
	$citoyens[$nom]['note'] = stripslashes(iconv("ISO-8859-1","UTF-8",$data['note']));
	$citoyens[$nom]['id'] = $data['id'];
}
// MWL
$mwlurl = "";
foreach($citoyens as $nom=>$citoyen)
	$mwlurl .= $nom.",";
$mwlurl = trim($mwlurl,",");
$mwl = new MWLXML();
$mwl->load_withName($mwlurl);

foreach($citoyens as $nom=>$infos)
{
	/* Calcul du ratio :
		=>	Nombre de recommandations / nombre de plaintes  <=
			SI nombre de plaintes est 0, alors nombre de plaintes défini à 1
			SI le ration est supérieur à 1, alors il est arrondi à l'entier supérieur
	*/ 	
	$mwlinfos = $mwl->getInfos($nom);
	$rt = $mwlinfos['ratio'];	
	if($rt > 20)		$ix = "rating_plus_5"; // 5 étoiles
	elseif($rt > 15)	$ix = "rating_plus_4"; // 4 étoiles
	elseif($rt > 10)	$ix = "rating_plus_3"; // etc.
	elseif($rt > 5)		$ix = "rating_plus_2";
	elseif($rt >= 1)	$ix = "rating_plus_1";
	elseif($rt > 0.8)	$ix = "rating_moins_1"; // 1 petite maison banni
	elseif($rt > 0.6)	$ix = "rating_moins_2"; // 2 petites maisons banni
	elseif($rt > 0.4)	$ix = "rating_moins_3"; // etc.
	elseif($rt > 0.2)	$ix = "rating_moins_4";
	elseif($rt > 0)		$ix = "rating_moins_5";
	else $ix = "h_warning"; // ratio inconnu, l'utilisateur cache ses infos MWL
	$ro = "<img src=\"smilies/$ix.gif\" />";
	
	$msg .= "<tr id=\"rencontres_$nom\" onMouseOver=\"onlight('rencontres_$nom')\" onMouseOut=\"offlight('rencontres_$nom')\">"
		."<td><span style=\"font-weight:bold;cursor:pointer;\" onclick=\"document.getElementById('nom').value = '$nom';\">$nom</span></td>"
		 ."<td style=\"text-align:right;\">$ro</td>"
		 ."<td><input type=\"text\" value=\"".$infos['note']."\" id=\"cit_".$infos['id']."\" style=\"width:98%\"/></td>"
		 ."<td><input type=\"submit\" value=\"Enregistrer\" onclick=\"addcit(".$infos['id'].")\" id=\"cit_b_".$infos['id']."\" /></td>"
		."<td style=\"text-align:center;\" class=\"d3\">".$mwlinfos['plaintes']."</td>"
		."<td style=\"text-align:center;\" class=\"d1\">".$mwlinfos['recomms']."</td>"
		."</tr>";
}
		
$msg .= "</table>";
$output .= "<p>&nbsp;</p><div class=\"command_tab big center\">$txt</div><div id=\"listcittab\" class=\"msg_text\">$msg</div>";
##########################################################################################
// Liste des citoyens déjà rencontrés
$sql = "SELECT * FROM xml_rencontres WHERE ville='$ville'";
$req = query($sql);
$out = "";

if(isset($_POST['ville']))
{
	$ville = $_POST['ville'];
}
else
{
	$doc = getxml($_COOKIE['key']);
	$cities = $doc->getElementsByTagName('city');
	foreach($cities as $city)
		$ville = $city->getAttribute('city');
}
	
while($data = mysql_fetch_assoc($req))
{
	$nom = stripslashes($data['nom']);
	
	// Si membre coa, sortir
	$sqlc = "SELECT * FROM coa_users WHERE name='$nom'"; // attention : table 'users' des membres de la méta !!
	$countc = num_rows($sqlc);
	if($countc > 0) continue;
	
	// Sinon le rechercher dans les rencontres
	$ville = $ma_ville;
	$sqlv = "SELECT * FROM xml_rencontres WHERE nom='$nom' and ville != '$ville'";
	$reqv = query($sqlv);
	
	//echo $sqlv."<br />";
	
	$hasmet = false;
	$innerout = "";
	while($datav = mysql_fetch_assoc($reqv))
	{
			$adate = fdate($datav['adate']);
			$ville = iconv("ISO-8859-1", "UTF-8", stripslashes($datav['ville']));
			$note = iconv("ISO-8859-1", "UTF-8", stripslashes($datav['note']));
		
			$innerout .= "<li />[$adate] $ville <i>$note</i>";
			$hasmet = true;
	}
	
	if($hasmet)
		$out .= "<b>".stripslashes($nom)."</b><ul>$innerout</ul>";
}
if(empty($out)) $out = "Aucune vieille connaissance dans votre ville ($ville)";
$output .= "<p>&nbsp;</p><div class=\"command_tab big center\">Vieilles connaissances</div><div class=\"msg_text\">".$out."</div><p>&nbsp;</p>";

// Affichage
echo $output;
?>