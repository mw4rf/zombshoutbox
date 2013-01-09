<?php
if(file_exists('config.inc.php')) include_once('config.inc.php'); else include_once('../config.inc.php');
if(file_exists('fonctions.inc.php')) include_once('fonctions.inc.php'); else include_once('../fonctions.inc.php');

if(file_exists('classes/Rooms.php'))
{
	include_once('classes/Rooms.php'); // chargement initial
	include_once('classes/Objets.php');
	include_once('classes/Chantiers.php');
}
else
{
	include_once('../classes/Rooms.php'); // par ajax
	include_once('../classes/Objets.php');
	include_once('../classes/Chantiers.php');
}

// Archives
$tomorrow = date("d/m/Y",mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));
$yesterday  = date("d/m/Y",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));

?>
<div id="search_forms">
	
	<div class="archives">
	<table width="100%" border=0>
	
	<tr>
		<td>
			<input type="submit" value="Rechercher &#x2192;" onclick="recherche('dates')"> les messages du 
			<input id="s1_adebut" type="text" maxlength="10" size="10" value="<?php echo $yesterday; ?>">
			<input id="s1_tdebut" type="text" maxlength="10" size="5" value="00:00">
			au
			<input id="s1_afin" type="text" maxlength="10" size="10" value="<?php echo $tomorrow; ?>">
			<input id="s1_tfin" type="text" maxlength="10" size="5" value="23:59">
		</td>
		<td>
			Ordre d'affichage :  
			<select id="s_tri_ordre">
				<option value="DESC">R&eacute;cent &#x2192; Ancien</option>
				<option value="ASC">Ancien &#x2192; R&eacute;cent</option>
			</select>
		</td>
	</tr>

	<tr>
		<td>
			<input type="submit" value="Rechercher &#x2192;" onclick="recherche('user')"> les messages &eacute;crits par l'utilisateur 
			<input id="s2_user" type="text" maxlength="50" size="20" value="<?php echo $_COOKIE['user']; ?>">
		</td>
		<td><label><input id="s_commandes" type="checkbox" checked />Inclure les commandes</label></td>
	</tr>

	<tr>
		<td>
			<input type="submit" value="Rechercher &#x2192;" onclick="recherche('fulltext')"> les messages contenant 
			<input id="s3_text" type="text" maxlength="50" size="35" value="Salut !">
		</td>
		<td><label><input id="s_limit" type="checkbox" checked />Limiter &agrave;</label> <input id="s_limit_value" type="text" maxlength="10" size="5" value="100"> r&eacute;sultats</td>
	</tr>

	<tr>
		<td>
			&nbsp;
		</td>
		<td>
			Dans le salon
			<select id="s_room">
			<option value="0" selected>Public</option>
			<?php
			$rooms = new Rooms();
			$rs = $rooms->getRoomsName(false,true);
			foreach($rs as $r)
				echo "<option value=\"".$r[0]."\">".iconv("ISO-8859-1", "UTF-8",$r[1])."</option>";
			?>
			</select>
		</td>
	</tr>
	</table>
	</div>

	<div>&nbsp;</div>

	<div class="archives">
	<table width="100%" border=0>
		<tr>
			<td>
				<input type="submit" value="Afficher &#x2192;" onclick="recherche('citoyens')">
				les citoyens de la ville
				<?php include_once('recherche/form_search_citoyens.php'); ?>
			</td>
		</tr>

		<tr>
			<td>
				<input type="submit" value="Afficher &#x2192;" onclick="recherche('graphiques')"> 
				le graphique
				<select id="s_graphiques_graphs" onchange="recherche_switch_graphique()">
					<option value="gattaque">de l'attaque et des d&eacute;fenses</option>
					<option value="gcitoyens">des citoyens et des bannis</option>
					<option value="gmap">des cases visit&eacute;es</option>
					<option value="geau">des rations d'eau</option>
					<option value="gobjdef">des objets de d&eacute;fense</option>
					<option value="gobjets">des objets en banque</option>
					<option value="gprofessions">de r&eacute;partition des professions</option>
				</select>
				de la ville

				<span id='form_search_graph_container'>
					<?php include_once('recherche/form_search_graph.php'); ?>
				</span>
			</td>
		</tr>
	</table>
	</div>
	
	<div>&nbsp;</div>

	<div class="archives">
	<table width="100%" border=0>
		<tr>
			<td>
				<input type="submit" value="Afficher &#x2192;" onclick="recherche('objets')">
				les information sur <b>l'objet</b>
				<?php  include_once('recherche/form_search_objets.php'); ?>
			</td>
			<td>
				<input type="submit" value="&#x238B; Afficher tous les objets" onclick="recherche('objets_all')">
			</td>
		</tr>

		<tr>
			<td>
				<input type="submit" value="Afficher &#x2192;" onclick="recherche('chantiers')">
				les information sur <b>le chantier</b>
				<?php  include_once('recherche/form_search_chantiers.php'); ?>
			</td>
			<td>
				<input type="submit" value="&#x238B;  Afficher tous les chantiers" onclick="recherche('chantiers_all')">
			</td>
		</tr>
	</table>
	</div>
</div>
<?php
if(isset($_COOKIE['prefs_height']) and is_numeric($_COOKIE['prefs_height']))
	$height = $_COOKIE['prefs_height'];
else
	$height = '500';
?>
<div id="search_results" style="height:<?php echo $height; ?>px;display:none;"></div>