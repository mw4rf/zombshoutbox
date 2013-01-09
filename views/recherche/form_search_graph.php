<?php
if(isset($_POST['table'])) 
{
	switch($_POST['table'])
	{
		case 'gattaque': $graphique = "xml_attaque"; break;
		case 'gcitoyens': $graphique = "xml_citoyens"; break;
		case 'geau': $graphique = "xml_ressources"; break;
		case 'gmap': $graphique = "xml_map"; break;
		case 'gobjdef': $graphique = "xml_ressources"; break;
		case 'gobjets': $graphique = "xml_ressources"; break;
		case 'gprofessions': $graphique = "xml_citoyens"; break;
	}
}
else
	$graphique = 'xml_attaque';

$sql = "SELECT ville, MAX(jour) AS mj, COUNT(ville) as cv FROM $graphique GROUP BY ville ORDER BY id DESC";
$req = query($sql);

echo "<select id=\"s_graphiques_villes\">";
while($data = mysql_fetch_assoc($req))
{
	if(!empty($data['ville']) and $data['cv'] > 1) // pas de graph si ville de moins de 2 jours
	{
		$ville = iconv("ISO-8859-1", "UTF-8", $data['ville']); // nécessaire depuis le passage par ajax / rev 30
		$mj = $data['mj'];
		echo "<option value=\"$ville\">".$ville." [".$mj."J]</option>";
	}
}
echo "</select>";
?>