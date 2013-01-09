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
$diff = array();
$ville = "";
$i = 0;

while($data = mysql_fetch_assoc($req))
{
	$od[] = $data['od'];
	$adate[] = $data['adate'];
	//$jour[] = "Jour ".$data['jour'];
	$ville = $data['ville'];
	
	// De plus qu'hier
	if(!isset($od[$i-1]) or !is_numeric($od[$i-1])) $yst = 0;
	else $yst = $od[$i-1];
	
	$diff[] = $od[$i] - $yst;
	
	// jour
	$i++;
}

// Moyennes
$moyenne = array(); $minod = array(); $maxod = array();
$limit = $count+2;
$sql = "SELECT AVG(od) AS moyenne, jour, od, MAX(od) as maxod, MIN(od) as minod FROM xml_ressources GROUP BY jour ORDER BY jour ASC LIMIT 0,$limit";
$req = query($sql);
while($data = mysql_fetch_assoc($req))
{
	$moyenne[] = $data['moyenne'];
	$jour[] = "J".$data['jour'];
	$minod[] = $data['minod'];
	$maxod[] = $data['maxod'];
	

	
	// Axe vertical
	$ymin = 0;
	$ymax = $data['od'];
}

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

// Création du graphique
$plot_def = new LinePlot($od);
$plot_moy = new LinePlot($moyenne);
$plot_minod = new LinePlot($minod);
$plot_maxod = new LinePlot($maxod);
$plot_diff = new LinePlot($diff);
$group->legend->add($plot_def, "Nombre d'OD", Legend::MARK);
$group->legend->add($plot_diff, "Nombre d'OD en plus", Legend::MARK);
if($graphfull) $group->legend->add($plot_moy, "Moyenne", Legend::BACKGROUND);
if($graphfull) $group->legend->add($plot_maxod, "Maxima", Legend::LINE);
if($graphfull) $group->legend->add($plot_minod, "Minima", Legend::LINE);
$group->legend->setPosition(0.30, 0.2);
$group->legend->setBackground( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );
$group->legend->shadow->setSize(0);
$group->legend->border->hide();
if($graphfull) $group->add($plot_minod);
if($graphfull) $group->add($plot_maxod);
if($graphfull) $group->add($plot_moy);
$group->add($plot_def);
$group->add($plot_diff);
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
$plot_def->setColor($blue);
$plot_diff->setColor($lavande);
$plot_moy->setThickness(0);
$plot_moy->setFillColor(new Color(0, 0, 200, 85)); //new Color(204, 102, 255, 80)
$plot_minod->setColor($red);
$plot_minod->setThickness(1);
$plot_minod->setStyle(Line::DOTTED);
$plot_maxod->setColor($red);
$plot_maxod->setThickness(1);
$plot_maxod->setStyle(Line::DOTTED);
// Définir l'axe de la courbe (ici, axe gauche)
$plot_def->setYAxis(Plot::LEFT);
$plot_moy->setYAxis(Plot::LEFT);
$plot_diff->setYAxis(Plot::LEFT);
// Ne pas afficher la ligne brisée
//$plot_def->hideLine(TRUE);
// Titre du graph
$group->title = new Label("[$ville] Nombre d'objets de défense en banque");
// LEGENDE AUX SOMMETS DE LA COURBE
	// On donne les valeurs prises par les étiquettes
   // Ici, on utilise les valeurs de chaque sommet de la courbe 
   	$plot_def->label->set($od);
	$plot_diff->label->set($diff);
   // On place l'étiquette par rapport au sommet
   // Par défaut, les étiquettes sont centrées sur les sommet
   // Dans le cas présent, on place l'étiquette un peu plus en haut
   	$plot_def->label->move(0, -23);
	$plot_diff->label->move(0, -10);
   // On donne aux étiquettes un dégradé de fond
   $plot_def->label->setBackgroundGradient(
      new LinearGradient(
         new Color(250, 250, 250, 10),
         new Color(0, 0, 200, 80),
         0
      	)
   	);
	$plot_diff->label->setColor($lavande);
   // On entoure les étiquettes d'une bordure
   // Cette bordure est presque noire et légèrement transparente
   	$plot_def->label->border->setColor(new Color(20, 20, 20, 20));
	//$plot_diff->label->border->setColor(new Color(20, 20, 20, 20));
   // Enfin, on ajoute un espace interne entre la bordure et le texte des étiquettes
   // Cette opération est nécessaire car les polices fournies par PHP
   // n'ont pas un rendu très bon
   	$plot_def->label->setPadding(3, 1, 1, 0);
	$plot_diff->label->setPadding(3, 1, 1, 0);
// MARQUE AUX SOMMETS DE LA COURBE
$plot_def->mark->setType(Mark::CIRCLE); 	// un rond ...
$plot_def->mark->border = new Border(Line::SOLID); // ... avec une bordure
$plot_def->mark->setFill($blue);

// Affichage du graph
$graph->add($group);
$graph->draw();
?>