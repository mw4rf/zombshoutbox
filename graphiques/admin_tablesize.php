<?php
###########################
# Ce graphique affiche les nombre d'enregistrements par table et la taille de chaque table
###########################

// Appel des bibliothèques
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
require_once("../api/artichow/BarPlot.class.php");

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

$TABCOUNT = ""; // le nombre d'enregistrements de chaque table
$TABSUM = ""; // la taille de chaque table
$TABNOM = ""; // le nom de chaque table
$index = 0;

// Récupération et formatage des données
$sql = "SHOW TABLE STATUS;";
$result = query($sql);
while ($row = mysql_fetch_array($result))
	{
	if($row['Name'] != 'log')
	{
	$TABSUM[$index] = ( $row['Data_length'] + $row['Index_length'] ) / 10000; // taille totale données + index
	$TABCOUNT[$index] = $row['Rows']; // nombre de ligne dans la table
	$TABNOM[$index] = $row['Name']; // nom de la table
	$index++;
	}
	}

// Création du graphique
$graph = new Graph(800, 300);
$graph->setAntiAliasing(TRUE);
$graph->border->show(FALSE);

$blue = new Color(0, 128, 255); 
$red = new Color(204, 102, 155);

$group = new PlotGroup;
$group->setPadding(60, 60, 5, 80); // gauche, droite, haut, bas

// Définir les axes
$group->axis->bottom->setLabelNumber($index+1);
$group->axis->bottom->setLabelText($TABNOM);
$group->axis->bottom->label->setAngle(90);
	
// 1er graph : nombre d'enregistrements par table
$plot = new BarPlot($TABCOUNT, 1, 2);
$plot->setBarColor($blue);
$plot->setYAxis(Plot::LEFT);

//$plot->label->set($TABCOUNT);	

// $plot->barShadow->smooth(TRUE);
// $plot->barShadow->setSize(3);
//    $plot->barShadow->setPosition(Shadow::RIGHT_TOP);
//    $plot->barShadow->setColor(new Color(204,204,204)); // gris clair

$group->add($plot);
$group->axis->left->setColor($blue);
$group->axis->left->title->set("Nombre d'enregistrements");
//$group->axis->left->title->move(-20,0);
$group->axis->left->title->setPadding(0,30,0,0);

// 2ème graph :taille de chaque table
$plot = new BarPlot($TABSUM, 2, 2);
$plot->setBarColor($red);
$plot->setYAxis(Plot::RIGHT);

//$plot->label->set($TABSUM);
	
// $plot->barShadow->setSize(3);
// $plot->barShadow->setPosition(Shadow::RIGHT_TOP);
// $plot->barShadow->setColor(new Color(204,204,204)); // gris clair
// $plot->barShadow->smooth(TRUE);

$group->add($plot);
$group->axis->right->setColor($red);
$group->axis->right->title->set("Taille des tables");
//$group->axis->right->title->move(20,0);
$group->axis->right->title->setPadding(30,0,0,0);

// Fond du graph
// Définir la couleur de fond de la zone
$graph->setBackgroundColor(new Color($bgcol[0],$bgcol[1],$bgcol[2])); // R-V-B-Transparence
$group->setBackgroundColor(new Color($bgcol[0],$bgcol[1],$bgcol[2])); // R-V-B-Transparence
$group->grid->setBackgroundColor(new Color($bgcol[0],$bgcol[1],$bgcol[2])); // R-V-B-Transparence
$group->grid->setColor(new Color($bgcol[0],$bgcol[1],$bgcol[2]));

$graph->add($group);
$graph->draw();

?>