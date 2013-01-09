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

// Skins
if(isset($_COOKIE['prefs_skincss']))
	include_once("../styles/".$_COOKIE['prefs_skincss'].".php");
else
	include_once("../styles/Hordes.php");

// Graphique complet ou simplifi�
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

// R�cup�ration des donn�es
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

$sql = "SELECT * FROM xml_map WHERE ville = '$ville' ORDER BY jour ASC";
$req = query($sql);
$count = num_rows($sql);

$cases = array();
$adate = array();
$jour = array();
$ville = "";

while($data = mysql_fetch_assoc($req))
{
	$cases[] = $data['cases'];
	$adate[] = $data['adate'];
	//$jour[] = "Jour ".$data['jour'];
	$ville = $data['ville'];
}

// Moyennes
$moyenne = array(); $max = array(); $min = array();
$limit = $count+2;
$sql = "SELECT AVG(cases) AS moyenne, jour, cases, MAX(cases) as max, MIN(cases) as min FROM xml_map GROUP BY jour ORDER BY jour ASC LIMIT 0,$limit";
$req = query($sql);
while($data = mysql_fetch_assoc($req))
{
	$moyenne[] = $data['moyenne'];
	$jour[] = "J".$data['jour'];
	
	// Axe vertical
	$ymin = 0;
	$ymax = $data['cases'];
	
	$max[] = $data['max'];
	$min[] = $data['min'];
}

// Axe horizontal
$xmin = 1;
$xmax = $count;

// Cr�ation du graphique. Les valeurs num�riques x,y pass�s en argument indiquent la taille en pixels du graph
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

// Cr�ation du graphique
$plot_cases = new LinePlot($cases);
$plot_moy = new LinePlot($moyenne);
$plot_max = new LinePlot($max);
$plot_min = new LinePlot($min);
$group->legend->add($plot_cases, "Nombre de cases", Legend::MARK);
if($graphfull) $group->legend->add($plot_moy, "Moyenne", Legend::BACKGROUND);
if($graphfull) $group->legend->add($plot_max, "Maxima", Legend::LINE);
if($graphfull) $group->legend->add($plot_min, "Minima", Legend::LINE);
$group->legend->setPosition(1, 0.1);
$group->legend->setBackground( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );
$group->legend->shadow->setSize(0);
$group->legend->border->hide();
$group->add($plot_cases);
if($graphfull) $group->add($plot_moy);
if($graphfull) $group->add($plot_max);
if($graphfull) $group->add($plot_min);
// Couleur de fond de la grille
$group->grid->setBackgroundColor( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );
$group->grid->setColor( new Color($bgcol[0]-10,$bgcol[1]-10,$bgcol[2]-10) );
// D�finir les axes
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
// D�finir la couleur de la courbe
$plot_cases->setColor($blue);
$plot_max->setColor($red);
$plot_max->setStyle(Line::DOTTED);
$plot_min->setColor($red);
$plot_min->setStyle(Line::DOTTED);
$plot_moy->setThickness(0);
$plot_moy->setFillColor(new Color(0, 0, 200, 85)); //new Color(204, 102, 255, 80)
// D�finir l'axe de la courbe (ici, axe gauche)
$plot_cases->setYAxis(Plot::LEFT);
$plot_moy->setYAxis(Plot::LEFT);
// Ne pas afficher la ligne bris�e
//$plot_cases->hideLine(TRUE);
// Titre du graph
$group->title = new Label("[$ville] Nombre de cases visit�es");
// LEGENDE AUX SOMMETS DE LA COURBE
	// On donne les valeurs prises par les �tiquettes
   // Ici, on utilise les valeurs de chaque sommet de la courbe 
   $plot_cases->label->set($cases);
   // On place l'�tiquette par rapport au sommet
   // Par d�faut, les �tiquettes sont centr�es sur les sommet
   // Dans le cas pr�sent, on place l'�tiquette un peu plus en haut
   $plot_cases->label->move(0, -23);
   // On donne aux �tiquettes un d�grad� de fond
   $plot_cases->label->setBackgroundGradient(
      new LinearGradient(
         new Color(250, 250, 250, 10),
         new Color(0, 0, 200, 80),
         0
      )
   );
   // On entoure les �tiquettes d'une bordure
   // Cette bordure est presque noire et l�g�rement transparente
   $plot_cases->label->border->setColor(new Color(20, 20, 20, 20));
   // Enfin, on ajoute un espace interne entre la bordure et le texte des �tiquettes
   // Cette op�ration est n�cessaire car les polices fournies par PHP
   // n'ont pas un rendu tr�s bon
   $plot_cases->label->setPadding(3, 1, 1, 0);
// MARQUE AUX SOMMETS DE LA COURBE
$plot_cases->mark->setType(Mark::CIRCLE); 	// un rond ...
$plot_cases->mark->border = new Border(Line::SOLID); // ... avec une bordure
$plot_cases->mark->setFill($blue);

// Affichage du graph
$graph->add($group);
$graph->draw();
?>