<?php
session_start();
header( 'Content-Type: text/xml; charset=UTF-8' );
include_once("../config.inc.php");
include_once("../fonctions.inc.php");

$now = date("H:i:s");
echo "<div class=\"tabmaj\">Derni&egrave;re mise &agrave; jour &agrave; $now&nbsp;&nbsp;<input id=\"tab_notes_rb\" type=\"submit\" value=\"&#x238B; Actualiser\" onclick=\"ajaxrefresh('tab_notes')\"></div>";

$output = "";
if(isset($_SESSION['auth']) and $_SESSION['auth'] == 1)
{
	$user = $_COOKIE['user'];
	
	// Formulaire d'ajout
	?>
	<div id="note_form">
		Nouvelle note
		<br /><small><i>NB : vous pouvez utiliser la commande <u>/note &lt;texte&gt;</u> depuis l'onglet ShoutBox pour prendre une nouvelle note.</i></small>
		<br /><textarea style="width:90%;" id="note" name="note" rows="5"></textarea>
		<br /><input type="submit" value="Enregistrer la note &#x2192;" onclick="noteform()">
	</div>
	
	<?php
	// Liste des notes
	//$sql = "SELECT * FROM notes WHERE user='$user' ORDER BY id DESC";
	$sql = "SELECT DISTINCT notes.id as id, notes.user as user, notes.note as note, notes.cdate as cdate FROM notes,notes_users WHERE user='$user' OR (notes.id = notes_users.note_id AND notes_users.reader = '$user') ORDER BY notes.id DESC";
	$req = query($sql);
	
	while($data = mysql_fetch_assoc($req))
	{
		$id = $data['id'];
		$note = stripslashes(iconv("ISO-8859-1","UTF-8",$data['note']));
		$cdate = fdatetime($data['cdate'],"d/m/Y H\hi");
		
		$sql2 = "SELECT * FROM notes_users WHERE note_id = '$id'";
		$count = num_rows($sql2);
		
		if($count > 0)
		{
			$class1 = "shared_note";
			$part = "<br /><i>Partag&eacute;e</i>";
		}
		else
		{
			$class1 = "";
			$part = "";
		}
		
		if($user != $data['user'])
			{ 
				$class2 = "foreign_note"; $class1 = ""; 
				$owner = $data['user'];
			}
		else
		{
			$class2 = '';
			$owner = '';
		}
			
		
		$output .= "<div class=\"note $class1 $class2\"><table width=\"100%\">"
				."<tr><td rowspan=\"2\" class=\"note_meta\" width=\"20%\">$cdate";
		
		if($user != $data['user'])
			$output .= "<br />Note de <u>$owner</u>";
		else
			$output .= $part;
		
		$output .= "<table width=\"100%\" cellspacing=0 style=\"font-size:0.9em;\">"
			    .  "<tr>";
		
		if($user == $data['user'])
			$output .= "<td><input type=\"submit\" value=\"Supprimer\" onclick=\"delnote($id)\"></td>";
		else
			$output .= "<td><input type=\"submit\" value=\"Rejeter\" onclick=\"cancelsharenote($id,'$user')\"></td>";
			
			$output .= "<td><input type=\"submit\" value=\"Copier\" onclick=\"copynote($id)\"></td>";
		
		$output .= "</tr><tr>";

				
		if($user == $data['user'])
			$output .= "<td><input type=\"submit\" value=\"Partager\" onclick=\"togglesharenote($id)\"></td>";
		else
			$output .= "<td>&nbsp;</td>";
		
			$output .= "<td><input type=\"submit\" value=\"Modifier\" onclick=\"modnote($id,'1')\"></td></td>";
		
		$output .= "</tr>";
		$output .= "</table>";
		
		$output .= "</td>"
				."<td class=\"note_data\" width=\"80%\">
					<div id=\"note_$id\">$note</div>
					<div id=\"modnote_$id\" style=\"display:none;\">
						<textarea style=\"width:90%;\" id=\"modnote_area_$id\" rows=\"5\">$note</textarea>
						<input type=\"submit\" value=\"Enregistrer les modifications &#x2192;\" 
								onclick=\"modnote($id,'2')\">
						&nbsp;
						<input type=\"submit\" value=\"Annuler\" 
								onclick=\"modnote($id,'1')\">
					</div>
				  </td></tr>";
				
		if($user == $data['user'])
			$output .= "<tr><td><span id=\"sharenote_container_$id\"></span></td></tr>";
				
		$output .= "</table></div>";
	}
}
else
{
	$output = "<div class=\"tabmaj\">Vous devez vous identifier avant d'avoir acc&egrave;s &agrave; vos notes.</div>";
}
echo $output;
?>