<?php
header ("Content-type: image/png");
if(!isset($_GET['x']) or !isset($_GET['y']) or !is_numeric($_GET['x']) or !is_numeric($_GET['y'])) die("Erreur");

include_once('../config.inc.php');
include_once('../fonctions.inc.php');
include_once('../classes/Map.php');
$map = new Map();

// R�cup�ration des coordonn�es de la case
$x = $_GET['x'];
$y = $_GET['y'];
$map->setCell($x,$y);

// Cr�ation de l'image
$image = imagecreate(45,45); // width,height en pixels

// Couleurs index�es
// NB : la premi�re couleur cr��e avec imagecolorallocate() devient la couleur de background de l'image


if($map->isVille()) // Case de la ville ?
	imagecolorallocate($image, 102, 204, 255); // sky blue
elseif($map->hasBeenVisited()) // La case a-t-elle �t� visit�e ?
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

// Ajouter les informations � l'image
// Syntaxe : imagestring($image, $police, $position_x, $position_y, $texte_a_ecrire, $couleur);
// $police : la taille de la police (integer) de 1 � 5
// Pour du texte vertical : imagestringup(m�mes arguments)

// Coordonn�es de la case
ImageStringRight($image, 1, 2, "$x.$y", $noir); // d�finie dans fonctions.inc.php

// Nombre de PA
$pa = $map->getDistance();
ImageStringRight($image, 1, 35, $pa, $noir); // d�finie dans fonctions.inc.php

// Nom de zomb	
$z = $map->getZomb();
if($z >0) imagestring($image, 2, 3, 30, $z, $rouge);

// Tag
//$t = $map->getTag();
//imagestring($image, 2, 2, 20, $t, $noir);

// D�limitation normale des cases (sans signification sp�cifique)
 imageline($image, 0, 0, 45, 0, $gris);
 imageline($image, 45, 0, 45, 45, $gris);
 imageline($image, 0, 45, 45, 45, $gris);
 imageline($image, 0, 0, 0, 45, $gris);

// D�limitation de la zone du scrutateur (hard!!)
$ar = $map->getArea($x,$y);
imagesetthickness($image, 3);
if( $ar['t'] ) imageline($image, 0, 0, 45, 0, $bleu);
if( $ar['r'] ) imageline($image, 44, 0, 44, 45, $bleu);
if( $ar['b'] ) imageline($image, 0, 44, 45, 44, $bleu);
if( $ar['l'] ) imageline($image, 0, 0, 0, 45, $bleu);

// D�limiation des cases : B�timent
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

// Cr�ation de l'image
//imagepng($image, NULL, 9, PNG_NO_FILTER); // la r�duction de la taille est trop minime ; mieux vaut r�duire le temps de travail de la fonction
imagepng($image);
?>