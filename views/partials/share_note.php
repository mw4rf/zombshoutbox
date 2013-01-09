<?php
if(file_exists('../../config.inc.php')) include_once('../../config.inc.php'); // chargement normal
elseif(file_exists('../config.inc.php')) include_once('../config.inc.php'); // ajax
elseif(file_exists('config.inc.php')) include_once('config.inc.php'); // ajax
else die("Bad rootpath");

include_once($rootpath."/fonctions.inc.php");
include_once($rootpath."/classes/Note.php");

if(!isset($_POST['note_id']) or !is_numeric($_POST['note_id']))
	die("Vous ne pouvez pas appeler cette page directement");
	
$id = $_POST['note_id'];
$note = new Note();
$note->loadNote($id);
$part = $note->getSharedWith();

$user = $_COOKIE['user'];
?>

<div id="sharenotes">
	<hr />
	<table>
		<tr>
			<td>Partager la note avec</td>
			<td>
				<select id="sharenotesselect_<?php echo $id; ?>"  style="width:100%;">
					<?php
					$sql = "SELECT * FROM users_registered ORDER BY user";
					$req = query($sql);
					while($data = mysql_fetch_assoc($req))
					{
						if(stripslashes($data['user']) != $user)
							echo "<option value=\"".stripslashes($data['user'])."\">".stripslashes($data['user'])."</option>";
					}
					?>
				</select>
			</td>
			<td>
				<input type="submit" onclick="sharenotewith(<?php echo $id; ?>)" value="Ajouter" />
			</td>
		</tr>
		<?php if(is_array($part)) { ?>
		<tr>
			<td>Ne <u>plus</u> partager la note avec</td>
			<td>
				<select id="unsharenotesselect_<?php echo $id; ?>" style="width:100%;">
					<?php
							foreach($part as $p)
								echo "<option value=\"$p\">$p</option>";
					?>
				</select>
			</td>
			<td>
				<input type="submit" onclick="unsharenotewith(<?php echo $id; ?>)" value="Retirer" />
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="3">
			<hr />
			<?php
				if(is_array($part))
				{
					echo "Note partag&eacute;e avec ";
					foreach($part as $p)
						echo $p." ";
				}
			?>
			</td>
		</tr>
	</table>
</div>