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
$eaup = array();
$eaub = array();
$eau = array();
$ville = "";

while($data = mysql_fetch_assoc($req))
{
	$eaup[] = $data['eaup'];
	$eaub[] = $data['eaub'];
	$eau[] = $data['eaup']+$data['eaub'];
	
	$adate[] = $data['adate'];
	//$jour[] = "Jour ".$data['jour'];
	$ville = $data['ville'];
}

// Moyennes
$moyenne = array(); $maxeau = array(); $mineau = array();
$limit = $count+2;
$sql = "SELECT AVG(eaup+eaub) AS moyenne, jour, eaup+eaub AS total, (MAX(eaup)+MAX(eaub)) AS maxeau, (MIN(eaup)+MIN(eaub)) AS mineau FROM xml_ressources GROUP BY jour ORDER BY jour ASC LIMIT 0,$limit";
$req = query($sql);
while($data = mysql_fetch_assoc($req))
{
	$moyenne[] = $data['moyenne'];
	$jour[] = "J".$data['jour'];
	$total = $data['total'];
	$maxeau[] = $data['maxeau'];
	$mineau[] = $data['mineau'];
	
	// Axe vertical
	$ymin = 0;
	$ymax = $total;
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
$plot_eau = new LinePlot($eau);
$plot_moy = new LinePlot($moyenne);
$plot_eaub = new LinePlot($eaub);
$plot_eaup = new LinePlot($eaup);
$plot_maxeau = new LinePlot($maxeau);
$plot_mineau = new LinePlot($mineau);
$group->legend->add($plot_eau, "Nombre total de rations", Legend::MARK);
if($graphfull) $group->legend->add($plot_moy, "Moyenne", Legend::BACKGROUND);
$group->legend->add($plot_eaup, "Rations dans le puits", Legend::LINE);
$group->legend->add($plot_eaub, "Rations en banque", Legend::LINE);
if($graphfull) $group->legend->add($plot_maxeau, "Maxima total", Legend::LINE);
if($graphfull) $group->legend->add($plot_mineau, "Minima total", Legend::LINE);
$group->legend->setPosition(0.50, 0.2);
$group->legend->setBackground( new Color($bgcol[0],$bgcol[1],$bgcol[2]) );
$group->legend->shadow->setSize(0);
$group->legend->border->hide();
$group->legend->setColumns(2);
$group->add($plot_eau);
if($graphfull) $group->add($plot_moy);
$group->add($plot_eaub);
$group->add($plot_eaup);
if($graphfull) $group->add($plot_maxeau);
if($graphfull) $group->add($plot_mineau);
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
$plot_eau->setColor($red);
$plot_maxeau->setColor($red);
$plot_maxeau->setStyle(Line::DOTTED);
$plot_mineau->setColor($red);
$plot_mineau->setStyle(Line::DOTTED);
$plot_moy->setThickness(0);
$plot_moy->setFillColor(new Color(200, 0, 0, 85)); //new Color(204, 102, 255, 80)
$plot_eaup->setColor(new Color(75,135,111));
$plot_eaub->setColor(new Color(61,85,65));
// Définir l'axe de la courbe (ici, axe gauche)
$plot_eau->setYAxis(Plot::LEFT);
$plot_moy->setYAxis(Plot::LEFT);
// Ne pas afficher la ligne brisée
//$plot_eau->hideLine(TRUE);
// Titre du graph
$group->title = new Label("[$ville] Nombre de rations d'eau");
// LEGENDE AUX SOMMETS DE LA COURBE
	// On donne les valeurs prises par les étiquettes
   // Ici, on utilise les valeurs de chaque sommet de la courbe 
   $plot_eau->label->set($eau);
   // On place l'étiquette par rapport au sommet
   // Par défaut, les étiquettes sont centrées sur les sommet
   // Dans le cas présent, on place l'étiquette un peu plus en haut
   $plot_eau->label->move(0, -23);
   // On donne aux étiquettes un dégradé de fond
   $plot_eau->label->setBackgroundGradient(
      new LinearGradient(
         new Color(250, 250, 250, 10),
         new Color(200, 0, 0, 80),
         0
      )
   );
   // On entoure les étiquettes d'une bordure
   // Cette bordure est presque noire et légèrement transparente
   $plot_eau->label->border->setColor(new Color(20, 20, 20, 20));
   // Enfin, on ajoute un espace interne entre la bordure et le texte des étiquettes
   // Cette opération est nécessaire car les polices fournies par PHP
   // n'ont pas un rendu très bon
   $plot_eau->label->setPadding(3, 1, 1, 0);
// MARQUE AUX SOMMETS DE LA COURBE
$plot_eau->mark->setType(Mark::CIRCLE); 	// un rond ...
$plot_eau->mark->border = new Border(Line::SOLID); // ... avec une bordure
$plot_eau->mark->setFill($red);

// Affichage du graph
$graph->add($group);
$graph->draw();
?>