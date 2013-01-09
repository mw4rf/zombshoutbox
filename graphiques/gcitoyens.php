<?php
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
require_once("../api/artichow/LinePlot.class.php");
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

$sql = "SELECT * FROM xml_citoyens WHERE ville = '$ville' ORDER BY jour ASC";
$req = query($sql);
$count = num_rows($sql);

$vivants = array();
$bannis = array();
$adate = array();
$jour = array();
$ville = "";

while($data = mysql_fetch_assoc($req))
{
	$vivants[] = $data['vivants'];
	$bannis[] = $data['bannis'];
	$adate[] = $data['adate'];
	//$jour[] = "Jour ".$data['jour'];
	$ville = $data['ville'];
}

// Moyennes
$vmoy = array(); $bmoy = array(); $max = array(); $min = array();
$limit = $count+2;
$sql = "SELECT AVG(vivants) AS vmoy, AVG(bannis) AS bmoy, jour, MAX(vivants) as max, MIN(vivants) as min FROM xml_citoyens GROUP BY jour ORDER BY jour ASC LIMIT 0,$limit";
$req = query($sql);
while($data = mysql_fetch_assoc($req))
{
	$vmoy[] = $data['vmoy'];
	$bmoy[] = $data['bmoy'];
	
	$jour[] = "J".$data['jour'];
	
	$max[] = $data['max'];
	$min[] = $data['min'];
}

// Axe vertical
$ymin = 0;
$ymax = 40;

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
$plot_vivants = new LinePlot($vivants);
$plot_vmoy = new LinePlot($vmoy);
$plot_bannis = new LinePlot($bannis);
$plot_bmoy = new LinePlot($bmoy);
$plot_max = new LinePlot($max);
$plot_min = new LinePlot($min);
$group->legend->add($plot_vivants, "Vivants", Legend::MARK);
$group->legend->add($plot_vmoy, "Moyenne", Legend::BACKGROUND);
$group->legend->add($plot_bannis, "Bannis", Legend::MARK);
$group->legend->add($plot_bmoy, "Moyenne", Legend::BACKGROUND);
if($graphfull) $group->legend->add($plot_max, "Maxima vivants", Legend::LINE);
if($graphfull) $group->legend->add($plot_min, "Minima vivants", Legend::LINE);
$group->legend->setPosition(1, 0.1);
$group->legend->setBackground( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );
$group->legend->shadow->setSize(0);
$group->legend->border->hide();
$group->legend->setColumns(2);
$group->add($plot_vivants);
if($graphfull) $group->add($plot_vmoy);
$group->add($plot_bannis);
if($graphfull) $group->add($plot_bmoy);
if($graphfull) $group->add($plot_max);
if($graphfull) $group->add($plot_min);
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
$plot_vivants->setColor($blue);
$plot_max->setColor($blue);
$plot_max->setStyle(Line::DOTTED);
$plot_min->setColor($blue);
$plot_min->setStyle(Line::DOTTED);
$plot_bannis->setColor($red);
$plot_vmoy->setThickness(0);
$plot_vmoy->setFillColor(new Color(0, 0, 200, 85)); //new Color(204, 102, 255, 80)
$plot_bmoy->setThickness(0);
$plot_bmoy->setFillColor(new Color(200, 0, 0, 85));
// Définir l'axe de la courbe (ici, axe gauche)
$plot_vivants->setYAxis(Plot::LEFT);
$plot_vmoy->setYAxis(Plot::LEFT);
$plot_bannis->setYAxis(Plot::LEFT);
$plot_bmoy->setYAxis(Plot::LEFT);
// Ne pas afficher la ligne brisée
//$plot_vivants->hideLine(TRUE);
// Titre du graph
$group->title = new Label("[$ville] Citoyens");
// LEGENDE AUX SOMMETS DE LA COURBE
	// On donne les valeurs prises par les étiquettes
   // Ici, on utilise les valeurs de chaque sommet de la courbe 
   $plot_vivants->label->set($vivants);
   $plot_bannis->label->set($bannis);
   // On place l'étiquette par rapport au sommet
   // Par défaut, les étiquettes sont centrées sur les sommet
   // Dans le cas présent, on place l'étiquette un peu plus en haut
   $plot_vivants->label->move(0, -23);
	$plot_bannis->label->move(0, -23);
   // On donne aux étiquettes un dégradé de fond
   $plot_vivants->label->setBackgroundGradient(
      new LinearGradient(
         new Color(250, 250, 250, 10),
         new Color(0, 0, 200, 80),
         0
      )
   );
	$plot_bannis->label->setBackgroundGradient(
	   new LinearGradient(
	      new Color(250, 250, 250, 10),
	      new Color(200, 0, 0, 80),
	      0
	   )
	);
   // On entoure les étiquettes d'une bordure
   // Cette bordure est presque noire et légèrement transparente
   $plot_vivants->label->border->setColor(new Color(20, 20, 20, 20));
   $plot_bannis->label->border->setColor(new Color(20, 20, 20, 20));
   // Enfin, on ajoute un espace interne entre la bordure et le texte des étiquettes
   // Cette opération est nécessaire car les polices fournies par PHP
   // n'ont pas un rendu très bon
   $plot_vivants->label->setPadding(3, 1, 1, 0);
   $plot_bannis->label->setPadding(3, 1, 1, 0);
// MARQUE AUX SOMMETS DE LA COURBE
$plot_vivants->mark->setType(Mark::CIRCLE); 	// un rond ...
$plot_vivants->mark->border = new Border(Line::SOLID); // ... avec une bordure
$plot_vivants->mark->setFill($blue);
$plot_bannis->mark->setType(Mark::SQUARE); 	// un rond ...
$plot_bannis->mark->border = new Border(Line::SOLID); // ... avec une bordure
$plot_bannis->mark->setFill($red);

// Affichage du graph
$graph->add($group);
$graph->draw();
?>