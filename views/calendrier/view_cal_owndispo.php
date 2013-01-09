<?php
if(file_exists('../../config.inc.php')) include_once('../../config.inc.php');
if(file_exists('../../fonctions.inc.php')) include_once('../../fonctions.inc.php');
?>
<div id="eventform" style="text-align:left;">
	<table>
	<tr>
	<td>Ajout rapide &#x2192;</td> <td><b>Aujourd'hui</b></td>
	<td>
	<select id="action_ajd" name="action_ajd">
		<option value="expe">Disponible | D'accord pour &ecirc;tre escort&eacute;</option>
		<option value="chantiers">Aux chantiers | Je reste en ville</option>
		<option value="absent" selected>Absent | D'accord pour &ecirc;tre escort&eacute;</option>
	</select>
	</td>
	<td><input type="submit" value="Enregistrer &#x2192;" onclick="quickevent('ajd')"></td>
	</tr>
	<tr>
	<td>Ajout rapide &#x2192;</td> <td><b>Demain</b></td>
	<td>
	<select id="action_demain" name="action_demain">
		<option value="expe">Disponible | D'accord pour &ecirc;tre escort&eacute;</option>
		<option value="chantiers">Aux chantiers | Je reste en ville</option>
		<option value="absent" selected>Absent | D'accord pour &ecirc;tre escort&eacute;</option>
	</select>
	</td>
	<td><input type="submit" value="Enregistrer &#x2192;" onclick="quickevent('demain')"></td>
	</tr>
	</table>
	Ajout d&eacute;taill&eacute; &#x2192; Du
	<input type="text" id="sdate" name="sdate" maxlength="10" size="10" value="<?php echo date("d/m/Y"); ?>" />
	&agrave;
	<input type="text" id="stime" name="stime" maxlength="5" size="5" value="<?php echo date("H:i"); ?>" />
	au
	<input type="text" id="edate" name="edate" maxlength="10" size="10" value="<?php echo date("d/m/Y"); ?>" />
	&agrave;
	<input type="text" id="etime" name="etime" maxlength="5" size="5" value="23:59" />
	je serai
	<select id="action" name="action">
		<option value="expe">Disponible | D'accord pour &ecirc;tre escort&eacute;</option>
		<option value="chantiers">Aux chantiers | Je reste en ville</option>
		<option value="absent" selected>Absent | D'accord pour &ecirc;tre escort&eacute;</option>
	</select>
	<input type="submit" value="Enregistrer &#x2192;" onclick="newevent()">
</div>
<div id="formresponse">&nbsp;</div>
<?php
// Mes disponibilités
if(isset($_COOKIE['user']))
{
	$user = $_COOKIE['user'];
	
	$output = "<table cellspacing=1>";
	
	$sql = "SELECT * FROM events WHERE user='$user' AND TO_DAYS(sdate) >= TO_DAYS(NOW()) ORDER BY sdate ASC";
	$count = num_rows($sql);
	if($count < 1) return "<div class=\"msg_text\" style=\"text-align:center;\">Aucune disponibilit&eacute; future enregistr&eacute;e</div>";
	$req = query($sql);
	while($data = mysql_fetch_assoc($req))
	{
		$id = $data['id'];
		$sdate = fdatetime($data['sdate'], "d/m");
		$stime = fdatetime($data['sdate'], "H\hi");
		$edate = fdatetime($data['edate'], "d/m");
		$etime = fdatetime($data['edate'], "H\hi");
		switch($data['action'])
		{
			case 'expe': $action = "Disponible et actif | Autorisation de m'escorter en exp&eacute;dition"; $class = "disponible"; break;
			case 'chantiers': $action = "Travail en ville aux chantiers | Laissez-moi en ville"; $class = "chantiers"; break;
			case 'absent': $action = "Absent ou indisponible | Autorisation de m'escorter en exp&eacute;dition"; $class = "indisponible"; break;
		}
		
		$output .= "<tr>"
				."<td width=\"10%\" style=\"text-align:right;\"><input type=\"submit\" value=\"Supprimer\" onclick=\"delevent('$id')\" /></td>"
				."<td class=\"frise_start frise_$class\" width=\"15%\">$sdate &agrave; $stime</td>"
				."<td class=\"frise_$class\" width=\"5%\">&nbsp;&#x2192;&nbsp;</td>"
				."<td class=\"frise_stop frise_$class\" width=\"15%\">$edate &agrave; $etime</td>"
				."<td class=\"calendrier_$class\" width=\"55%\">$action</td>"
				."</tr><tr>";
	}
	$output .= "</table>";
	echo $output;
}
?>