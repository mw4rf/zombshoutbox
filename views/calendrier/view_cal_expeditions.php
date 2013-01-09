<?php
if(file_exists('../../config.inc.php')) include_once('../../config.inc.php');
if(file_exists('../../fonctions.inc.php')) include_once('../../fonctions.inc.php');
if(file_exists('../../classes/HordesXML.php')) include_once("../../classes/HordesXML.php");
?>
<div id="expeform" class="prefs">
	<table width="70%">
	<tr>
	<td style="text-align:right;">Exp&eacute;dition &#x2192; </td>
	<td>
	<select id="expe_trajet">
		<?php
		$hxml = new HordesXML();
		$trajets = $hxml->getExpeditions();
		foreach($trajets as $trajet)
			echo "<option value=\"".$trajet['author']."\">".$trajet['name']."  [".$trajet['author']."]</option>";
		?>
	</select>
	</td>
	</tr>
	
	<tr>
	<td style="text-align:right;">Horaire &#x2192; </td>
	<td>le <input type="text" id="expe_depart_date" name="expe_depart_date" maxlength="10" size="10" value="<?php echo date("d/m/Y"); ?>" />
	de
	<input type="text" id="expe_depart_time" name="expe_depart_time" maxlength="5" size="5" value="<?php echo date("H:i"); ?>" />
	&agrave;
	<input type="text" id="expe_arrivee_time" name="expe_arrivee_time" maxlength="5" size="5" value="22:00" />
	</td>
	</tr>
	
	<tr>
	<td style="text-align:right;">Participants &#x2192; </td>
	<td>
	<?php
	// Liste des utilisateurs déjà pris dans une expé
	$expediteurs = "";
	$sql = "SELECT * FROM expeditions WHERE TO_DAYS(depart) = TO_DAYS('".date("Y-m-d")."')";
	$req = query($sql);
	while($data = mysql_fetch_assoc($req))
	{
		$expediteurs[] = $data['leader'];
		$pps = explode(",",$data['membres']);
		foreach($pps as $pp)
			$expediteurs[] = $pp;
	}
	
	// Liste des utilisateurs disponibles
	$sql = "SELECT * FROM events WHERE user != 'SYSTEM' AND ( TO_DAYS(sdate) = TO_DAYS('".date("Y-m-d")."') OR TO_DAYS(edate) = TO_DAYS('".date("Y-m-d")."') ) AND ( action='absent' OR action = 'expe' )";
	$req = query($sql);
	$out = "";
	while($data = mysql_fetch_assoc($req))
	{
		if(!empty($expediteurs) and is_array($expediteurs))
		foreach($expediteurs as $xpd)
			if($xpd == $data['user'])
				continue 2;
		
		$out .= "<label><input type=\"checkbox\" value=\"".$data['user']."\" name=\"expe_users\" />".$data['user']."</label>";
		$out .= " | ";
	}
	echo trim($out," | ");
	?>
	</td>
	</tr>
	
	<tr>
	<td>&nbsp;</td>
	<td>
	<input type="submit" value="Cr&eacute;er l'exp&eacute;dition &#x2192;" onclick="newexpe()">
	</td>
	</tr>
	</table>
</div>
<div id="experesponse"></div>

<div id="expe_list" class="msg_text" style="margin-top:1em;">
<?php

	echo "<table cellspacing=1 style=\"font-size:0.9em;\">";
	
	$sql = "SELECT * FROM expeditions WHERE ( TO_DAYS(depart) = TO_DAYS('".date("Y-m-d")."') OR TO_DAYS(arrivee) = TO_DAYS('".date("Y-m-d")."') )";
	$req = query($sql);
	while($data = mysql_fetch_assoc($req))
	{
		// liste des participants à l'expédition
		$participants = "";
		$membres = explode(",",$data['membres']);
		foreach($membres as $membre)
			$participants .= $membre." ";
		$participants = stripslashes(trim($participants));
		
		// dates
		$ddate = fdatetime($data['depart'],"d/m/Y");
		if($ddate == date("d/m/Y")) $ddate = "Aujourd'hui";
		
		$dtime = fdatetime($data['depart'],"H\hi");
		$atime = fdatetime($data['arrivee'],"H\hi");
		
		echo         "<tr>"
					."<td><input type=\"submit\" value=\"Supprimer\" onclick=\"delexpe('".$data['id']."')\"></td>"
					."<td style=\"font-size:0.9em\" class=\"frise_start frise_stop tabmaj\">&nbsp;$ddate&nbsp;</td>"
					."<td style=\"font-size:0.9em\" class=\"frise_start tabmaj\">&nbsp;$dtime&nbsp;</td>"
					."<td style=\"font-size:0.9em\" class=\"tabmaj\">&#x2192;</td>"
					."<td style=\"font-size:0.9em\" class=\"frise_stop tabmaj\">&nbsp;$atime&nbsp;</td>"
					."<td>".$data['trajet']."</td>"
					."<td>".$data['leader']."</td>"
					."<td>$participants</td>"
					."</tr>";
	}
	
	echo "</table>";


?>
</div>