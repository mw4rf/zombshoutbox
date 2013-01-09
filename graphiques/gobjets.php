<?php
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
require_once("../api/artichow/LinePlot.class.php");

// Taille du graphique
if(isset($_COOKIE['prefs_graph_height']) and is_numeric($_COOKIE['prefs_graph_height']))
	$grheight = $_COOKIE['prefs_graph_height'];
else 
	$grheight = 250;

if(isset($_COOKIE['prefs_graph_width']) and is_numeric($_COOKIE['prefs_graph_width']))
	$grwidth = $_COOKIE['prefs_graph_width'];
else 
	$grwidth = 700;

// Graphique complet ou simplifié
if(isset($_COOKIE['prefs_graphfull']) and is_numeric($_COOKIE['prefs_graphfull']))
{
	if($_COOKIE['prefs_graphfull'] == 2)
		$graphfull = true;
	else
		$graphfull = false;
}
else
		$graphfull = false;

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

$sql = "SELECT * FROM xml_ressources WHERE ville = '$ville' ORDER BY jour ASC";
$req = query($sql);
$count = num_rows($sql);

$od = array();
$adate = array();
$jour = array();
$ville = "";

while($data = mysql_fetch_assoc($req))
{
	$objets[] = $data['objets'];
	$ressources[] = $data['ressources'];
	$armes[] = $data['armes'];
	
	$adate[] = $data['adate'];
	//$jour[] = "Jour ".$data['jour'];
	$ville = $data['ville'];
}

// Moyennes
$moyenne = array(); $maxobj = array(); $minobj = array();
$limit = $count+2;
$sql = "SELECT AVG(objets) AS mobjets, AVG(ressources) AS mressources, AVG(armes) AS marmes, jour, objets, MAX(objets) AS maxobj, MIN(objets) AS minobj FROM xml_ressources GROUP BY jour ORDER BY jour ASC LIMIT 0,$limit";
$req = query($sql);
while($data = mysql_fetch_assoc($req))
{
	$mobjets[] = $data['mobjets'];
	$mressources[] = $data['mressources'];
	$marmes[] = $data['marmes'];
	
	$jour[] = "J".$data['jour'];
	$total[] = $data['objets'];
	
	$maxobj[] = $data['maxobj'];
	$minobj[] = $data['minobj'];
}

// Axe vertical
$ymin = 0;
$ymax = 0;
foreach($total as $val)
	if($ymax < $total)
		$ymax = $total;

// Axe horizontal
$xmin = 1;
$xmax = $count;

// Création du graphique. Les valeurs numériques x,y passés en argument indiquent la taille en pixels du graph
$graph = new Graph($grwidth, $grheight);
$graph->setAntiAliasing(TRUE);
$graph->border->show(FALSE); // on cache les axes

$group = new PlotGroup;
   $group->setPadding(40, 40);
   $group->setBackgroundColor(
      new Color($bgcol[0],$bgcol[1],$bgcol[2])
   );

$blue = new Color(0, 0, 200);
$red = new Color(200, 0, 0);
$lavande = new Color(204, 102, 255);
$green = new Color(0,200,0);

// Création du graphique
$plot_objets = new LinePlot($objets);
$plot_maxobj = new LinePlot($maxobj);
$plot_minobj = new LinePlot($minobj);
$plot_moy_objets = new LinePlot($mobjets);
$plot_ressources = new LinePlot($ressources);
$plot_moy_ressources = new LinePlot($ressources);
$plot_armes = new LinePlot($armes);
$plot_moy_armes = new LinePlot($marmes);
$group->legend->add($plot_objets, "Total des objets", Legend::MARK);
if($graphfull) $group->legend->add($plot_moy_objets, "Moyenne Total", Legend::BACKGROUND);
$group->legend->add($plot_armes, "Armes", Legend::MARK);
if($graphfull) $group->legend->add($plot_moy_armes, "Moyenne Armes", Legend::BACKGROUND);
$group->legend->add($plot_ressources, "Ressources", Legend::MARK);
if($graphfull) $group->legend->add($plot_moy_ressources, "Moyenne Ressources", Legend::BACKGROUND);
if($graphfull) $group->legend->add($plot_maxobj, "Maxima total", Legend::LINE);
if($graphfull) $group->legend->add($plot_minobj, "Minima total", Legend::LINE);
$group->legend->setPosition(0.4, 0.2);
$group->legend->setBackground( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );
$group->legend->shadow->setSize(0);
$group->legend->border->hide();
$group->legend->setColumns(2);
$group->add($plot_objets);
if($graphfull) $group->add($plot_moy_objets);
$group->add($plot_ressources);
if($graphfull) $group->add($plot_moy_ressources);
$group->add($plot_armes);
if($graphfull) $group->add($plot_moy_armes);
if($graphfull) $group->add($plot_maxobj);
if($graphfull) $group->add($plot_minobj);
// Couleur de fond de la grille
$group->grid->setBackgroundColor( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );
$group->grid->setColor( new Color($bgcol[0]-10,$bgcol[1]-10,$bgcol[2]-10) );
// Définir les axes
$group->axis->bottom->setLabelText($jour);
//$group->axis->bottom->title = "Jours";
$group->axis->left->setLabelPrecision(0);
// Espacement
$group->setSpace(
      6, /* Gauche */
      6, /* Droite */
      15, /* Haut */
      6 /* Bas */
);
// Définir la couleur de la courbe
$plot_objets->setColor($blue);
$plot_maxobj->setColor($blue);
$plot_maxobj->setStyle(Line::DOTTED);
$plot_minobj->setColor($blue);
$plot_minobj->setStyle(Line::DOTTED);
$plot_moy_objets->setThickness(0);
$plot_moy_objets->setFillColor(new Color(0, 0, 200, 85)); //new Color(204, 102, 255, 80)
$plot_armes->setColor(new Color(200,0,0));
$plot_moy_armes->setThickness(0);
$plot_moy_armes->setFillColor(new Color(200, 0, 0, 85)); //new Color(204, 102, 255, 80)
$plot_ressources->setColor(new Color(0,200,00));
$plot_moy_ressources->setThickness(0);
$plot_moy_ressources->setFillColor(new Color(0, 200, 0, 85)); //new Color(204, 102, 255, 80)
// Définir l'axe de la courbe (ici, axe gauche)
$plot_objets->setYAxis(Plot::LEFT);
$plot_moy_objets->setYAxis(Plot::LEFT);
$plot_armes->setYAxis(Plot::LEFT);
$plot_moy_armes->setYAxis(Plot::LEFT);
$plot_ressources->setYAxis(Plot::LEFT);
$plot_moy_ressources->setYAxis(Plot::LEFT);
// Ne pas afficher la ligne brisée
//$plot_objets->hideLine(TRUE);
// Titre du graph
$group->title = new Label("[$ville] Objets en banque");
// LEGENDE AUX SOMMETS DE LA COURBE
	// On donne les valeurs prises par les étiquettes
   // Ici, on utilise les valeurs de chaque sommet de la courbe 
   $plot_objets->label->set($objets);
	$plot_armes->label->set($armes);
	$plot_ressources->label->set($ressources);
   // On place l'étiquette par rapport au sommet
   // Par défaut, les étiquettes sont centrées sur les sommet
   // Dans le cas présent, on place l'étiquette un peu plus en haut
   $plot_objets->label->move(0, -23);
	$plot_armes->label->move(0, -23);
	$plot_ressources->label->move(0, -23);
   // On donne aux étiquettes un dégradé de fond
   $plot_objets->label->setBackgroundGradient(new LinearGradient(new Color(250, 250, 250, 10),new Color(0, 0, 200, 80),0));
   $plot_armes->label->setBackgroundGradient(new LinearGradient(new Color(250, 250, 250, 10),new Color(200, 0, 0, 80),0));
   $plot_ressources->label->setBackgroundGradient(new LinearGradient(new Color(250, 250, 250, 10),new Color(0, 200, 0, 80),0));
   // On entoure les étiquettes d'une bordure
   // Cette bordure est presque noire et légèrement transparente
   $plot_objets->label->border->setColor(new Color(20, 20, 20, 20));
	$plot_armes->label->border->setColor(new Color(20, 20, 20, 20));
	$plot_ressources->label->border->setColor(new Color(20, 20, 20, 20));
   // Enfin, on ajoute un espace interne entre la bordure et le texte des étiquettes
   // Cette opération est nécessaire car les polices fournies par PHP
   // n'ont pas un rendu très bon
   $plot_objets->label->setPadding(3, 1, 1, 0);
	$plot_armes->label->setPadding(3, 1, 1, 0);
	$plot_ressources->label->setPadding(3, 1, 1, 0);
// MARQUE AUX SOMMETS DE LA COURBE
$plot_objets->mark->setType(Mark::CIRCLE); 	// un rond ...
$plot_armes->mark->setType(Mark::INVERTED_TRIANGLE);
$plot_ressources->mark->setType(Mark::TRIANGLE);
$plot_objets->mark->border = new Border(Line::SOLID); // ... avec une bordure
$plot_objets->mark->setFill($blue);
$plot_armes->mark->setFill($red);
$plot_ressources->mark->setFill($green);

// Affichage du graph
$graph->add($group);
$graph->draw();
?>