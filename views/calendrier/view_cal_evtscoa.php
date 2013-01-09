<?php
if(file_exists('../../config.inc.php')) include_once('../../config.inc.php');
if(file_exists('../../fonctions.inc.php')) include_once('../../fonctions.inc.php');

if(!empty($_COOKIE['prefs_frise']) and is_numeric($_COOKIE['prefs_frise']))
	$nbjrs = $_COOKIE['prefs_frise'];
else
	$nbjrs = 2;

// Evts coa
for($i = 0 ; $i < $nbjrs ; $i++)
	evtscoa(date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+$i, date("Y"))));

#################################################################################
function evtscoa($jour)
{
	$output = "<table width=\"100%\">";
	
	$sql = "SELECT * FROM events WHERE user = 'SYSTEM' AND ( TO_DAYS(sdate) = TO_DAYS('$jour') OR TO_DAYS(edate) = TO_DAYS('$jour') )";
	$req = query($sql);
	$count = num_rows($sql);
	while($data = mysql_fetch_assoc($req))
	{
		$action = entitiescharset(stripslashes($data['action']));
		$start = $data['sdate'];
		$end = $data['edate'];
		
		if(getFDate($start, "d") == date("d"))
			$jour = "Aujourd'hui";
		elseif(getFDate($start, "d") == date("d", mktime(0,0,0,0,date("d")+1)))
			$jour = "Demain";
		elseif(getFDate($start, "d") == date("d", mktime(0,0,0,0,date("d")+2)))
			$jour = "Apr&egrave;s-demain";
		else
			$jour = "Le ".getFDate($start,"d")." ".nommer_mois(getFDate($start,"m"));
		
		$start = fdatetime($start, "H\hi");
		$end = fdatetime($end, "H\hi");
		
		$output .= "<tr>"
				. "<td width=\"30%\" class=\"frise_header_alt\">$jour de $start &agrave; $end</td>"
				. "<td class=\"msg_text\">$action</td>"
				. "</tr>";
	}
	$output .= "</table><br />";
	
	if($count > 0)
		echo $output;
	else
		echo "<div class=\"msg_text\" style=\"text-align:center;\">Aucun &eacute;v&eacute;nement enregistr&eacute; pour le ".fdate($jour)."</div>";
}
?>