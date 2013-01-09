<?php
include_once('config.inc.php');
include_once('fonctions.inc.php');

function analyze($in)
{
	global $auteur, $message;
	
	if(empty($in)) return;
	if($in{1} == '#') return;
	
	// debug
	//echo "ANALYZE<br /> <i>$in</i> <br />";
	
	// Remplacements REGEX
	$scripts = array(
		// Conditions
		"/SI (.*) ALORS (.*)/"	=>	"if($1){\$2}",
		"/NON (.+?)/" => "!$1",
		"/(.+?) OU (.+?)/" => "$1 or $2",
		"/(.+?) ET (.+?)/" => "$1 and $2",
		"/([A-Za-z0-9_]*) EST ([A-Za-z0-9_]*)/"	=>	"$1=='$2'",
		"/([A-Za-z0-9_]*) DIFFERENT DE ([A-Za-z0-9_]*)/" => "$1!='$2'",
		"/([A-Za-z0-9_]*) CONTIENT \"(.*?)\"/" => "stristr($1,'$2') !== FALSE",
		
		// Actions
		"/AFFICHER ([A-Za-z0-9_]*) ([A-Za-z0-9_\-]*)/"	=>	"\$message=\"<span style='$1:$2;'>\$message</span>\";",
		"/REMPLACER (.*) PAR (.*) DANS ([A-Za-z0-9_]*)/" => "$3=strip_tags(str_replace(\"$1\",\"$2\",$3));",
		"/REMPLACER ([A-Za-z0-9_]*) PAR ([A-Za-z0-9_]*)/" => "$1=\"$2\";",
		
		// Variables
		"/AUTEUR/"	=>	"\$auteur",
		"/MOI/" => $_COOKIE['user'],
		"/MESSAGE/" => "\$message",
		"/MP/" => "\$ismp",
		"/COMMANDE/" => "\$iscommande",
		"/SALON/" => "\$room",
		
		// Propriétés
		"/COULEUR/" => "color",
		"/FOND/" => "background-color",
		"/STYLE/" => "font-style",
		"/POIDS/" => "font-weight",
		"/DECORATION/" => "text-decoration",
		"/TAILLE/" => "font-size",
		"/IGNORER/" => "\$ignore=true;"
		);
	
	// Adapter pour la compatibilité preg_replace
	$patterns = array();
	$replacements = array();
	foreach($scripts as $pattern => $replacement)
	{
		$patterns[] = $pattern;
		$replacements[] = $replacement;
	}
	
	// Effectuer le remplacement
	$replaced = preg_replace($patterns, $replacements, $in);
	
	// debug
	//echo "$in<br />$replaced<br /><br />";
	
	if($replaced == $in) return ''; // anti-injection SQL :) => il faut que preg_replace ait changé la chaîne...
	return $replaced;
}

?>