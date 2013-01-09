<?php

function __autoload($class_name) {
    require_once $class_name . '.php';
}

function connexion($base=false)
{
	global $dbhost, $dblogin, $dbpassword, $dbbase;
	
	if(!$base)
	{;}
	else
	{
		$dbbase = $base;
	}
	
	$db = mysql_connect($dbhost, $dblogin, $dbpassword);
	mysql_select_db($dbbase,$db); 
}

function query($sql,$base=false)
{
	global $twitter_user, $logqueries;
	
	if(!$base)
		connexion();
	else
		connexion($base);
	
	// $req = mysql_query($sql) or die("Le serveur de base de donn&eacute;es ne r&eacute;pond pas. Veuillez r&eacute;essayer dans quelques minutes, ou utiliser Twitter : <a href=\"http://twitter.com/$twitter_user\">@$twitter_user</a>");
	$req = mysql_query($sql) or die("<u>Requ&ecirc;te : </u>".$sql."<br /><u>Erreur</u> : ".mysql_error());
	
	// Log
	if($logqueries)
	{
		$log = "INSERT INTO log_sql VALUES('','$sql','".$_COOKIE['user']."','".date("Y-m-d H:i:s")."')";
	mysql_query($log);
	}
	
	// retour
	return $req;
}

function adminlog($message,$more='')
{
	$message = addslashes($message);
	$more = addslashes($more);
	$log = "INSERT INTO log_admin VALUES('','$message','".$_COOKIE['user']."','$more','".date("Y-m-d H:i:s")."')";
	query($log);
}

function logquery($query)
{
	date_default_timezone_set('Europe/Paris');
	$date = date("Y-m-d H:i:s");
	$query = addslashes($query);
	$log = "INSERT INTO log_sql VALUES('','$query','".$_COOKIE['user']."','$date')";
	query($log);
}

function num_rows($sql,$base=false)
{
	$req = query($sql,$base);
	$num = mysql_num_rows($req);
	return $num;
}

function getAPIKey($user)
{
	$sql = "SELECT * FROM users WHERE user = '$user'";
	$count = num_rows($sql);
	if($count)
	{
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		$key = $data['apikey'];
	}
	else
	{
		$key = $_COOKIE['key'];
	}
	return $key;
}

function ImageStringRight($image, $font, $y, $str, $col,$r_padding=1, $ImageString = 'ImageString') {
    // http://www.puremango.co.uk/2009/04/php-imagestringright-center-italic/

    $font_width = ImageFontWidth($font);
    //$str_width = strlen($str)*$font_width;
	$str_width = strlen($str)*$font_width+1;//hack pour la bordure
    if(!function_exists($ImageString) || $ImageString==__FUNCTION__) {
        // don't allow recursion
        $ImageString = 'ImageString';
    }
    $ImageString($image, $font, ImageSX($image)-$str_width-$r_padding, $y, $str, $col);
}

function fdate($date,$vers_mysql=false)
{

// JJ/MM/AAAA => AAAA-MM-JJ
if($vers_mysql)
{
$pattern = "`([0-9]{2})/([0-9]{2})/([0-9]{4})`";
$replacement = "$3-$2-$1";
}

// AAAA-MM-JJ => JJ/MM/AAAA
else
{
$pattern = "`([0-9]{4})-([0-9]{2})-([0-9]{2})`";
$replacement = "$3/$2/$1";
}

return preg_replace($pattern, $replacement, $date);
}

function fdatetime($datetime,$format=false)
{
	$annees = substr($datetime,0,4);
	$mois = substr($datetime,5,2);
	$jours = substr($datetime,8,2);
	$heures = substr($datetime,11,2);
	$minutes = substr($datetime,14,2);
	$secondes = substr($datetime,17,2);
	
	if(!$format)
		$format = "d M - H:i:s";
	
	return date($format, mktime($heures,$minutes,$secondes,$mois,$jours,$annees));
}

function tmstp($datetime)
{
	
	$sep = explode(" ",$datetime);
	
	$tmpa = explode("/",$sep[0]);
	if(!isset($tmpa[0])) $tmpa[0] = "0";
	if(!isset($tmpa[1])) $tmpa[1] = "0";
	if(!isset($tmpa[2])) $tmpa[2] = "0";
	
	$tmpb = explode(":",$sep[1]);
	if(!isset($tmpb[0])) $tmpb[0] = "0";
	if(!isset($tmpb[1])) $tmpb[1] = "0";
	if(!isset($tmpb[2])) $tmpb[2] = "0";
		
	$stp = mktime($tmpb[0],$tmpb[1],$tmpb[2],$tmpa[1],$tmpa[0],$tmpa[2]);
	return $stp;
}

function getxml($key)
{
	$doc = new DOMDocument();
	@$doc->load('http://www.hordes.fr/xml/?k='.$key);
	return $doc;
}

// Intervient avant l'enregistrement du message dans la bdd
function parsing($in)
{
	// tags HTML dangereux
	$remove = array("php","javascript","script","embed","applet","iframe","object","style","input","div");
	$in = strip_selected_tags($in, $remove);
	
	// Caractères inconnus en ISO-LATIN-1
	$in = str_replace("’","'",$in);
	$in = str_replace("€",'&euro;',$in);
	
	// smilies => déplacé au moment de l'affichage : v. displayparsing
	//$in = parse_smileys($in);
	return $in;
}

// Intervient avant l'enregistrement du message dans la bdd
// Cette fonction émule une sytaxe particulière pour les tags html, p. ex. lien=http://... remplace <a href="http://">
function postparsing($txt)
{
	$result = "";
	$tab = explode(" ", $txt);
	$continuer = true;
	foreach($tab as $ex)
	{	
		// Résolution des opérations
		if(substr($ex,0,7) == "calcul=")
			{ $ex = substr($ex,7)."<b> = ".eval("return ".substr($ex,7).";")."</b>"; $continuer = false; }
		// Images
		elseif(substr($ex,0,6) == "image=")
			{ $ex = "<img src=\"".substr($ex,6)."\" />"; $continuer = false; }
		// Liens
		elseif(substr($ex,0,5) == "lien=")
			{ $ex = "<a href=\"".substr($ex,5)."\" target=\"_blank\">".substr($ex,5)."</a>"; $continuer = false; }
		// YouTube
		elseif(substr($ex,0,8) == "youtube=")
		{
			$inex = explode("?v=",$ex);
			$ref = $inex[1];
			$ex = "<object width=\"320\" height=\"265\"><param name=\"movie\" value=\"http://www.youtube-nocookie.com/v/$ref&hl=fr&fs=1&\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"http://www.youtube-nocookie.com/v/$ref&hl=fr&fs=1&\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"320\" height=\"265\"></embed></object>";
			$continuer = false;
		}
		// Saut de ligne
		elseif($ex == "//")
			$ex = "<br />";
		// tag [membre] : choix parmi les utilisateurs inscrits sur la shoutbox
		elseif($ex == "[membre]" or $ex == "[membre]," or $ex == "[membre]." or $ex == "[membre]:")
		{
			$sql = "SELECT * FROM users_registered";
			$req = query($sql);
			$membres = "";
			while($data = mysql_fetch_assoc($req))
				$membres[] = $data['user'];
			$count = count($membres);
			$rand = ceil(rand(0,$count));
			$ex = "<span class=\"msg_inner\">&nbsp;<img src=\"smilies/r_heroac.gif\" />&nbsp;".$membres[$rand]."&nbsp;</span>";
			$continuer = false;
		}
		// tag [habitant] : choix parmi les citoyens de la ville
		elseif($ex == "[habitant]" or $ex == "[habitant]," or $ex == "[habitant]." or $ex == "[habitant]:")
		{
			// Récupérer les données
			$doc = getxml($_COOKIE['key']);

			// Si le XML n'est pas disponible
			$error = "";
			$errs = $doc->getElementsByTagName('error');
			foreach($errs as $err)
				$error = $err->getAttribute('code');

			if($error == "not_in_game"  or $error == "horde_attacking")
				return $ex;

			// Liste des citoyens
			$citizens = $doc->getElementsByTagName('citizen');
			foreach($citizens as $citizen)
				$habitants[] = iconv("UTF-8", "ISO-8859-1", $citizen->getAttribute('name'));
			$count = count($habitants);
			$rand = ceil(rand(0,$count));
			
			$ex = "<span class=\"msg_inner\">&nbsp;<img src=\"smilies/h_human.gif\" />&nbsp;".$habitants[$rand]."&nbsp;</span>";
			$continuer = false;
		}
		
		// Retour
		$result .= $ex." ";
	}
	$result = trim($result);
	
	// Evite que, par exemple, les _ dans les URL d'image soient remplacés par des <u>...
	if($continuer)
	{	
		// Italique *, Gras **, Grans-italique ***, souligné _, barré __
		$patterns = array("/\*\*\*(.*)\*\*\*/","/\*\*(.*)\*\*/","/\*(.*)\*/","/--(.*)--/","/__(.*)__/");
		$replacements = array("<i><b>$1</b></i>","<b>$1</b>","<i>$1</i>","<s>$1</s>","<u>$1</u>");
		$result = preg_replace($patterns, $replacements, $result);
	
		// Couleurs
		$patterns = "/couleur\=(.*?)\((.*?)\)/";
		$replacements = "<span style=\"color:$1;\">$2</span>";
		$result = preg_replace($patterns, $replacements, $result);
		$patterns = "/fond\=(.*?)\((.*?)\)/";
		$replacements = "<span style=\"background-color:$1;\">$2</span>";
		$result = preg_replace($patterns, $replacements, $result);
	}
	
	return $result;
}

// Intervient avant l'affichage du message (postérieurement à son enregistrement dans la bdd)
function displayparsing($in)
{
	// smilies
	$in = parse_smileys($in);
	
	//images
	if(isset($_COOKIE['prefs_images']) and $_COOKIE['prefs_images'] == "Non")
		$in = strip_selected_tags($in, array("img"));
	
	return $in;
}


function schtroumpfer($in)
{
	$in = explode(" ", $in);
	$out = "";
	foreach($in as $t)
	{
		$a = array($t, $t, $t, $t, $t, $t, $t, $t, $t, $t, $t, $t, $t, $t, $t, "schtroumpf", "schtroumpfer", "banania", "carotte", "prout", "je ne pense qu'&agrave; &ccedil;a", "nanan&egrave;re", "outch", "mais lol", "noooooon", "ben si lol", "c'est pas vrai !", "ENORME", "cacaprout", "j'ai mang&eacute; un carambar !");
		$rand = ceil(rand(0,count($a)));
		$t = $a[$rand];
		$out .= $t." ";
	}
	$out = trim($out);
	return $out;
}


function cleancharset($in)
{
	$in = str_replace("Ã©","é",$in);
	$in = str_replace("Ã¨","è",$in);
	$in = str_replace("Ã","à",$in);
	$in = str_replace("àª","ê",$in);
	$in = str_replace("à¢","â",$in);
	$in = str_replace("à«","ë",$in);
	$in = str_replace("à¯","ï",$in);
	$in = str_replace("à§","ç",$in);
	return $in;
}

function entitiescharset($in)
{
	$in = str_replace("&eacute;","é",$in);
	$in = str_replace("&egrave;¨","è",$in);
	$in = str_replace("&agrave;","à",$in);
	$in = str_replace("&ecirc;","ê",$in);
	$in = str_replace("&acirc;","â",$in);
	$in = str_replace("&euml;","ë",$in);
	$in = str_replace("&iuml;","ï",$in);
	$in = str_replace("&ccedil;","ç",$in);
	return $in;
}

// thanks : http://fr.php.net/manual/fr/function.strip-tags.php
function strip_selected_tags($text, $tags = array())
{
    $args = func_get_args();
    $text = array_shift($args);
    $tags = func_num_args() > 2 ? array_diff($args,array($text))  : (array)$tags;
    foreach ($tags as $tag){
        while(preg_match('/<'.$tag.'(|\W[^>]*)>(.*)<\/'. $tag .'>/iusU', $text, $found)){
            $text = str_replace($found[0],$found[2],$text);
        }
    }

    return preg_replace('/(<('.join('|',$tags).')(|\W.*)\/>)/iusU', '', $text);
}

//thanks : http://www.liamdelahunty.com/tips/php_convert_url_to_link.php
function parse_to_url($str)
{
	
	return ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">\\0</a>", $str);
}

function parse_smileys($txt)
{
	global $smiley_in, $smiley_out;
	$in = $smiley_in;
	$out = $smiley_out;
	
	for($i = 0; $i < count($out); $i++)
		$out[$i] = "<img alt=\"$out[$i]\" src=\"smilies/h_$out[$i].gif\" />";
		
	return str_replace($in,$out,$txt);
}


/* 
Nom: file_size_info
But: Calcule la taille d'un fichier en octets
Info: http://www.webmasterworld.com/forum88/2069.htm, 02/03/2007
*/
function file_size_info($filesize)
{
 $bytes = array('Octets', 'Ko', 'Mo', 'Go', 'To');

 if ($filesize < 1024) $filesize = 1;
 	for ($i = 0; $filesize > 1024; $i++)
 		$filesize /= 1024;

 $file_size_info['size'] = ceil($filesize);
 $file_size_info['type'] = $bytes[$i];

 return $file_size_info;
}

/*
* Nom : getFDate()
* But : retourne une partie d'une valeur date créée par PHP avec la fonction date()
* Info : Guillaume Florimond, 15/03/2008
* Param 1 : $valeur : la date PHP à traiter
* Param 2 : $operateur : la partie à extraire
* Param 2 : $operateur peut être 's' (seconde), 'i' (minutes), 'H' (heures), 'd' (jours), 'm' (mois) ou 'Y' (années)
*/
function getFDate($valeur,$operateur)
{
	switch($operateur)
	{
		// Secondes
		case 's': $res = substr($valeur, 17, 2); break;
		// Minutes
		case 'i': $res = substr($valeur, 14, 2); break;
		// Heures
		case 'H': $res = substr($valeur, 11, 2); break;
		// Jours
		case 'd': $res = substr($valeur, 8, 2); break;
		// Mois
		case 'm': $res = substr($valeur, 5, 2); break;
		// Années
		case 'Y': $res = substr($valeur, 0, 4); break;
		// Par défaut
		default: $res = NULL; break;
	}
	return $res;
}

// Donne un nom au mois passé en argument
function nommer_mois($mois) 
{
	$M = Array("", "Janvier", "F&eacute;vrier", "Mars", "Avril", "Mai", "Juin", "Juillet", "A&ocirc;ut", "Septembre", "Octobre", "Novembre", "D&eacute;cembre");
	return (intval($mois) > 0 && intval($mois) < 13) ? $M[intval($mois)] : "Indéfini";
}

// Thanks Federico Bricker
// http://www.php.net/print_r
function print_nice($elem,$max_level=10,$print_nice_stack=array()){ 
    if(is_array($elem) || is_object($elem)){ 
        if(in_array(&$elem,$print_nice_stack,true)){ 
            echo "<font color=red>RECURSION</font>"; 
            return; 
        } 
        $print_nice_stack[]=&$elem; 
        if($max_level<1){ 
            echo "<font color=red>nivel maximo alcanzado</font>"; 
            return; 
        } 
        $max_level--; 
        echo "<table border=1 cellspacing=0 cellpadding=3 width=100%>"; 
        if(is_array($elem)){ 
            echo '<tr><td colspan=2 style="background-color:#333333;"><strong><font color=white>ARRAY</font></strong></td></tr>'; 
        }else{ 
            echo '<tr><td colspan=2 style="background-color:#333333;"><strong>'; 
            echo '<font color=white>OBJECT Type: '.get_class($elem).'</font></strong></td></tr>'; 
        } 
        $color=0; 
        foreach($elem as $k => $v){ 
            if($max_level%2){ 
                $rgb=($color++%2)?"#888888":"#BBBBBB"; 
            }else{ 
                $rgb=($color++%2)?"#8888BB":"#BBBBFF"; 
            } 
            echo '<tr><td valign="top" style="width:40px;background-color:'.$rgb.';">'; 
            echo '<strong>'.$k."</strong></td><td>"; 
            print_nice($v,$max_level,$print_nice_stack); 
            echo "</td></tr>"; 
        } 
        echo "</table>"; 
        return; 
    } 
    if($elem === null){ 
        echo "<font color=green>NULL</font>"; 
    }elseif($elem === 0){ 
        echo "0"; 
    }elseif($elem === true){ 
        echo "<font color=green>TRUE</font>"; 
    }elseif($elem === false){ 
        echo "<font color=green>FALSE</font>"; 
    }elseif($elem === ""){ 
        echo "<font color=green>EMPTY STRING</font>"; 
    }else{ 
        echo str_replace("\n","<strong><font color=red>*</font></strong><br>\n",$elem); 
    } 
}

?>