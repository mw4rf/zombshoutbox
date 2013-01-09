<?php
header ("Content-type: image/png");
if(!isset($_GET['x']) or !isset($_GET['y']) or !is_numeric($_GET['x']) or !is_numeric($_GET['y'])) die("Erreur");

include_once('../config.inc.php');
include_once('../fonctions.inc.php');
include_once('../classes/Map.php');
$map = new Map();

// Récupération des coordonnées de la case
$x = $_GET['x'];
$y = $_GET['y'];
$map->setCell($x,$y);

// Création de l'image
$image = imagecreate(45,45); // width,height en pixels

// Couleurs indexées
// NB : la première couleur créée avec imagecolorallocate() devient la couleur de background de l'image


if($map->isVille()) // Case de la ville ?
	imagecolorallocate($image, 102, 204, 255); // sky blue
elseif($map->hasBeenVisited()) // La case a-t-elle été visitée ?
	imagecolorallocate($image, 255, 255, 180); // jaune clair
else
	imagecolorallocate($image, 230, 230, 230); // gris clair

$orange = imagecolorallocate($image, 255, 128, 0);
$bleu = imagecolorallocate($image, 0, 0, 255);
$rouge = imagecolorallocate($image, 255, 0, 0);
$bleuclair = imagecolorallocate($image, 156, 227, 254);
$noir = imagecolorallocate($image, 0, 0, 0);
$blanc = imagecolorallocate($image, 255, 255, 255);
$gris = imagecolorallocate($image, 179, 179, 179);

// Ajouter les informations à l'image
// Syntaxe : imagestring($image, $police, $position_x, $position_y, $texte_a_ecrire, $couleur);
// $police : la taille de la police (integer) de 1 à 5
// Pour du texte vertical : imagestringup(mêmes arguments)

// Coordonnées de la case
ImageStringRight($image, 1, 2, "$x.$y", $noir); // définie dans fonctions.inc.php

// Nombre de PA
$pa = $map->getDistance();
ImageStringRight($image, 1, 35, $pa, $noir); // définie dans fonctions.inc.php

// Nom de zomb	
$z = $map->getZomb();
if($z >0) imagestring($image, 2, 3, 30, $z, $rouge);

// Tag
//$t = $map->getTag();
//imagestring($image, 2, 2, 20, $t, $noir);

// Délimitation normale des cases (sans signification spécifique)
 imageline($image, 0, 0, 45, 0, $gris);
 imageline($image, 45, 0, 45, 45, $gris);
 imageline($image, 0, 45, 45, 45, $gris);
 imageline($image, 0, 0, 0, 45, $gris);

// Délimitation de la zone du scrutateur (hard!!)
$ar = $map->getArea($x,$y);
imagesetthickness($image, 3);
if( $ar['t'] ) imageline($image, 0, 0, 45, 0, $bleu);
if( $ar['r'] ) imageline($image, 44, 0, 44, 45, $bleu);
if( $ar['b'] ) imageline($image, 0, 44, 45, 44, $bleu);
if( $ar['l'] ) imageline($image, 0, 0, 0, 45, $bleu);

// Délimiation des cases : Bâtiment
if($map->hasBat())
{
	// imagesetthickness($image, 3);
	// imageline($image, 0, 0, 45, 0, $orange); // top
	// imageline($image, 44, 0, 44, 45, $orange); // right
	// imageline($image, 0, 44, 45, 44, $orange); // bottom
	// imageline($image, 0, 0, 0, 45, $orange); // left
	
	$imgbat = @imagecreatefromgif("../smilies/h_home.gif");
	@imagecopy($image, $imgbat, 15, 13, 0, 0, 19, 19);
}

// Création de l'image
//imagepng($image, NULL, 9, PNG_NO_FILTER); // la réduction de la taille est trop minime ; mieux vaut réduire le temps de travail de la fonction
imagepng($image);
?>