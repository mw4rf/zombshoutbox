<?php
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
?>
<table width="100%" border=0 style="font-size:0.8em" class="msg_text">
<tr>
	<td>User</td>
	<td>IP</td>
	<td>Vu le</td>
	<td>Admin</td>
	<td>MPub</td>
	<td>MPriv</td>
	<td>MPrivSent</td>
	<td>MPrivRece</td>
	<td>Commandes</td>
	<td>Vilains</td>
	<td>Calendrier</td>
	<td>Notes</td>
</tr>
<?php
$sql = "SELECT * FROM users_registered";
$req = query($sql);
$out = "";
// Nombre total de messages publics
$u_mtotal = num_rows("SELECT * FROM messages WHERE pm='0' AND command='0'");
while($data = mysql_fetch_assoc($req))
{
	$u_user = $data['user'];
	$u_admin = $data['admin']; if($u_admin) $u_admin = "&bull;"; else $u_admin = "";
	
	$sqlx = "SELECT * FROM users WHERE user='$u_user'";
	$reqx = query($sqlx);
	while($datax = mysql_fetch_assoc($reqx))
	{
		$u_la = date("d/m/Y H:i",$datax['lastaction']);
		$u_api = $datax['apikey'];
		$u_ip = $datax['ip'];
		
		$u_kicked = $datax['kicked']; if($u_kicked) $u_kicked = "&bull;"; else $u_kicked = "";
		$u_voice = $datax['voice']; if($u_voice) $u_voice = "&bull;"; else $u_voice = "";
		$u_focus = $datax['focus']; if($u_focus) $u_focus = "&bull;"; else $u_focus = "";
		$u_online = $datax['online']; if($u_online) $u_online = "&bull;"; else $u_online = "";
		$u_afk = $datax['afk']; if($u_afk) $u_afk = "&bull;"; else $u_afk = "";
	}
	
	
	// Nombre de messages publics
	$u_mpub = num_rows("SELECT * FROM messages WHERE user='$u_user' AND pm='0' AND command='0'");
	// Proportion messages publics de l'utilisateur / nombre total de messages publics
	$u_cpubp = ceil($u_mpub * 100 / $u_mtotal);
	// Nombre de messages privés ENVOYÉS par l'utilisateur
	$u_mprive = num_rows("SELECT * FROM messages WHERE user='$u_user' AND pm!='0' AND command='0'");
	// Nombre de messages privés RECUS par l'utilisateur
	$u_mprivr = num_rows("SELECT * FROM messages WHERE pm='$u_user' AND user!='SYSTEM' AND command='0'");
	// Nombre total de MP de l'utilisateur
	$u_mpt = $u_mprive + $u_mprivr;
	// Nombre de messages SYSTEM reçus
	$u_mprivs = num_rows("SELECT * FROM messages WHERE pm='$u_user' AND user='SYSTEM'");
	// Nombre de commandes de l'utilisateur
	$u_cpub = num_rows("SELECT * FROM messages WHERE user='$u_user' AND command='1'");
	// Nombre de vilains dénoncés
	$u_cvilains = num_rows("SELECT * FROM parias WHERE user='$u_user'");
	// Nombre d'entrées dans le calendrier
	$u_cevents = num_rows("SELECT * FROM events WHERE user='$u_user'");
	// Nombre de notes prises
	$u_cnotes = num_rows("SELECT * FROM notes WHERE user='$u_user'");
	
	$out .= "<tr>
				<td>$u_user</td>
				<td>$u_ip</td>
				<td>$u_la</td>
				<td>$u_admin</td>
				<td>$u_mpub ($u_cpubp%)</td>
				<td>$u_mpt</td>
				<td>$u_mprive</td>
				<td>$u_mprivr</td>
				<td>$u_cpub</td>
				<td>$u_cvilains</td>
				<td>$u_cevents</td>
				<td>$u_cnotes</td>
			</tr>";
	
}
echo $out;
?>
</table>