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

$sql = "SELECT * FROM xml_attaque WHERE ville = '$ville' ORDER BY jour ASC";
$req = query($sql);
$count = num_rows($sql);

$min = array();
$max = array();
$def = array();
$adate = array();
$jour = array();
$ville = "";

while($data = mysql_fetch_assoc($req))
{
	$min[] = $data['min'];
	$max[] = $data['max'];
	$def[] = $data['def'];
	$adate[] = $data['adate'];
	//$jour[] = "Jour ".$data['jour'];
	$ville = $data['ville'];
}

// Moyennes
$moyenne = array(); $mmax = array(); $mmin = array();; $maxdef = array(); $mindef = array();
$limit = $count+2;
$sql = "SELECT ROUND(AVG(min+max)/2) AS moyenne, AVG(def) AS mdef, jour, MAX(max) as mmax, MIN(min) as mmin, MAX(def) as maxdef, MIN(def) as mindef FROM xml_attaque WHERE min != '0' GROUP BY jour ORDER BY jour ASC LIMIT 0,$limit";
$req = query($sql);
while($data = mysql_fetch_assoc($req))
{
	$moyenne[] = $data['moyenne'];
	$mdef[] = $data['mdef'];
	$jour[] = "J".$data['jour'];
	$mmax[] = $data['mmax'];
	$mmin[] = $data['mmin'];
	$maxdef[] = $data['maxdef'];
	$mindef[] = $data['mindef'];
}

// Axe vertical
$sql = "SELECT max FROM xml_attaque WHERE ville = '".addslashes($ville)."' ORDER BY jour DESC";
$req = query($sql);
$data = mysql_fetch_assoc($req);
$ymax = $data['max'];
$ymin = 0;

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
$orange = new Color(255,204,102);
$sky = new Color(102,204,255);
$aqua = new Color(0,128,255);
$salmon = new Color(255,102,102);

// Création du graphique
$plot_def = new LinePlot($def);
$plot_min = new LinePlot($min);
$plot_max = new LinePlot($max);
$plot_moy = new LinePlot($moyenne);
$plot_mdef = new LinePlot($mdef);
$plot_mmax = new LinePlot($mmax);
$plot_mmin = new LinePlot($mmin);
$plot_maxdef = new LinePlot($maxdef);
$plot_mindef = new LinePlot($mindef);
$group->legend->add($plot_def, "Défenses", Legend::MARK);
if($graphfull) $group->legend->add($plot_mdef, "Moyenne défenses", Legend::BACKGROUND);
$group->legend->add($plot_min, "Attaque min/max", Legend::LINE);
if($graphfull) $group->legend->add($plot_moy, "Moyenne attaque", Legend::BACKGROUND);
if($graphfull) $group->legend->add($plot_mmax, "Maxima moyen attaque", Legend::LINE);
if($graphfull) $group->legend->add($plot_mmin, "Minima moyen attaque", Legend::LINE);
if($graphfull) $group->legend->add($plot_maxdef, "Maxima moyen défenses", Legend::LINE);
if($graphfull) $group->legend->add($plot_mindef, "Minima moyen défenses", Legend::LINE);
$group->legend->setPosition(0.45, 0.20);
$group->legend->setBackground( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );
$group->legend->shadow->setSize(0);
$group->legend->border->hide();
$group->legend->setColumns(2);
if($graphfull) $group->add($plot_moy);
if($graphfull) $group->add($plot_mdef);
if($graphfull) $group->add($plot_mmax);
if($graphfull) $group->add($plot_mmin);
if($graphfull) $group->add($plot_maxdef);
if($graphfull) $group->add($plot_mindef);
$group->add($plot_min);
$group->add($plot_max);
$group->add($plot_def);// en dernier pour être en dessus de tout
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
$plot_def->setColor($aqua);
$plot_def->setThickness(2);
$plot_min->setColor($salmon);
$plot_min->setThickness(2);
$plot_max->setColor($salmon);
$plot_max->setThickness(2);
$plot_moy->setThickness(0);
$plot_moy->setFillColor(new Color(200, 0, 0, 85)); //new Color(204, 102, 255, 80)
$plot_mdef->setThickness(0);
$plot_mdef->setFillColor(new Color(0, 0, 200, 85));
$plot_mmax->setColor($red);
$plot_mmax->setThickness(1);
$plot_mmax->setStyle(Line::DOTTED);
$plot_mmin->setColor($red);
$plot_mmin->setThickness(1);
$plot_mmin->setStyle(Line::DOTTED);
$plot_maxdef->setColor($blue);
$plot_maxdef->setThickness(1);
$plot_maxdef->setStyle(Line::DOTTED);
$plot_mindef->setColor($blue);
$plot_mindef->setThickness(1);
$plot_mindef->setStyle(Line::DOTTED);
// Définir l'axe de la courbe (ici, axe gauche)
$plot_def->setYAxis(Plot::LEFT);
$plot_min->setYAxis(Plot::LEFT);
$plot_max->setYAxis(Plot::LEFT);
$plot_moy->setYAxis(Plot::LEFT);
$plot_mmax->setYAxis(Plot::LEFT);
$plot_mmin->setYAxis(Plot::LEFT);
$plot_maxdef->setYAxis(Plot::LEFT);
$plot_mindef->setYAxis(Plot::LEFT);
// Ne pas afficher la ligne brisée
//$plot_def->hideLine(TRUE);
// Titre du graph
$group->title = new Label("[$ville] Défenses et estimations de l'attaque");
// LEGENDE AUX SOMMETS DE LA COURBE
	// On donne les valeurs prises par les étiquettes
   // Ici, on utilise les valeurs de chaque sommet de la courbe 
   $plot_def->label->set($def);
   // On place l'étiquette par rapport au sommet
   // Par défaut, les étiquettes sont centrées sur les sommet
   // Dans le cas présent, on place l'étiquette un peu plus en haut
   $plot_def->label->move(0, -23);
   // On donne aux étiquettes un dégradé de fond
   $plot_def->label->setBackgroundGradient(
         new LinearGradient(
            new Color(250, 250, 250, 10),
            new Color(0, 0, 200, 80),
            0
         )
      );
   // On entoure les étiquettes d'une bordure
   // Cette bordure est presque noire et légèrement transparente
   $plot_def->label->border->setColor(new Color(20, 20, 20, 20));
   // Enfin, on ajoute un espace interne entre la bordure et le texte des étiquettes
   // Cette opération est nécessaire car les polices fournies par PHP
   // n'ont pas un rendu très bon
   $plot_def->label->setPadding(3, 1, 1, 0);
	// Couleur du texte du label
	//$plot_def->label->setColor($aqua);
// MARQUE AUX SOMMETS DE LA COURBE
$plot_def->mark->setType(Mark::CIRCLE); 	// un rond ...
$plot_def->mark->border = new Border(Line::SOLID); // ... avec une bordure
$plot_def->mark->setFill($aqua);

// Affichage du graph
$graph->add($group);
$graph->draw();
?>