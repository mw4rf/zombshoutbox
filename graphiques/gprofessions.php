<?php
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
require_once("../api/artichow/Pie.class.php");

// Taille du graphique
if(isset($_COOKIE['prefs_graph_height']) and is_numeric($_COOKIE['prefs_graph_height']))
	$grheight = $_COOKIE['prefs_graph_height'];
else 
	$grheight = 250;

if(isset($_COOKIE['prefs_graph_width']) and is_numeric($_COOKIE['prefs_graph_width']))
	$grwidth = $_COOKIE['prefs_graph_width'];
else 
	$grwidth = 700;

// Skins
if(isset($_COOKIE['prefs_skincss']))
	include_once("../styles/".$_COOKIE['prefs_skincss'].".php");
else
	include_once("../styles/Hordes.php");

$bgcol = explode(',',$bgalt);

// Récupération des données
if(isset($_GET['ville']))
	$ville = urldecode($_GET['ville']);
else
{
	$doc = getxml($_COOKIE['key']);
	$cities = $doc->getElementsByTagName('city');
	foreach($cities as $city)
		$ville = $city->getAttribute('city');
	$ville = iconv("UTF-8", "ISO-8859-1", $ville);
	$ville = addslashes($ville);
}

$sql = "SELECT * FROM xml_citoyens WHERE ville = '$ville' ORDER BY jour DESC LIMIT 0,1";
$req = query($sql);
$count = num_rows($sql);

$citoyens = array();
$adate = array();
$jour = array();
$ville = "";

while($data = mysql_fetch_assoc($req))
{
	$citoyens['citoyens'] = $data['citoyens'];
	if($data['gardiens']) $citoyens['gardiens'] = $data['gardiens'];
	if($data['eclaireurs']) $citoyens['eclaireurs'] = $data['eclaireurs'];
	if($data['fouineurs']) $citoyens['fouineurs'] = $data['fouineurs'];
	
	$adate[] = $data['adate'];
	$jour[] = $data['jour'];
	$ville = $data['ville'];
}

##################################################################################################
$gpie = new Graph($grwidth, $grheight);
$gpie->setAntiAliasing(TRUE);
$gpie->setBackgroundColor( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );
$gpie->border->show(FALSE); // on cache les axes

$colors = array(new Color(0,0,200), new Color(200,0,0), new Color(0,128,64), new Color(255,255,0));

$pie = new Pie(array_values($citoyens), $colors);
$pie->setLabelPrecision(1);

$pie->setLegend(array_keys($citoyens));
$pie->legend->setPosition(1.45, .25);
$pie->legend->setBackground( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );
$pie->legend->shadow->setSize(0);
$pie->legend->border->hide();

$pie->setCenter(.36, .58);
$pie->setSize(.65, .65);
$pie->set3D(5);
$pie->setBackgroundColor( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );

$pie->title->set("[$ville, Jour ".$jour[0]."] Répartition des professions");
$pie->title->move(0, -40);
$pie->title->setPadding(5, 5, 2, 2);

$gpie->add($pie);

##################################################################################################
$gpie->draw();
?>